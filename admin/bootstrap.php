<?php
// Запуск сессии (только если она еще не запущена)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Определение констант
define('ADMIN_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));

// Подключение основных файлов
require_once ROOT_DIR . '/includes/config.php';
require_once ROOT_DIR . '/includes/database.php';
require_once ROOT_DIR . '/includes/functions.php';
require_once ADMIN_DIR . '/includes/admin-functions.php';

// Создание экземпляра базы данных
global $database;
if (!isset($database)) {
    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
}

// Проверка авторизации
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    redirect(ADMIN_URL . '/login.php');
}