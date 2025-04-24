<?php
session_start();
require_once 'includes/db.php';

// Редирект если уже авторизован
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM managers WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_role'] = $user['role'];
            
            // Обработка аватара
            $avatar_from_db = $user['avatar_path'] ?? '';
            $_SESSION['avatar_web'] = '/tso/' . ltrim($avatar_from_db, '/');
            
            header('Location: index.php');
            exit;
        }
        
        $error = "Неверный логин или пароль";
    } catch (PDOException $e) {
        $error = "Ошибка системы. Пожалуйста, попробуйте позже.";
        error_log("DB Error: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <title>Система управления заказами</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
<div class="dash-bg d-flex min-vh-100 justify-content-center align-items-center">
    <div class="row g-0 justify-content-center w-75 m-xxl-5 px-xxl-4 m-3">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card overflow-hidden text-center rounded-4 p-xxl-4 p-3 mb-0 shadow-sm">
                <a href="index.php" class="text-center mb-4">
                    <img src="assets/img/logo.svg" alt="logo" height="46" class="logo-light">
                </a>

                <h4 class="fw-semibold mb-2 fs-18">Вход в систему</h4>
                
                <?php if ($error): ?>
                <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" class="text-start mb-3 mt-3">
                    <div class="mb-2">
                        <input type="text" id="login" name="login" class="form-control" placeholder="Введите свой логин" required>
                    </div>

                    <div class="mb-3">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Введите свой пароль" required>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary fw-semibold" type="submit">Авторизация</button>
                    </div>
                </form>
            </div>

            <p class="my-4 text-center">
                &copy; <?= date('Y') ?> Система управления заказами. Все права защищены.
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous"></script>
</body>
</html>