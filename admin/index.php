<?php
require_once 'bootstrap.php';

$pageTitle = 'Панель управления';
include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';

// Получение статистики
$totalPosts = $database->selectOne("SELECT COUNT(*) as count FROM content", [])['count'] ?? 0;
$totalCategories = $database->selectOne("SELECT COUNT(*) as count FROM categories", [])['count'] ?? 0;
$totalUsers = $database->selectOne("SELECT COUNT(*) as count FROM users", [])['count'] ?? 0;
$recentPosts = $database->select("SELECT * FROM content ORDER BY created_at DESC LIMIT 5", []);
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Панель управления</h2>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalPosts; ?></h3>
                <p>Статей</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalCategories; ?></h3>
                <p>Категорий</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Пользователей</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-widgets">
        <div class="widget">
            <div class="widget-header">
                <h3>Последние статьи</h3>
                <a href="<?php echo ADMIN_URL; ?>/manage-content.php" class="btn btn-sm btn-primary">Все статьи</a>
            </div>
            <div class="widget-content">
                <?php if (count($recentPosts) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Заголовок</th>
                                <th>Категория</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPosts as $post): ?>
                                <?php 
                                $category = $database->selectOne("SELECT name FROM categories WHERE id = ?", [$post['category_id']]);
                                $categoryName = $category ? $category['name'] : 'Без категории';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($categoryName); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/edit-content.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Нет статей для отображения.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="widget">
            <div class="widget-header">
                <h3>Быстрые действия</h3>
            </div>
            <div class="widget-content">
                <div class="quick-actions">
                    <a href="<?php echo ADMIN_URL; ?>/add-content.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Добавить статью
                    </a>
                    <a href="<?php echo ADMIN_URL; ?>/manage-categories.php" class="btn btn-secondary">
                        <i class="fas fa-folder-plus"></i> Управление категориями
                    </a>
                    <a href="<?php echo ADMIN_URL; ?>/upload-image.php" class="btn btn-info">
                        <i class="fas fa-upload"></i> Загрузить изображение
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>