<?php
session_start();
require_once 'config.php';

// Проверка авторизации (опционально)
$requireAuth = false;
if ($requireAuth && !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Определяем запрашиваемую страницу
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'feeds', 'create', 'edit', 'logs', 'settings', 'help'];

// Загружаем соответствующий шаблон
if (in_array($page, $allowedPages) && file_exists("ui/partials/{$page}.php")) {
    include 'ui/partials/header.php';
    include "ui/partials/{$page}.php";
    include 'ui/partials/footer.php';
} else {
    include 'ui/partials/header.php';
    include 'ui/partials/404.php';
    include 'ui/partials/footer.php';
}