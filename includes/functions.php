<?php
/**
 * Файл с основными функциями сайта
 */

/**
 * Очистка входных данных
 */
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Перенаправление на указанный URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Создание slug из строки
 */
function createSlug($string) {
    // Транслитерация кириллицы
    $string = transliterateRussian($string);
    
    // Приведение к нижнему регистру
    $string = mb_strtolower($string, 'UTF-8');
    
    // Замена всех символов, кроме букв и цифр, на дефисы
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    
    // Удаление дефисов в начале и конце строки
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Транслитерация русских символов в латиницу
 */
function transliterateRussian($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',   'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',   'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',   'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',   'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch', 'ь' => '',    'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',   'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',   'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',   'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',   'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch', 'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    
    return strtr($string, $converter);
}

/**
 * Форматирование даты
 */
function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

/**
 * Безопасная обрезка текста до указанной длины
 * Не использует mb_strlen, который может быть недоступен
 */
function safeTextTruncate($text, $length = 100, $suffix = '...') {
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        // Если доступны многобайтовые функции, используем их
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
    } else {
        // Если многобайтовые функции недоступны, используем обычные
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
}

/**
 * Обрезка текста до указанной длины
 * Заменено на безопасную версию, которая проверяет наличие mb_* функций
 */
function truncateText($text, $length = 100, $suffix = '...') {
    return safeTextTruncate($text, $length, $suffix);
}

/**
 * Проверка авторизации пользователя
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Проверка прав администратора
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Установка флеш-сообщения
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Получение флеш-сообщения
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Загрузка изображения
 */
function uploadImage($file, $prefix = '') {
    // Проверка наличия файла
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'Ошибка загрузки файла: ' . uploadErrorMessage($file['error'])
        ];
    }
    
    // Проверка типа файла
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return [
            'success' => false,
            'error' => 'Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.'
        ];
    }
    
    // Проверка размера файла (150MB)
    $maxSize = 150 * 1024 * 1024; // 150MB
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'error' => 'Размер файла превышает допустимый (150MB).'
        ];
    }
    
    // Создание директории, если она не существует
    $uploadDir = UPLOADS_DIR;
    if (!empty($prefix)) {
        $uploadDir .= $prefix;
    }
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return [
                'success' => false,
                'error' => 'Не удалось создать директорию для загрузки. Проверьте права доступа.'
            ];
        }
    }
    
    // Проверка прав доступа
    if (!is_writable($uploadDir)) {
        return [
            'success' => false,
            'error' => 'Директория недоступна для записи. Установите права доступа 755 или 777.'
        ];
    }
    
    // Генерация уникального имени файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    // Перемещение файла
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Добавляем префикс к имени файла только для URL, если он указан
        $filenamePath = !empty($prefix) ? $prefix . $filename : $filename;
        
        return [
            'success' => true,
            'filename' => $filenamePath,
            'path' => $destination,
            'url' => UPLOADS_URL . $filenamePath
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Не удалось сохранить файл. Проверьте права доступа.'
        ];
    }
}

/**
 * Получение сообщения об ошибке загрузки файла
 */
function uploadErrorMessage($code) {
    switch ($code) {
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

/**
 * Получение текущего URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Получение статьи по slug
 */
function getContentBySlug($slug) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT * FROM content WHERE slug = ? AND status = 'published'";
    return $database->selectOne($sql, [$slug]);
}

/**
 * Получение категории по slug
 */
function getCategoryBySlug($slug) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT * FROM categories WHERE slug = ?";
    return $database->selectOne($sql, [$slug]);
}

/**
 * Получение статей по категории
 */
function getContentByCategory($categoryId, $limit = 10, $offset = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT c.*, cat.name as category_name 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE c.category_id = ? AND c.status = 'published' 
            ORDER BY c.created_at DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit;
    
    return $database->select($sql, [$categoryId]);
}

/**
 * Получение списка категорий
 */
function getCategories() {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    return $database->select("SELECT * FROM categories ORDER BY name", []);
}

/**
 * Обновление счетчика просмотров
 */
