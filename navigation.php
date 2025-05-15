<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/init.php';

// Получение категорий для отображения в сайдбаре
$categories = [];
try {
    $categories = $database->select("SELECT * FROM categories ORDER BY name", []);
} catch (Exception $e) {
    // Логирование ошибки
    error_log("Ошибка при получении категорий: " . $e->getMessage());
}

// Оптимизированный подсчет статей для всех категорий одним запросом
$categoryCountsArr = [];
try {
    $categoryCountsArr = $database->select(
        "SELECT category_id, COUNT(*) as count FROM content WHERE status = 'published' GROUP BY category_id",
        []
    );
} catch (Exception $e) {
    error_log("Ошибка при подсчете статей по категориям: " . $e->getMessage());
}

// Преобразуем результат в удобный массив [id => count]
$categoryCounts = [];
foreach ($categoryCountsArr as $row) {
    $categoryCounts[$row['category_id']] = $row['count'];
}

$pageTitle = SITE_NAME;
$pageDescription = defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : 'Полное руководство по игре Ghost Exile';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- Категории -->
            <section class="main-content">
                <h2 class="section-title">Навигация</h2>
                
                <?php if (count($categories) > 0): ?>
                    <div class="content-grid">
                        <?php foreach ($categories as $category): ?>
                            <div class="content-card">
                                <div class="card-content">
                                    <h3 class="card-title">
                                        <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                            <span style="color:#bb86fc;">
                                                (<?php echo isset($categoryCounts[$category['id']]) ? $categoryCounts[$category['id']] : 0; ?>)
                                            </span>
                                        </a>
                                    </h3>
                                    
                                    <?php if (!empty($category['description'])): ?>
                                        <p><?php echo safeTextTruncate($category['description'], 100); ?></p>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>" class="read-more">
                                        Просмотреть <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Категории еще не добавлены.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
        
        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>