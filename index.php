<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    // Можно добавить редирект или скрыть элементы
}

// В любом файле выполнить один раз
unset($_SESSION['avatar']);
session_write_close();
?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'includes/db.php';
require 'includes/header.php';
?>

<main>
    <?php include 'includes/main_content.php'; ?>
</main>

<?php
require 'includes/footer.php';
?>