<?php
require_once 'bootstrap.php';

// Обработка удаления категории
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Проверка, есть ли статьи в этой категории
    $articlesCount = $database->selectOne("SELECT COUNT(*) as count FROM content WHERE category_id = ?", [$id])['count'] ?? 0;
    
    if ($articlesCount > 0) {
        setFlashMessage('danger', 'Невозможно удалить категорию, так как в ней есть статьи.');
        redirect(ADMIN_URL . '/manage-categories.php');
    }
    
    try {
        // Удаление категории
        $database->delete('categories', 'id = ?', [$id]);
        
        setFlashMessage('success', 'Категория успешно удалена!');
        redirect(ADMIN_URL . '/manage-categories.php');
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Ошибка при удалении категории: ' . $e->getMessage());
        redirect(ADMIN_URL . '/manage-categories.php');
    }
}

// Обработка формы добавления категории
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($name);
    $description = $_POST['description'] ?? '';
    $parentId = (int)($_POST['parent_id'] ?? 0);
    
    if (empty($name)) {
        $errors[] = 'Название категории не может быть пустым';
    } else {
        // Проверка уникальности slug
        $existingCategory = $database->selectOne("SELECT id FROM categories WHERE slug = ?", [$slug]);
        
        if ($existingCategory) {
            $slug = $slug . '-' . time();
        }
        
        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'parent_id' => $parentId > 0 ? $parentId : null
        ];
        
        $database->insert('categories', $data);
        
        setFlashMessage('success', 'Категория успешно добавлена!');
        redirect(ADMIN_URL . '/manage-categories.php');
    }
}

// Получение списка категорий
$categories = $database->select("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.name", []);

$pageTitle = 'Управление категориями';
include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Управление категориями</h2>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?>">
            <?php echo $_SESSION['flash_message']['message']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Добавление категории</h3>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="name">Название</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="slug">URL (slug)</label>
                            <input type="text" id="slug" name="slug" class="form-control">
                            <small>Оставьте пустым для автоматической генерации из названия</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_id">Родительская категория</label>
                            <select id="parent_id" name="parent_id" class="form-control">
                                <option value="0">Нет родительской категории</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Описание</label>
                            <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Добавить категорию</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Список категорий</h3>
                </div>
                <div class="card-body">
                    <?php if (count($categories) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>URL (slug)</th>
                                        <th>Родительская категория</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                            <td><?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : 'Нет'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/edit-category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Редактировать
                                                    </a>
                                                    <a href="<?php echo ADMIN_URL; ?>/manage-categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту категорию?');">
                                                        <i class="fas fa-trash"></i> Удалить
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Нет категорий для отображения.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Автоматическая генерация slug из названия
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (slugInput.value === '') {
                slugInput.value = createSlug(nameInput.value);
            }
        });
    }
    
    // Функция для создания slug
    function createSlug(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Заменяем пробелы на дефисы
            .replace(/[^\w\-]+/g, '')       // Удаляем все не-буквенно-цифровые символы
            .replace(/\-\-+/g, '-')         // Заменяем множественные дефисы на один
            .replace(/^-+/, '')             // Удаляем дефисы в начале
            .replace(/-+$/, '');            // Удаляем дефисы в конце
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>