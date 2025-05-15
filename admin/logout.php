<?php
// Используем абсолютные пути
$rootPath = dirname(__DIR__);
require_once $rootPath . '/includes/config.php';
require_once $rootPath . '/includes/functions.php';

// Уничтожаем все данные сессии
session_unset();
session_destroy();

// Перенаправляем на страницу входа
redirect(ADMIN_URL . '/login.php');