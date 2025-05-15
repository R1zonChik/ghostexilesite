<?php
require_once 'bootstrap.php';

// Убедимся, что сессия запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Переменная для хранения ошибок
$error = '';

try {
    // Обработка массового удаления
    if (isset($_POST['bulk_action']) && $_POST['bulk_action'] === 'delete' && isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
        $selectedIds = array_map('intval', $_POST['selected_items']);
        
        if (!empty($selectedIds)) {
            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            // Используем метод delete вместо query
            $database->delete('content', "id IN ($placeholders)", $selectedIds);
            
            $_SESSION['bulk_deleted'] = true;
            redirect(ADMIN_URL . '/manage-content.php?bulk_deleted=1');
        }
    }

    // Обработка удаления
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $database->delete('content', 'id = ?', [$id]);
        
        $_SESSION['content_deleted'] = true;
        redirect(ADMIN_URL . '/manage-content.php?deleted=1');
    }

    // Проверка успешного удаления
    $successMessage = '';
    if (isset($_GET['deleted']) && $_GET['deleted'] == 1 && isset($_SESSION['content_deleted'])) {
        $successMessage = 'Статья успешно удалена!';
        unset($_SESSION['content_deleted']);
    } elseif (isset($_GET['bulk_deleted']) && $_GET['bulk_deleted'] == 1 && isset($_SESSION['bulk_deleted'])) {
        $successMessage = 'Выбранные статьи успешно удалены!';
        unset($_SESSION['bulk_deleted']);
    }

    // Параметры пагинации
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Параметры фильтрации
    $categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    $statusFilter   = isset($_GET['status'])   ? $_GET['status']        : '';
    $searchQuery    = isset($_GET['search'])   ? trim($_GET['search'])  : '';

    // Формирование SQL запроса с фильтрами
    $sql = "SELECT c.*, cat.name as category_name FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            WHERE 1=1";
    $params = [];

    if ($categoryFilter > 0) {
        $sql      .= " AND c.category_id = ?";
        $params[]  = $categoryFilter;
    }

    if ($statusFilter !== '') {
        $sql      .= " AND c.status = ?";
        $params[]  = $statusFilter;
    }

    if ($searchQuery !== '') {
        $sql      .= " AND (c.title LIKE ? OR c.content LIKE ?)";
        $params[]  = "%$searchQuery%";
        $params[]  = "%$searchQuery%";
    }

    // Логируем SQL и параметры для отладки
    error_log("SQL: " . $sql);
    error_log("Params: " . json_encode($params));

    // Получение общего количества записей
    $countSql = str_replace("c.*, cat.name as category_name", "COUNT(*) as count", $sql);
    $countResult = $database->selectOne($countSql, $params);
    $totalCount = $countResult['count'] ?? 0;
    $totalPages = ceil($totalCount / $perPage);

    // Добавление сортировки и лимита
    $sql .= " ORDER BY c.created_at DESC LIMIT $offset, $perPage";

    // Получение списка статей
    $articles = $database->select($sql, $params);

    // Получение списка категорий для фильтра
    $categories = $database->select("SELECT * FROM categories ORDER BY name", []);

} catch (Exception $e) {
    // Логируем ошибку и сохраняем для отображения
    error_log("Error in manage-content.php: " . $e->getMessage());
    $error = "Ошибка: " . $e->getMessage();
}

