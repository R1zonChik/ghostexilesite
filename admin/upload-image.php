<?php
require_once 'bootstrap.php';

$errors = [];
$success = false;
$uploadedFile = '';
$successMessage = '';

/**
 * Функция для форматирования размера файла
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Обработка удаления изображения
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $fileName = basename($_GET['delete']);
    $filePath = UPLOADS_DIR . $fileName;
    
    if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
            $successMessage = 'Изображение успешно удалено!';
        } else {
            $errors[] = 'Не удалось удалить изображение. Проверьте права доступа.';
        }
    } else {
        $errors[] = 'Файл не найден.';
    }
}

// Обработка загрузки изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
        // Проверка размера файла (150MB)
        $maxSize = 150 * 1024 * 1024; // 150MB в байтах
        if ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Размер файла превышает допустимый (150MB)';
        } else {
            // Проверка типа файла
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $errors[] = 'Недопустимый тип файла. Разрешены только JPG, PNG, GIF, WEBP.';
            } else {
                // Загрузка изображения
                $upload = uploadImage($_FILES['image'], '');
                
                if ($upload['success']) {
                    $_SESSION['image_uploaded'] = true;
                    $_SESSION['uploaded_file'] = $upload['filename'];
                    redirect(ADMIN_URL . '/upload-image.php?success=1');
                } else {
                    $errors[] = $upload['error'];
                }
            }
        }
    } else {
        // Проверка ошибок загрузки
        if (isset($_FILES['image'])) {
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errors[] = 'Размер файла превышает максимально допустимый размер, указанный в php.ini';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'Размер файла превышает максимально допустимый размер, указанный в форме';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'Файл был загружен только частично';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'Выберите файл для загрузки';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errors[] = 'Отсутствует временная папка';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errors[] = 'Не удалось записать файл на диск';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errors[] = 'Загрузка файла была остановлена расширением PHP';
                    break;
                default:
                    $errors[] = 'Выберите файл для загрузки';
            }
        } else {
            $errors[] = 'Выберите файл для загрузки';
        }
    }
}

// Проверка успешной загрузки
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['image_uploaded'])) {
    $success = true;
    $uploadedFile = $_SESSION['uploaded_file'] ?? '';
    $successMessage = 'Изображение успешно загружено!';
    unset($_SESSION['image_uploaded']);
    unset($_SESSION['uploaded_file']);
}

// Получение списка загруженных изображений
$uploadedImages = [];
$uploadsDir = UPLOADS_DIR;

// Проверка существования директории
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

if (is_dir($uploadsDir) && $handle = opendir($uploadsDir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && !is_dir($uploadsDir . $entry)) {
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $uploadedImages[] = [
                    'name' => $entry,
                    'url' => UPLOADS_URL . $entry,
                    'size' => filesize($uploadsDir . $entry),
                    'date' => filemtime($uploadsDir . $entry)
                ];
            }
        }
    }
    closedir($handle);
}

// Сортировка по дате (новые сверху)
usort($uploadedImages, function($a, $b) {
    return $b['date'] - $a['date'];
});

$pageTitle = 'Загрузка изображений';
include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Загрузка изображений</h2>
    </div>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="upload-form-container">
        <form action="" method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="MAX_FILE_SIZE" value="157286400" /> <!-- 150MB в байтах -->
            <div class="form-group">
                <label for="image">Выберите изображение</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Максимальный размер файла: 150MB. Поддерживаемые форматы: JPG, PNG, GIF, WEBP.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Загрузить</button>
            </div>
        </form>
    </div>
    
    <?php if ($success && $uploadedFile): ?>
        <div class="uploaded-image-preview">
            <h3>Загруженное изображение</h3>
            <div class="image-preview">
                <img src="<?php echo UPLOADS_URL . $uploadedFile; ?>" alt="Загруженное изображение" onerror="this.src='<?php echo ADMIN_URL; ?>/assets/img/image-placeholder.png'; this.alt='Изображение недоступно';">
            </div>
            <div class="image-url">
                <p>URL изображения:</p>
                <input type="text" value="<?php echo UPLOADS_URL . $uploadedFile; ?>" readonly onclick="this.select()">
            </div>
        </div>
    <?php endif; ?>
    
    <div class="uploaded-images">
        <h3>Загруженные изображения</h3>
        
        <?php if (count($uploadedImages) > 0): ?>
            <div class="image-gallery">
                <?php foreach ($uploadedImages as $image): ?>
                    <div class="image-item">
                        <div class="image-thumbnail">
                            <a href="<?php echo $image['url']; ?>" target="_blank">
                                <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['name']; ?>" onerror="this.src='<?php echo ADMIN_URL; ?>/assets/img/image-placeholder.png'; this.alt='Изображение недоступно';">
                            </a>
                        </div>
                        <div class="image-info">
                            <p class="image-name"><?php echo $image['name']; ?></p>
                            <p class="image-size"><?php echo formatFileSize($image['size']); ?></p>
                            <p class="image-date"><?php echo date('d.m.Y H:i', $image['date']); ?></p>
                        </div>
                        <div class="image-actions">
                            <button class="btn btn-sm btn-info copy-url" data-url="<?php echo $image['url']; ?>">
                                <i class="fas fa-copy"></i> Копировать URL
                            </button>
                            <a href="<?php echo $image['url']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Открыть
                            </a>
                            <a href="<?php echo ADMIN_URL; ?>/upload-image.php?delete=<?php echo $image['name']; ?>" 
                               class="btn btn-sm btn-danger delete-image" 
                               onclick="return confirm('Вы уверены, что хотите удалить это изображение?');">
                                <i class="fas fa-trash"></i> Удалить
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Нет загруженных изображений.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.upload-form-container {
    background-color: var(--dark-card);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid var(--border-color);
}

.uploaded-image-preview {
    background-color: var(--dark-card);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid var(--border-color);
}

.image-preview {
    margin-bottom: 15px;
    text-align: center;
}

.image-preview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 4px;
    object-fit: contain;
}

.image-url {
    margin-top: 15px;
}

.image-url input {
    width: 100%;
    padding: 10px;
    background-color: rgba(0, 0, 0, 0.2);
    border: 1px solid var(--border-color);
    color: var(--light-text);
    border-radius: 4px;
}

.uploaded-images {
    background-color: var(--dark-card);
    border-radius: 8px;
    padding: 20px;
    border: 1px solid var(--border-color);
}

.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.image-item {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    transition: transform 0.3s ease;
}

.image-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.image-thumbnail {
    height: 150px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.3);
}

.image-thumbnail img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.image-info {
    padding: 10px;
    border-top: 1px solid var(--border-color);
}

.image-name {
    font-weight: 500;
    margin-bottom: 5px;
    word-break: break-all;
}

.image-size, .image-date {
    font-size: 0.8rem;
    color: var(--muted-text);
    margin-bottom: 5px;
}

.image-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 10px;
    border-top: 1px solid var(--border-color);
}

.image-actions .btn {
    flex: 1;
    font-size: 0.8rem;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 100px;
}

.image-actions .btn i {
    margin-right: 5px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

@media (max-width: 768px) {
    .image-gallery {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .image-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .image-gallery {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Копирование URL изображения
    const copyButtons = document.querySelectorAll('.copy-url');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const tempInput = document.createElement('input');
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Изменение текста кнопки на короткое время
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Скопировано!';
            
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });
});
</script>

<?php include 'includes/admin-footer.php'; ?>