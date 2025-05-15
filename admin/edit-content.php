<?php
require_once 'bootstrap.php';

// Получение ID статьи из URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect(ADMIN_URL . '/manage-content.php');
}

// Получение данных статьи
$article = $database->selectOne("SELECT * FROM content WHERE id = ?", [$id]);

if (!$article) {
    redirect(ADMIN_URL . '/manage-content.php');
}

$pageTitle = 'Редактировать статью';

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
    $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($title);
    
    // Проверяем, существует ли уже такой slug у другой статьи
    $existingSlug = $database->selectOne("SELECT id FROM content WHERE slug = ? AND id != ?", [$slug, $id]);
    
    if ($existingSlug) {
        $slug = $slug . '-' . time();
    }
    
    // Загрузка изображения
    $featuredImage = $article['featured_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $uploadResult = uploadImage($_FILES['featured_image'], 'content/');
        if ($uploadResult['success']) {
            // Удаляем старое изображение, если оно существует
            if (!empty($featuredImage) && file_exists(UPLOADS_DIR . $featuredImage)) {
                @unlink(UPLOADS_DIR . $featuredImage);
            }
            $featuredImage = $uploadResult['filename'];
        } else {
            $error = $uploadResult['error'];
        }
    }
    
    if (empty($error)) {
        // Обновляем статью в базе данных
        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'category_id' => $categoryId,
            'featured_image' => $featuredImage,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Используем правильный формат для whereParams
        $updated = $database->update('content', $data, 'id = ?', [$id]);
        if ($updated) {
            // Сохраняем флаг в сессии для отображения сообщения об успехе
            $_SESSION['content_updated'] = true;
            
            // Перенаправляем на ту же страницу, чтобы избежать повторной отправки формы при обновлении
            redirect(ADMIN_URL . '/edit-content.php?id=' . $id . '&success=1');
        } else {
            $error = 'Произошла ошибка при обновлении статьи.';
        }
    }
}

// Проверяем, было ли успешное обновление (после перенаправления)
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['content_updated'])) {
    $success = true;
    // Удаляем флаг из сессии, чтобы сообщение не показывалось при последующих обновлениях
    unset($_SESSION['content_updated']);
}

// Получаем список категорий
$categories = getCategories();

include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Редактировать статью</h2>
        <a href="<?php echo ADMIN_URL; ?>/manage-content.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад �� списку
        </a>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Статья успешно обновлена!
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="title">Заголовок</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="slug">URL (slug)</label>
            <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($article['slug']); ?>">
            <small>Оставьте пустым для автоматической генерации из заголовка</small>
        </div>
        
        <div class="form-group">
            <label for="category_id">Категория</label>
            <select id="category_id" name="category_id" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($article['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="featured_image">Изображение</label>
            <?php if (!empty($article['featured_image'])): ?>
                <div class="current-image">
                    <img src="<?php echo UPLOADS_URL . $article['featured_image']; ?>" alt="Текущее изображение" style="max-width: 200px; margin-bottom: 10px;">
                    <p>Текущее изображение</p>
                </div>
            <?php endif; ?>
            <input type="file" id="featured_image" name="featured_image">
            <small>Рекомендуемый размер: 1200x800 пикселей. Оставьте пустым, чтобы сохранить текущее изображение.</small>
        </div>
        
        <div class="form-group">
            <label for="content">Содержание</label>
            <textarea id="content" name="content" class="wysiwyg-editor"><?php echo $article['content']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Статус</label>
            <select id="status" name="status" required>
                <option value="published" <?php echo ($article['status'] == 'published') ? 'selected' : ''; ?>>Опубликовано</option>
                <option value="draft" <?php echo ($article['status'] == 'draft') ? 'selected' : ''; ?>>Черновик</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Обновить статью</button>
        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>