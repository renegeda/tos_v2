<?php
// Включаем вывод всех ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Устанавливаем заголовок JSON
header('Content-Type: application/json; charset=utf-8');

// Начало сессии и проверка авторизации
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Требуется авторизация']));
}

// Подключаем файл с настройками БД
require_once __DIR__.'/../includes/db.php';

try {
    // Создаем подключение к БД
    $conn = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

// Параметры запроса
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
    $dir = isset($_GET['dir']) ? ($_GET['dir'] === 'DESC' ? 'DESC' : 'ASC') : 'ASC';

    // Безопасные поля для сортировки
    $allowedColumns = ['id', 'first_name', 'last_name', 'destination', 
                     'departure_date', 'arrival_date', 'persons', 'price', 'total', 'status'];

    // SQL запрос
    $sql = "SELECT 
                o.id,
                o.first_name,
                o.last_name,
                o.destination,
                o.departure_date,
                o.arrival_date,
                o.persons,
                o.price,
                o.total,
                o.status,
                m.login as manager_login
            FROM orders o
            LEFT JOIN managers m ON o.manager_id = m.id
            WHERE 1=1";

    $params = [];

    // Поиск
    if (!empty($search)) {
        $sql .= " AND (
            o.first_name LIKE :search OR
            o.last_name LIKE :search OR
            o.destination LIKE :search OR
            o.id LIKE :search OR
            o.status LIKE :search
        )";
        $params[':search'] = "%$search%";
    }

    // Сортировка
    if (!in_array($sort, $allowedColumns)) $sort = 'id';
    
    if ($sort === 'id') {
        $sql .= " ORDER BY CAST(SUBSTRING_INDEX(o.id, '/', 1) AS UNSIGNED) $dir, 
                  SUBSTRING_INDEX(o.id, '/', -1) $dir";
    } else {
        $sql .= " ORDER BY o.$sort $dir";
    }

    // Выполнение запроса
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $data = $stmt->fetchAll();

    // Форматирование дат с проверкой
    foreach ($data as &$row) {
        try {
            $row['departure_date_formatted'] = !empty($row['departure_date']) 
                ? date('d.m.Y', strtotime($row['departure_date']))
                : 'Нет даты';
                
            $row['arrival_date_formatted'] = !empty($row['arrival_date']) 
                ? date('d.m.Y', strtotime($row['arrival_date']))
                : 'Нет даты';
        } catch (Exception $e) {
            $row['departure_date_formatted'] = 'Ошибка даты';
            $row['arrival_date_formatted'] = 'Ошибка даты';
            error_log("Date format error for order {$row['id']}: " . $e->getMessage());
        }
    }

    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'data' => $data,
        'meta' => [
            'total' => count($data),
            'sorted_by' => $sort,
            'direction' => $dir
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Логируем ошибку в файл
    error_log(date('[Y-m-d H:i:s]') . " DB Error: " . $e->getMessage() . "\n", 3, __DIR__.'/../logs/db_errors.log');
    
    // Возвращаем понятную ошибку
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка базы данных',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Логируем другие ошибки
    error_log(date('[Y-m-d H:i:s]') . " System Error: " . $e->getMessage() . "\n", 3, __DIR__.'/../logs/system_errors.log');
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Системная ошибка',
        'details' => $e->getMessage()
    ]);
}