$pageTitle = 'Управление статьями';
include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Управление статьями</h2>
        <a href="<?php echo ADMIN_URL; ?>/add-content.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Добавить статью
        </a>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    
    <form action="" method="GET" class="filter-panel">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="category">Категория:</label>
                <div class="custom-select-wrapper">
                    <select id="category" name="category" class="form-control custom-select">
                        <option value="0">Все категории</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($categoryFilter == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="select-arrow"></div>
                </div>
            </div>
            
            <div class="filter-group">
                <label for="status">Статус:</label>
                <div class="custom-select-wrapper">
                    <select id="status" name="status" class="form-control custom-select">
                        <option value="">Все статусы</option>
                        <option value="published" <?php echo ($statusFilter == 'published') ? 'selected' : ''; ?>>Опубликовано</option>
                        <option value="draft" <?php echo ($statusFilter == 'draft') ? 'selected' : ''; ?>>Черновик</option>
                    </select>
                    <div class="select-arrow"></div>
                </div>
            </div>
            
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <div class="search-input-group">
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Поиск..." class="form-control">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Применить</button>
            <a href="<?php echo ADMIN_URL; ?>/manage-content.php" class="btn btn-secondary">Сбросить</a>
        </div>
    </form>
    
    <form action="" method="POST" id="content-form">
        <input type="hidden" name="bulk_action" id="bulk_action" value="">
        
        <div class="bulk-actions">
            <div class="select-all-controls">
                <button type="button" id="select-all" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check-square"></i> Выбрать все
                </button>
                <button type="button" id="deselect-all" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-square"></i> Снять выделение
                </button>
            </div>
            
            <div class="bulk-actions-buttons">
                <button type="button" id="delete-selected" class="btn btn-sm btn-danger" disabled>
                    <i class="fas fa-trash"></i> Удалить выбранные
                </button>
            </div>
        </div>
        
        <?php if (!empty($articles)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40">
                                <div class="form-check">
                                    <input type="checkbox" id="check-all" class="form-check-input">
                                </div>
                            </th>
                            <th>Заголовок</th>
                            <th>Категория</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" name="selected_items[]" value="<?php echo $article['id']; ?>" class="form-check-input item-checkbox">
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($article['title']); ?></td>
                                <td><?php echo htmlspecialchars($article['category_name'] ?? 'Без категории'); ?></td>
                                <td>
                                    <span class="badge <?php echo ($article['status'] == 'published') ? 'badge-success' : 'badge-secondary'; ?>">
                                        <?php echo ($article['status'] == 'published') ? 'Опубликовано' : 'Черновик'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $article['slug']; ?>" class="btn btn-sm btn-info" target="_blank" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/edit-content.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/manage-content.php?delete=<?php echo $article['id']; ?>" class="btn btn-sm btn-danger delete-item" title="Удалить" data-confirm="Вы уверены, что хотите удалить эту статью?">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <ul>
                        <?php if ($page > 1): ?>
                            <li>
                                <a href="<?php echo ADMIN_URL; ?>/manage-content.php?page=<?php echo $page - 1; ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                    &laquo; Предыдущая
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <a href="<?php echo ADMIN_URL; ?>/manage-content.php?page=<?php echo $i; ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li>
                                <a href="<?php echo ADMIN_URL; ?>/manage-content.php?page=<?php echo $page + 1; ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                    Следующая &raquo;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <p>Нет статей для отображения.</p>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="confirmation-modal" id="delete-confirmation">
    <div class="confirmation-content">
        <h3>Подтверждение удаления</h3>
        <p>Вы уверены, что хотите удалить выбранные статьи?</p>
        <div class="confirmation-buttons">
            <button id="confirm-delete" class="btn btn-danger">Удалить</button>
            <button id="cancel-delete" class="btn btn-secondary">Отмена</button>
        </div>
    </div>
</div>

<style>
/* Основные переменные темной темы */
:root {
    --dark-bg: rgba(18, 18, 18, 0.7);
    --darker-bg: rgba(10, 10, 10, 0.8);
    --dark-card: rgba(30, 30, 30, 0.8);
    --darker-card: rgba(24, 24, 24, 0.85);
    --light-text: #ffffff;
    --muted-text: #b0b0b0;
    --border-color: #333333;
    --primary-color: #6a1b9a;
    --primary-dark: #4a148c;
    --primary-light: #9c4dcc;
    --accent-color: #e040fb;
    --accent-light: #ea80fc;
    --success-color: #4caf50;
    --danger-color: #f44336;
    --info-color: #2196f3;
    --warning-color: #ff9800;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.5);
    --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.6);
    --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.7);
}

/* Фоновое изображение для всей страницы */
body {
    background-image: url('https://ghost-exile.site/img/3840_2140.png');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: -1;
}

/* Стили для фильтров */
.filter-panel {
    background-color: var(--darker-card);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(5px);
}

.filter-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--light-text);
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    background-color: var(--darker-bg);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    color: var(--light-text);
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    background-color: rgba(10, 10, 10, 0.9);
    box-shadow: 0 0 0 2px rgba(106, 27, 154, 0.2);
}

.custom-select-wrapper {
    position: relative;
    width: 100%;
}

.custom-select {
    appearance: none;
    padding-right: 30px;
    background-color: var(--darker-bg);
    cursor: pointer;
}

.select-arrow {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid var(--light-text);
    pointer-events: none;
}

.search-input-group {
    position: relative;
    display: flex;
}

.search-input-group input {
    flex: 1;
    padding-right: 40px;
    background-color: var(--darker-bg);
}

.search-btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 40px;
    background: transparent;
    border: none;
    color: var(--muted-text);
    cursor: pointer;
    transition: color 0.3s ease;
}

.search-btn:hover {
    color: var(--light-text);
}

.filter-actions {
    display: flex;
    gap: 10px;
}

/* Стили для массовых действий */
.bulk-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
}

.select-all-controls {
    display: flex;
    gap: 10px;
}

.bulk-actions-buttons {
    display: flex;
    gap: 10px;
}

.btn-outline-primary {
    background-color: rgba(10, 10, 10, 0.6);
    border: 1px solid var(--primary-color);
    color: var(--primary-light);
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: var(--light-text);
}

.btn-outline-secondary {
    background-color: rgba(10, 10, 10, 0.6);
    border: 1px solid var(--muted-text);
    color: var(--muted-text);
}

.btn-outline-secondary:hover {
    background-color: rgba(30, 30, 30, 0.8);
    color: var(--light-text);
}

/* Стили для таблицы */
.table-responsive {
    overflow-x: auto;
    background-color: var(--darker-card);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    backdrop-filter: blur(5px);
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.table th {
    background-color: rgba(10, 10, 10, 0.6);
    font-weight: 500;
    color: var(--light-text);
}

.table tbody tr {
    transition: background-color 0.3s ease;
}

.table tbody tr:hover {
    background-color: rgba(10, 10, 10, 0.4);
}

.form-check {
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary-color);
    background-color: rgba(10, 10, 10, 0.6);
    border: 1px solid var(--border-color);
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background-color: var(--success-color);
    color: #fff;
}

