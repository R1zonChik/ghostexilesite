<?php
// Установка кодировки (если доступно расширение mbstring)
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    mb_language('uni');
    mb_regex_encoding('UTF-8');
}

// Установка кодировки через header (важно для HTML)
header('Content-Type: text/html; charset=utf-8');

// Настройка обработки ошибок
ini_set('display_errors', 0); // Не показывать ошибки пользователям
ini_set('log_errors', 1); // Логировать ошибки
ini_set('error_log', __DIR__ . '/../logs/php_errors.log'); // Путь к файлу логов

// Запуск сессии (только если она еще не запущена)
if (session_status() === PHP_SESSION_NONE) {
    // Настройка безопасности сессий
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// Подключение конфигурации
require_once __DIR__ . '/config.php';

// Проверка и установка необходимых констант
if (!defined('SITE_DESCRIPTION')) {
    define('SITE_DESCRIPTION', 'Полное руководство по игре Ghost Exile');
}

// Создание директории для логов, если она не существует
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Подключение базы данных
require_once __DIR__ . '/database.php';

// Подключение функций
require_once __DIR__ . '/functions.php';

// Создание экземпляра базы данных
global $database;
if (!isset($database)) {
    try {
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    } catch (Exception $e) {
        error_log("Критическая ошибка при инициализации базы данных: " . $e->getMessage());
        
        // Отображение страницы с ошибкой
        header("HTTP/1.1 500 Internal Server Error");
        echo '<!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ошибка сервера</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #121212;
                    color: #fff;
                    text-align: center;
                    padding: 50px;
                    margin: 0;
                }
                .error-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: rgba(0, 0, 0, 0.3);
                    padding: 30px;
                    border-radius: 8px;
                }
                h1 {
                    color: #ff5f52;
                }
                .error-code {
                    display: inline-block;
                    background-color: #ff5f52;
                    color: #fff;
                    padding: 5px 15px;
                    border-radius: 4px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }
                .home-link {
                    display: inline-block;
                    margin-top: 20px;
                    background-color: #ff5f52;
                    color: #fff;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: bold;
                }
                .home-link:hover {
                    background-color: #e54942;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-code">HTTP ERROR 500</div>
                <h1>СТРАНИЦА НЕДОСТУПНА</h1>
                <p>Сайт временно не может обработать этот запрос.</p>
                <a href="' . (defined('SITE_URL') ? SITE_URL : '/') . '" class="home-link">Вернуться на главную</a>
            </div>
        </body>
        </html>';
        exit;
    }
}