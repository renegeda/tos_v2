<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullName = trim($_POST['full_name']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'manager';
    
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO managers (login, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$login, $hash, $fullName, $role]);
    
    echo "Пользователь $login создан!";
}
?>

<form method="POST">
    <input type="text" name="login" placeholder="Логин" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <input type="text" name="full_name" placeholder="ФИО" required>
    <select name="role">
        <option value="manager">Менеджер</option>
        <option value="admin">Администратор</option>
    </select>
    <button type="submit">Создать</button>
</form>