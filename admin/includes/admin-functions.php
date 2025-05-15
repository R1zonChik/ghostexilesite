<?php
/**
 * Файл с функциями для админ-панели
 */

/**
 * Инициализация TinyMCE
 */
function initTinyMCE() {
    $tinyMCEKey = 'gzr6vzdc9dwdn70lt7h7ljuqdvuajb8l0t7mh0mu91ivd753';
    
    return "
    <script>
        tinymce.init({
            selector: '.wysiwyg-editor',
            apiKey: '{$tinyMCEKey}',
            height: 500,
            language: 'ru',
            language_url: '" . SITE_URL . "/assets/js/tinymce/langs/ru.js',
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | link image media | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            images_upload_url: '" . ADMIN_URL . "/upload-tinymce.php',
            automatic_uploads: true,
            file_picker_types: 'image media',
            images_reuse_filename: true,
            media_live_embeds: true,
            media_alt_source: false,
            media_poster: false,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            /* Локализация интерфейса */
            language_url: '" . SITE_URL . "/assets/js/tinymce/langs/ru.js',
            language: 'ru'
        });
    </script>";
}

/**
 * Проверка, является ли текущая страница активной
 */
function isActivePage($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page;
}

// Проверяем, существует ли уже функция setFlashMessage
if (!function_exists('setFlashMessage')) {
    /**
     * Установка флеш-сообщения
     */
    function setFlashMessage($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

// Проверяем, существует ли уже функция getFlashMessage
if (!function_exists('getFlashMessage')) {
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
}

/**
 * Вывод флеш-сообщения
 */
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $type = $message['type'];
        $text = $message['message'];
        
        echo "<div class=\"alert alert-{$type}\">{$text}</div>";
    }
}

/**
 * Генерация пагинации
 */
function generatePagination($currentPage, $totalPages, $url, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination"><ul>';
    
    // Предыдущая страница
    if ($currentPage > 1) {
        $prevParams = array_merge($params, ['page' => $currentPage - 1]);
        $prevUrl = $url . '?' . http_build_query($prevParams);
        $html .= '<li><a href="' . $prevUrl . '">&laquo; Предыдущая</a></li>';
    }
    
    // Номера страниц
    for ($i = 1; $i <= $totalPages; $i++) {
        $pageParams = array_merge($params, ['page' => $i]);
        $pageUrl = $url . '?' . http_build_query($pageParams);
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $html .= '<li><a href="' . $pageUrl . '" class="' . $activeClass . '">' . $i . '</a></li>';
    }
    
    // Следующая страница
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($params, ['page' => $currentPage + 1]);
        $nextUrl = $url . '?' . http_build_query($nextParams);
        $html .= '<li><a href="' . $nextUrl . '">Следующая &raquo;</a></li>';
    }
    
    $html .= '</ul></div>';
    
    return $html;
}

/**
 * Получение списка статусов статей
 */
function getContentStatuses() {
    return [
        'published' => 'Опубликовано',
        'draft' => 'Черновик'
    ];
}

/**
 * Получение списка ролей пользователей
 */
function getUserRoles() {
    return [
        'admin' => 'Администратор',
        'editor' => 'Редактор',
        'author' => 'Автор'
    ];
}

/**
 * Получение списка статей для админки
 */
function getAdminContent($page = 1, $perPage = 10, $filters = []) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/../../includes/database.php';
        require_once __DIR__ . '/../../includes/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT c.*, cat.name as category_name 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE 1=1";
    $params = [];
    
    // Применение фильтров
    if (!empty($filters['category_id'])) {
        $sql .= " AND c.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND c.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (c.title LIKE ? OR c.content LIKE ?)";
        $searchParam = "%" . $filters['search'] . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Получение общего количества записей
    $countSql = str_replace("c.*, cat.name as category_name", "COUNT(*) as count", $sql);
    $totalCount = $database->selectOne($countSql, $params)['count'] ?? 0;
    
    // Добавление сортировки и лимита
    $sql .= " ORDER BY c.created_at DESC LIMIT " . (int)$offset . ", " . (int)$perPage;
    
    // Получение данных
    $items = $database->select($sql, $params);
    
    return [
        'items' => $items,
        'total' => $totalCount,
        'pages' => ceil($totalCount / $perPage),
        'current_page' => $page
    ];
}

/**
 * Получение списка категорий для админки
 */
function getAdminCategories($page = 1, $perPage = 10, $filters = []) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/../../includes/database.php';
        require_once __DIR__ . '/../../includes/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT c.*, p.name as parent_name 
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            WHERE 1=1";
    $params = [];
    
    // Применение фильтров
    if (!empty($filters['parent_id'])) {
        $sql .= " AND c.parent_id = ?";
        $params[] = $filters['parent_id'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (c.name LIKE ? OR c.description LIKE ?)";
        $searchParam = "%" . $filters['search'] . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Получение общего количества записей
    $countSql = str_replace("c.*, p.name as parent_name", "COUNT(*) as count", $sql);
    $totalCount = $database->selectOne($countSql, $params)['count'] ?? 0;
    
    // Добавление сортировки и лимита
    $sql .= " ORDER BY c.name ASC LIMIT " . (int)$offset . ", " . (int)$perPage;
    
    // Получение данных
    $items = $database->select($sql, $params);
    
    return [
        'items' => $items,
        'total' => $totalCount,
        'pages' => ceil($totalCount / $perPage),
        'current_page' => $page
    ];
}

/**
 * Получение списка пользователей для админки
 */
function getAdminUsers($page = 1, $perPage = 10, $filters = []) {
    global $database;
    
    // Проверка инициализации базы данных
    if (!isset($database)) {
        require_once __DIR__ . '/../../includes/database.php';
        require_once __DIR__ . '/../../includes/config.php';
        $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    }
    
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT id, username, email, role, created_at 
            FROM users 
            WHERE 1=1";
    $params = [];
    
    // Применение фильтров
    if (!empty($filters['role'])) {
        $sql .= " AND role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (username LIKE ? OR email LIKE ?)";
        $searchParam = "%" . $filters['search'] . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Получение общего количества записей
    $countSql = str_replace("id, username, email, role, created_at", "COUNT(*) as count", $sql);
    $totalCount = $database->selectOne($countSql, $params)['count'] ?? 0;
    
    // Добавление сортировки и лимита
    $sql .= " ORDER BY created_at DESC LIMIT " . (int)$offset . ", " . (int)$perPage;
    
    // Получение данных
    $items = $database->select($sql, $params);
    
    return [
        'items' => $items,
        'total' => $totalCount,
        'pages' => ceil($totalCount / $perPage),
        'current_page' => $page
    ];
}