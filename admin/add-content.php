<?php
require_once 'bootstrap.php';

// Убедимся, что сессия запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Добавить статью';

// Проверка, была ли отправлена форма
$formSubmitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$success = false;
$error = '';

// Обработка формы
if ($formSubmitted) {
    $title = clean($_POST['title'] ?? '');
    $content = $_POST['content'] ?? ''; // Не очищаем, так как используем HTML-редактор
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $status = clean($_POST['status'] ?? 'draft');
    
    // Проверка обязательных полей
    if (empty($title)) {
        $error = 'Заголовок не может быть пустым';
    } elseif ($categoryId <= 0) {
        $error = 'Пожалуйста, выберите категорию';
    } else {
        // Создаем slug только если основные поля заполнены
        $slug = createSlug($title);
        
        // Проверяем, существует ли уже такой slug
        $existingSlug = $database->selectOne("SELECT id FROM content WHERE slug = ?", [$slug]);
        if ($existingSlug) {
            $slug = $slug . '-' . time();
        }
        
        // Загрузка изображения
        $featuredImage = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
            $uploadResult = uploadImage($_FILES['featured_image'], 'content/');
            if ($uploadResult['success']) {
                $featuredImage = $uploadResult['filename']; // Исправлено с file_name на filename
            } else {
                $error = $uploadResult['error']; // Исправлено с message на error
            }
        }
        
        // Если нет ошибок, добавляем статью
        if (empty($error)) {
            try {
                // Подготавливаем данные для вставки
                // Включаем только те поля, которые точно есть в таблице
                $data = [
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                    'category_id' => $categoryId,
                    'featured_image' => $featuredImage,
                    'status' => $status
                ];
                
                // Логируем данные перед вставкой для отладки
                error_log("Attempting to insert article: " . json_encode($data));
                
                // Выполняем вставку
                $contentId = $database->insert('content', $data);
                
                if ($contentId) {
                    // Сохраняем ID статьи в сессии для отображения сообщения об успехе
                    $_SESSION['content_added'] = true;
                    
                    // Перенаправляем на ту же страницу, чтобы избежать повторной отправки формы при обновлении
                    redirect(ADMIN_URL . '/add-content.php?success=1');
                } else {
                    $error = 'Произошла ошибка при добавлении статьи. Проверьте логи сервера.';
                    error_log("DB insert failed: " . json_encode($data));
                }
            } catch (Exception $e) {
                // Полное сообщение об ошибке для отладки
                $fullError = $e->getMessage();
                error_log("DB Error: " . $fullError);
                $error = 'Ошибка базы данных: ' . $fullError;
            }
        }
    }
}

// Проверяем, было ли успешное добавление (после перенаправления)
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['content_added'])) {
    $success = true;
    // Удаляем флаг из сессии, чтобы сообщение не показывалось при последующих обновлениях
    unset($_SESSION['content_added']);
}

// Получаем список категорий
$categories = getCategories();

include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Добавить статью</h2>
        <a href="<?php echo ADMIN_URL; ?>/manage-content.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад к списку
        </a>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Статья успешно добавлена!
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="title">Заголовок <span class="required">*</span></label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="category_id">Категория <span class="required">*</span></label>
            <select id="category_id" name="category_id" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="featured_image">Изображение</label>
            <input type="file" id="featured_image" name="featured_image">
            <small>Рекомендуемый размер: 1200x800 пикселей</small>
        </div>
        
        <div class="form-group">
            <label for="content">Содержание <span class="required">*</span></label>
            <textarea id="content" name="content" class="wysiwyg-editor"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Статус</label>
            <select id="status" name="status" required>
                <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>Опубликовано</option>
                <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Черновик</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Добавить статью</button>
        </div>
    </form>
</div>

<style>
.required {
    color: red;
}
</style>

<?php include 'includes/admin-footer.php'; ?>