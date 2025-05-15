<?php
$pageTitle = 'Панель управления';
require_once 'includes/admin-header.php';

// Получаем статистику
$totalContent = $database->selectOne("SELECT COUNT(*) as count FROM content")['count'];
$totalGhosts = $database->selectOne("SELECT COUNT(*) as count FROM ghosts")['count'];
$totalEquipment = $database->selectOne("SELECT COUNT(*) as count FROM equipment")['count'];
$totalRituals = $database->selectOne("SELECT COUNT(*) as count FROM rituals")['count'];
$totalCategories = $database->selectOne("SELECT COUNT(*) as count FROM categories")['count'];

// Получаем последние добавленные статьи
$latestContent = $database->select("
    SELECT c.*, cat.name as category_name 
    FROM content c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    ORDER BY c.created_at DESC 
    LIMIT 5
");

// Получаем популярные статьи
$popularContent = $database->select("
    SELECT c.*, cat.name as category_name 
    FROM content c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    ORDER BY c.views DESC 
    LIMIT 5
");
?>

<div class="dashboard-content">
    <div class="dashboard-header">
        <h2>Панель управления</h2>
        <p>Добро пожаловать, <?php echo $_SESSION['username']; ?>!</p>
    </div>
    
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalContent; ?></h3>
                <p>Статей</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ghost"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalGhosts; ?></h3>
                <p>Призраков</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalEquipment; ?></h3>
                <p>Оборудования</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalRituals; ?></h3>
                <p>Ритуалов</p>
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
    </div>
    
    <div class="dashboard-widgets">
        <div class="widget">
            <div class="widget-header">
                <h3>Последние добавленные статьи</h3>
                <a href="<?php echo ADMIN_URL; ?>/manage-content.php" class="btn btn-sm btn-primary">Все статьи</a>
            </div>
            
            <div class="widget-content">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Заголовок</th>
                            <th>Категория</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestContent as $content): ?>
                            <tr>
                                <td><?php echo $content['title']; ?></td>
                                <td><?php echo $content['category_name']; ?></td>
                                <td>
                                    <?php if ($content['status'] == 'published'): ?>
                                        <span class="badge badge-success">Опубликовано</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Черновик</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($content['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/edit-content.php?id=<?php echo $content['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $content['slug']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="widget">
            <div class="widget-header">
                <h3>Популярные статьи</h3>
            </div>
            
            <div class="widget-content">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Заголовок</th>
                            <th>Категория</th>
                            <th>Просмотры</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularContent as $content): ?>
                            <tr>
                                <td><?php echo $content['title']; ?></td>
                                <td><?php echo $content['category_name']; ?></td>
                                <td><?php echo $content['views']; ?></td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/edit-content.php?id=<?php echo $content['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $content['slug']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <div class="quick-action-header">
            <h3>Быстрые действия</h3>
        </div>
        
        <div class="quick-action-buttons">
            <a href="<?php echo ADMIN_URL; ?>/add-content.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить статью
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/add-ghost.php" class="btn btn-primary">
                <i class="fas fa-ghost"></i> Добавить призрака
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/add-equipment.php" class="btn btn-primary">
                <i class="fas fa-tools"></i> Добавить оборудование
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/add-ritual.php" class="btn btn-primary">
                <i class="fas fa-book"></i> Добавить ритуал
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/manage-categories.php" class="btn btn-secondary">
                <i class="fas fa-folder"></i> Управление категориями
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>