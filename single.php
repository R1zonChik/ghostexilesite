<?php
require_once 'includes/init.php';

// Обработка ошибок
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error in single.php: [$errno] $errstr in $errfile on line $errline");
    return true;
});

try {
    // Получение slug из URL
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

    if (empty($slug)) {
        // Перенаправление на главную, если slug не указан
        header('Location: ' . SITE_URL);
        exit;
    }

    // Получение статьи по slug
    $article = [];
    try {
        $article = $database->selectOne(
            "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
             FROM content c 
             LEFT JOIN categories cat ON c.category_id = cat.id 
             WHERE c.slug = ? AND c.status = 'published'",
            [$slug]
        );
    } catch (Exception $e) {
        // Логирование ошибки
        error_log("Ошибка при получении статьи: " . $e->getMessage());
    }

    // Если статья не найдена, перенаправляем на 404
    if (empty($article)) {
        header('Location: ' . SITE_URL . '/404.php');
        exit;
    }

    // Обработка просмотров статьи
    try {
        // Создаем таблицу для отслеживания просмотров, если она не существует
        $database->execute("
            CREATE TABLE IF NOT EXISTS post_views (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent VARCHAR(255) NOT NULL,
                viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (post_id, ip_address(40))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Получаем IP-адрес посетителя
        $ipAddress = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        // Получаем User-Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Ограничиваем длину для предотвращения ошибок
        $ipAddress = substr($ipAddress, 0, 45);
        $userAgent = substr($userAgent, 0, 255);

        // Проверяем, просматривал ли уже этот посетитель данную статью за последние 24 часа
        $hasViewed = false;
        $result = $database->selectOne("
            SELECT id FROM post_views 
            WHERE post_id = ? AND ip_address = ? AND user_agent = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ", [$article['id'], $ipAddress, $userAgent]);
        
        $hasViewed = !empty($result);

        // Если посетитель еще не просматривал статью, увеличиваем счетчик
        if (!$hasViewed) {
            // Записываем информацию о просмотре
            $database->execute("
                INSERT INTO post_views (post_id, ip_address, user_agent) 
                VALUES (?, ?, ?)
            ", [$article['id'], $ipAddress, $userAgent]);
            
            // Увеличиваем счетчик просмотров
            $database->execute("
                UPDATE content SET views = views + 1 WHERE id = ?
            ", [$article['id']]);
            
            // Обновляем значение просмотров в текущем объекте статьи
            $article['views'] = (int)$article['views'] + 1;
        }
    } catch (Exception $e) {
        // Логируем ошибку, но продолжаем выполнение скрипта
        error_log("Ошибка при обработке просмотров: " . $e->getMessage());
    }

    // Получение связанных статей из той же категории
    $relatedArticles = [];
    if (!empty($article['category_id'])) {
        try {
            $relatedArticles = $database->select(
                "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
                 FROM content c 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE c.category_id = ? AND c.id != ? AND c.status = 'published' 
                 ORDER BY c.created_at DESC 
                 LIMIT 4",
                [$article['category_id'], $article['id']]
            );
        } catch (Exception $e) {
            // Логирование ошибки
            error_log("Ошибка при получении связанных статей: " . $e->getMessage());
        }
    }

    // Получение тегов статьи (если есть таблица с тегами)
    $articleTags = [];
    try {
        $articleTags = $database->select(
            "SELECT tag FROM content_tags WHERE content_id = ?",
            [$article['id']]
        );
    } catch (Exception $e) {
        // Таблица тегов может отсутствовать, игнорируем ошибку
        error_log("Примечание: таблица тегов не найдена или другая ошибка: " . $e->getMessage());
    }

    // Установка заголовка и описания страницы
    $pageTitle = $article['title'];
    $pageDescription = !empty($article['meta_description']) ? $article['meta_description'] : truncateText(strip_tags($article['content']), 160);

    include 'includes/header.php';
} catch (Exception $e) {
    // Логирование критической ошибки
    error_log("Критическая ошибка в single.php: " . $e->getMessage());
    
    // Отображение страницы с ошибкой
    header("HTTP/1.1 500 Internal Server Error");
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка сервера</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #121212;
                color: #fff;
                text-align: center;
                padding: 50px;
                margin: 0;
            }
            .error-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: rgba(0, 0, 0, 0.3);
                padding: 30px;
                border-radius: 8px;
            }
            h1 {
                color: #ff5f52;
            }
            .error-code {
                display: inline-block;
                background-color: #ff5f52;
                color: #fff;
                padding: 5px 15px;
                border-radius: 4px;
                font-weight: bold;
                margin-bottom: 20px;
            }
            .home-link {
                display: inline-block;
                margin-top: 20px;
                background-color: #ff5f52;
                color: #fff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
            }
            .home-link:hover {
                background-color: #e54942;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-code">HTTP ERROR 500</div>
            <h1>СТРАНИЦА НЕДОСТУПНА</h1>
            <p>Сайт временно не может обработать этот запрос.</p>
            <a href="' . SITE_URL . '" class="home-link">Вернуться на главную</a>
        </div>
    </body>
    </html>';
    exit;
}
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <section class="main-content">
                <article class="single-article">
                    <div class="article-header">
                        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                        
                        <div class="article-meta">
                            <?php if (!empty($article['category_name'])): ?>
                                <span class="category">
                                    <i class="fas fa-folder"></i>
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $article['category_slug']; ?>">
                                        <?php echo htmlspecialchars($article['category_name']); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <span class="date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo formatDate($article['created_at']); ?>
                            </span>
                            
                            <span class="views">
                                <i class="fas fa-eye"></i>
                                <?php echo $article['views']; ?> просмотров
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!empty($article['featured_image'])): ?>
                        <div class="article-image">
                            <img src="<?php echo UPLOADS_URL . $article['featured_image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="img-fluid">
                        </div>
                    <?php endif; ?>
                    
                    <div class="article-content">
                        <?php echo $article['content']; ?>
                    </div>
                    
                    <?php if (!empty($articleTags)): ?>
                        <div class="article-tags">
                            <span class="tags-title"><i class="fas fa-tags"></i> Теги:</span>
                            <?php foreach ($articleTags as $tag): ?>
                                <a href="<?php echo SITE_URL; ?>/search.php?q=<?php echo urlencode($tag['tag']); ?>" class="tag">
                                    <?php echo htmlspecialchars($tag['tag']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
                
                <?php if (!empty($relatedArticles)): ?>
                    <div class="related-articles">
                        <h3>Похожие статьи</h3>
                        
                        <div class="row">
                            <?php foreach ($relatedArticles as $relatedArticle): ?>
                                <div class="col-md-6">
                                    <div class="related-article">
                                        <?php if (!empty($relatedArticle['featured_image'])): ?>
                                            <div class="related-article-image">
                                                <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $relatedArticle['slug']; ?>">
                                                    <img src="<?php echo UPLOADS_URL . $relatedArticle['featured_image']; ?>" alt="<?php echo htmlspecialchars($relatedArticle['title']); ?>" class="img-fluid">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="related-article-content">
                                            <h4>
                                                <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $relatedArticle['slug']; ?>">
                                                    <?php echo htmlspecialchars($relatedArticle['title']); ?>
                                                </a>
                                            </h4>
                                            
                                            <div class="related-article-meta">
                                                <span class="date">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <?php echo formatDate($relatedArticle['created_at']); ?>
                                                </span>
                                                
                                                <span class="views">
                                                    <i class="fas fa-eye"></i>
                                                    <?php echo $relatedArticle['views']; ?> просмотров
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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