function updateViews($contentId) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $database->query("UPDATE content SET views = views + 1 WHERE id = ?", [$contentId]);
}

/**
 * Поиск статей
 */
function searchContent($query, $limit = 10, $offset = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE c.status = 'published' AND (c.title LIKE ? OR c.content LIKE ?) 
            ORDER BY c.created_at DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit;
    
    $searchParam = "%$query%";
    return $database->select($sql, [$searchParam, $searchParam]);
}

/**
 * Получение последних статей
 */
function getLatestContent($limit = 10, $offset = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE c.status = 'published' 
            ORDER BY c.created_at DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit;
    
    return $database->select($sql, []);
}

/**
 * Получение популярных статей
 */
function getPopularContent($limit = 10, $offset = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE c.status = 'published' 
            ORDER BY c.views DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit;
    
    return $database->select($sql, []);
}

/**
 * Получение случайных статей
 */
function getRandomContent($limit = 10, $excludeId = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE c.status = 'published'";
    
    if ($excludeId > 0) {
        $sql .= " AND c.id != ?";
        $params = [$excludeId];
    } else {
        $params = [];
    }
    
    $sql .= " ORDER BY RAND() LIMIT " . (int)$limit;
    
    return $database->select($sql, $params);
}

/**
 * Получение статей по тегу
 */
function getContentByTag($tag, $limit = 10, $offset = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN content_tags ct ON c.id = ct.content_id 
            WHERE c.status = 'published' AND ct.tag = ? 
            ORDER BY c.created_at DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit;
    
    return $database->select($sql, [$tag]);
}

/**
 * Получение популярных тегов
 */
function getPopularTags($limit = 20) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT tag, COUNT(*) as count 
            FROM content_tags ct 
            JOIN content c ON ct.content_id = c.id 
            WHERE c.status = 'published' 
            GROUP BY tag 
            ORDER BY count DESC 
            LIMIT " . (int)$limit;
    
    return $database->select($sql, []);
}

/**
 * Получение статьи по ID
 */
function getContentById($id) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT * FROM content WHERE id = ?";
    return $database->selectOne($sql, [$id]);
}

/**
 * Получение категории по ID
 */
function getCategoryById($id) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT * FROM categories WHERE id = ?";
    return $database->selectOne($sql, [$id]);
}

/**
 * Получение пользователя по ID
 */
function getUserById($id) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT id, username, email, role, created_at FROM users WHERE id = ?";
    return $database->selectOne($sql, [$id]);
}

/**
 * Получение пользователя по имени пользователя
 */
function getUserByUsername($username) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT * FROM users WHERE username = ?";
    return $database->selectOne($sql, [$username]);
}

/**
 * Получение пользователя по email
 */
function getUserByEmail($email) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT * FROM users WHERE email = ?";
    return $database->selectOne($sql, [$email]);
}

/**
 * Получение списка пользователей
 */
function getUsers($limit = 10, $offset = 0) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $sql = "SELECT id, username, email, role, created_at FROM users 
            ORDER BY created_at DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit;
    
    return $database->select($sql, []);
}

/**
 * Создание нового пользователя
 */
function createUser($username, $email, $password, $role = 'user') {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role
    ];
    
    return $database->insert('users', $data);
}

/**
 * Обновление пользователя
 */
function updateUser($id, $data) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    return $database->update('users', $data, 'id = ?', [$id]);
}

/**
 * Удаление пользователя
 */
function deleteUser($id) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    return $database->delete('users', 'id = ?', [$id]);
}

/**
 * Проверка пароля пользователя
 */
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

/**
 * Генерация случайного токена
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Проверка CSRF-токена
 */
function checkCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

/**
 * Генерация CSRF-токена
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Вывод CSRF-поля для формы
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Проверка прав доступа
 */
function checkPermission($permission) {
    // Проверка авторизации
    if (!isLoggedIn()) {
        return false;
    }
    
    // Администратор имеет все права
    if (isAdmin()) {
        return true;
    }
    
    // Проверка конкретного разрешения
    $userPermissions = $_SESSION['user_permissions'] ?? [];
    return in_array($permission, $userPermissions);
}