.badge-secondary {
    background-color: #6c757d;
    color: #fff;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

/* Модальное окно подтверждения */
.confirmation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
}

.confirmation-content {
    background-color: var(--darker-card);
    border-radius: 8px;
    padding: 25px;
    width: 400px;
    max-width: 90%;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
}

.confirmation-content h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--light-text);
}

.confirmation-content p {
    color: var(--muted-text);
}

.confirmation-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

/* Кнопки */
.btn {
    display: inline-block;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.875rem;
}

.btn-primary {
    background-color: var(--primary-color);
    color: var(--light-text);
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.btn-secondary {
    background-color: rgba(85, 85, 85, 0.8);
    color: var(--light-text);
}

.btn-secondary:hover {
    background-color: rgba(68, 68, 68, 0.9);
}

.btn-danger {
    background-color: var(--danger-color);
    color: var(--light-text);
}

.btn-danger:hover {
    background-color: #d32f2f;
}

.btn-info {
    background-color: var(--info-color);
    color: var(--light-text);
}

.btn-info:hover {
    background-color: #0b7dda;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Пагинация */
.pagination {
    margin-top: 20px;
}

.pagination ul {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    justify-content: center;
    flex-wrap: wrap;
    gap: 5px;
}

.pagination ul li a {
    display: block;
    padding: 8px 12px;
    background-color: var(--darker-card);
    border: 1px solid var(--border-color);
    color: var(--light-text);
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.pagination ul li a:hover {
    background-color: rgba(10, 10, 10, 0.7);
}

.pagination ul li a.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Уведомления */
.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    backdrop-filter: blur(5px);
}

.alert-success {
    background-color: rgba(76, 175, 80, 0.2);
    border: 1px solid rgba(76, 175, 80, 0.3);
    color: #4caf50;
}

.alert-danger {
    background-color: rgba(244, 67, 54, 0.2);
    border: 1px solid rgba(244, 67, 54, 0.3);
    color: #f44336;
}

.no-results {
    padding: 20px;
    text-align: center;
    background-color: var(--darker-card);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    color: var(--muted-text);
    backdrop-filter: blur(5px);
}

/* Эффект свечения для элементов */
.btn-primary, .badge-success, .pagination ul li a.active {
    box-shadow: 0 0 10px rgba(106, 27, 154, 0.3);
}

.btn-danger {
    box-shadow: 0 0 10px rgba(244, 67, 54, 0.3);
}

.btn-info {
    box-shadow: 0 0 10px rgba(33, 150, 243, 0.3);
}

/* Адаптивность */
@media (max-width: 768px) {
    .filter-controls {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .bulk-actions {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}

/* Стили для админ-контента */
.admin-content {
    padding: 20px;
    background-color: rgba(18, 18, 18, 0.6);
    border-radius: 8px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(5px);
    margin-bottom: 20px;
}

.admin-content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
}

.admin-content-header h2 {
    color: var(--light-text);
    margin: 0;
    text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка выбора всех элементов
    const checkAll = document.getElementById('check-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const deleteSelectedBtn = document.getElementById('delete-selected');
    const bulkActionInput = document.getElementById('bulk_action');
    const contentForm = document.getElementById('content-form');
    const deleteConfirmation = document.getElementById('delete-confirmation');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    
    // Кнопки выбора всех/снятия выделения
    const selectAllBtn = document.getElementById('select-all');
    const deselectAllBtn = document.getElementById('deselect-all');
    
    // Функция обновления состояния кнопки удаления
    function updateDeleteButtonState() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        deleteSelectedBtn.disabled = checkedItems.length === 0;
    }
    
    // Обработчик для чекбокса "выбрать все"
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDeleteButtonState();
        });
    }
    
    // Обработчик для отдельных чекбоксов
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateDeleteButtonState();
            
            // Обновление состояния главного чекбокса
            const allChecked = document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length;
            if (checkAll) {
                checkAll.checked = allChecked;
            }
        });
    });
    
    // Кнопка "Выбрать все"
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            if (checkAll) {
                checkAll.checked = true;
            }
            updateDeleteButtonState();
        });
    }
    
    // Кнопка "Снять выделение"
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            if (checkAll) {
                checkAll.checked = false;
            }
            updateDeleteButtonState();
        });
    }
    
    // Кнопка "Удалить выбранные"
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length > 0) {
                deleteConfirmation.style.display = 'flex';
            }
        });
    }
    
    // Подтверждение удаления
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            bulkActionInput.value = 'delete';
            contentForm.submit();
        });
    }
    
    // Отмена удаления
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            deleteConfirmation.style.display = 'none';
        });
    }
    
    // Обработчики для кнопок удаления отдельных элементов
    const deleteButtons = document.querySelectorAll('.delete-item');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
    
    // Инициализация состояния кнопки удаления
    updateDeleteButtonState();
});
</script>

<?php include 'includes/admin-footer.php'; ?>