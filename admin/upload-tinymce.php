<?php
require_once 'bootstrap.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

// Увеличение лимитов PHP для загрузки файлов
ini_set('upload_max_filesize', '150M');
ini_set('post_max_size', '150M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300); // 5 минут
ini_set('max_input_time', 300); // 5 минут

// Обработка загрузки файла
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Проверка на ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = getUploadErrorMessage($file['error']);
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => $errorMessage]);
        exit;
    }
    
    // Проверка размера файла (150MB)
    $maxFileSize = 150 * 1024 * 1024; // 150MB в байтах
    if ($file['size'] > $maxFileSize) {
        header('HTTP/1.1 413 Request Entity Too Large');
        echo json_encode(['error' => 'Размер файла превышает допустимый лимит (150MB)']);
        exit;
    }
    
    // Определение типа файла
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Разрешенные типы файлов
    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedVideoTypes = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
    $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);
    
    if (!in_array($fileType, $allowedTypes)) {
        header('HTTP/1.1 415 Unsupported Media Type');
        echo json_encode(['error' => 'Неподдерживаемый тип файла. Разрешены только: ' . implode(', ', $allowedTypes)]);
        exit;
    }
    
    // Определение директории для загрузки
    $uploadDir = in_array($fileType, $allowedImageTypes) ? 'images/' : 'videos/';
    $targetDir = UPLOADS_DIR . $uploadDir;
    
    // Создание директории, если она не существует
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Генерация уникального имени файла
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $file['name']);
    $targetPath = $targetDir . $fileName;
    
    // Перемещение загруженного файла
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Успешная загрузка
        $fileUrl = UPLOADS_URL . $uploadDir . $fileName;
        echo json_encode(['location' => $fileUrl]);
    } else {
        // Ошибка при перемещении файла
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Не удалось сохранить файл. Проверьте права доступа к директории.']);
    }
} else {
    // Файл не передан
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Файл не передан']);
}

/**
 * Получение сообщения об ошибке загрузки файла
 */
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Размер файла превышает максимально допустимый размер, указанный в php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Размер файла превышает максимально допустимый размер, указанный в форме';
        case UPLOAD_ERR_PARTIAL:
            return 'Файл был загружен только частично';
        case UPLOAD_ERR_NO_FILE:
            return 'Файл не был загружен';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Отсутствует временная папка';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Не удалось записать файл на диск';
        case UPLOAD_ERR_EXTENSION:
            return 'Загрузка файла была остановлена расширением PHP';
        default:
            return 'Неизвестная ошибка загрузки';
    }
}