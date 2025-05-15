<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/init.php';

// Получение новостей
$newsArticles = [];
try {
    $newsCategory = $database->selectOne("SELECT id FROM categories WHERE slug = 'novosti' OR slug = 'news'", []);
    if ($newsCategory) {
        $newsArticles = $database->select(
            "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
             FROM content c 
             LEFT JOIN categories cat ON c.category_id = cat.id 
             WHERE c.status = 'published' AND c.category_id = ?
             ORDER BY c.created_at DESC 
             LIMIT 6",
            [$newsCategory['id']]
        );
    }
} catch (Exception $e) {
    // Логирование ошибки
    error_log("Ошибка при получении новостей: " . $e->getMessage());
}

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

$pageTitle = 'Главная';
$pageDescription = defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : 'Полное руководство по игре Ghost Exile - призраки, улики, оборудование и стратегии';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- Об игре Ghost Exile -->
            <section class="main-content">
                <h2 class="section-title">Об игре Ghost Exile</h2>
                
                <div class="about-game">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="about-image">
                                <img src="<?php echo SITE_URL; ?>/assets/images/ghost-exile-game.jpg" alt="Ghost Exile Game" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="about-content">
                                <p>Ghost Exile — это захватывающая кооперативная хоррор-игра, в которой вы и ваши друзья становитесь охотниками за привидениями. Ваша задача — исследовать различные локации, собирать улики и определять типы призраков, используя специальное оборудование.</p>
                                
                                <p>В игре вам предстоит столкнуться с различными типами призраков, каждый из которых обладает уникальными способностями и характеристиками. Чтобы выжить и успешно выполнить задание, вам нужно будет работать в команде, эффективно использовать оборудование и правильно идентифицировать тип призрака.</p>
                                
                                <a href="<?php echo SITE_URL; ?>/about.php" class="read-more">
                                    Узнать больше об игре <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <?php if (count($newsArticles) > 0): ?>
            <!-- Новости -->
            <section class="main-content">
                <h2 class="section-title">Последние новости</h2>
                
                <div class="articles-list">
                    <?php foreach ($newsArticles as $article): ?>
                        <div class="article-item">
                            <div class="row">
                                <?php if (!empty($article['featured_image'])): ?>
                                    <div class="col-md-4">
                                        <div class="article-image">
                                            <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $article['slug']; ?>">
                                                <img src="<?php echo UPLOADS_URL . $article['featured_image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="img-fluid">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                <?php else: ?>
                                    <div class="col-md-12">
                                <?php endif; ?>
                                        <div class="article-body">
                                            <h3>
                                                <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $article['slug']; ?>">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </a>
                                            </h3>
                                            
                                            <div class="article-meta">
                                                <span class="date">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <?php echo formatDate($article['created_at']); ?>
                                                </span>
                                                
                                                <span class="views">
                                                    <i class="fas fa-eye"></i>
                                                    <?php echo $article['views']; ?> просмотров
                                                </span>
                                            </div>
                                            
                                            <div class="article-excerpt">
                                                <?php echo safeTextTruncate(strip_tags($article['content']), 200); ?>
                                            </div>
                                            
                                            <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $article['slug']; ?>" class="read-more">
                                                Читать далее <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>