<?php
// Получение категорий если они не были переданы из index.php
if (!isset($categories) || empty($categories)) {
    $categories = $database->select("SELECT * FROM categories ORDER BY name", []);
}

// Оптимизированный подсчет статей для категорий, если не был передан из index.php
if (!isset($categoryCounts) || empty($categoryCounts)) {
    $categoryCountsArr = $database->select(
        "SELECT category_id, COUNT(*) as count FROM content WHERE status = 'published' GROUP BY category_id",
        []
    );
    $categoryCounts = [];
    foreach ($categoryCountsArr as $row) {
        $categoryCounts[$row['category_id']] = $row['count'];
    }
}

// Получение популярных статей
$sidebarPopularPosts = $database->select("SELECT * FROM content WHERE status = 'published' ORDER BY views DESC LIMIT 5", []);

// Получение тегов (если есть таблица с тегами)
$sidebarTags = [];
try {
    $sidebarTags = $database->select("
        SELECT DISTINCT tag, COUNT(*) as count 
        FROM content_tags 
        JOIN content ON content_tags.content_id = content.id 
        WHERE content.status = 'published' 
        GROUP BY tag 
        ORDER BY count DESC 
        LIMIT 20
    ", []);
} catch (Exception $e) {
    // Таблица тегов может отсутствовать, игнорируем ошибку
}
?>

<div class="sidebar">
    <div class="sidebar-widget">
        <h4>Навигация</h4>
        <ul class="categories-list">
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                        <span style="color:#bb86fc;">
                            (<?php echo isset($categoryCounts[$category['id']]) ? $categoryCounts[$category['id']] : 0; ?>)
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Популярные статьи -->
    <div class="sidebar-widget popular-posts-widget">
        <h4>Популярные статьи</h4>
        <?php if (count($sidebarPopularPosts) > 0): ?>
            <ul class="popular-posts">
                <?php foreach ($sidebarPopularPosts as $post): ?>
                    <li>
                        <div class="post-item">
                            <?php if (!empty($post['featured_image'])): ?>
                                <div class="post-image">
                                    <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $post['slug']; ?>">
                                        <img src="<?php echo UPLOADS_URL . $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid">
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="post-info">
                                <h5>
                                    <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $post['slug']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h5>
                                <div class="post-meta">
                                    <span class="post-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo formatDate($post['created_at']); ?>
                                    </span>
                                    <span class="post-views">
                                        <i class="fas fa-eye"></i>
                                        <?php echo $post['views']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Нет популярных статей.</p>
        <?php endif; ?>
    </div>
    
    <!-- Теги (если есть) -->
    <?php if (count($sidebarTags) > 0): ?>
    <div class="sidebar-widget tags-widget">
        <h4>Теги</h4>
        <div class="tags-cloud">
            <?php foreach ($sidebarTags as $tag): ?>
                <a href="<?php echo SITE_URL; ?>/search.php?q=<?php echo urlencode($tag['tag']); ?>" class="tag">
                    <?php echo htmlspecialchars($tag['tag']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Социальные сети -->
    <div class="sidebar-widget social-widget">
        <h4>Мы в соцсетях</h4>
        <div class="social-links">
            <a href="https://vk.com/ghostexile_ru" target="_blank" class="social-link vk">
                <i class="fab fa-vk"></i>
            </a>
            <a href="https://t.me/ghostexileofficial" target="_blank" class="social-link telegram">
                <i class="fab fa-telegram"></i>
            </a>
            <a href="https://discord.gg/K3TpZPccqH" target="_blank" class="social-link discord">
                <i class="fab fa-discord"></i>
            </a>
            <a href="https://www.youtube.com/channel/UCU0_xL7WGL7QGaVzdDMfgaw" target="_blank" class="social-link youtube">
                <i class="fab fa-youtube"></i>
            </a>
        </div>
    </div>
    
    <!-- Последние комментарии (если есть система комментариев) -->
    <?php
    $hasComments = false;
    try {
        $latestComments = $database->select("SELECT * FROM comments WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5", []);
        $hasComments = count($latestComments) > 0;
    } catch (Exception $e) {
        // Таблица комментариев может отсутствовать, игнорируем ошибку
    }
    
    if ($hasComments):
    ?>
    <div class="sidebar-widget comments-widget">
        <h4>Последние комментарии</h4>
        <ul class="recent-comments">
            <?php foreach ($latestComments as $comment): ?>
                <?php
                $commentArticle = $database->selectOne("SELECT title, slug FROM content WHERE id = ?", [$comment['content_id']]);
                if (!$commentArticle) continue;
                ?>
                <li>
                    <div class="comment-author">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($comment['author_name']); ?>
                    </div>
                    <div class="comment-text">
                        <?php echo truncateText(strip_tags($comment['content']), 80); ?>
                    </div>
                    <div class="comment-article">
                        <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $commentArticle['slug']; ?>">
                            <?php echo htmlspecialchars($commentArticle['title']); ?>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <!-- Информационный блок -->
    <div class="sidebar-widget info-widget">
        <h4>О Ghost Exile</h4>
        <p>Ghost Exile Guide — ваш надежный помощник в мире охоты за привидениями. Здесь вы найдете полезные советы, гайды и информацию о всех типах призраков и оборудовании.</p>
        <div class="info-buttons">
            <a href="<?php echo SITE_URL; ?>/about.php" class="btn btn-sm btn-primary">О проекте</a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-sm btn-secondary">Связаться с нами</a>
        </div>
    </div>
    
    <!-- Статистика сайта -->
    <div class="sidebar-widget stats-widget">
        <h4>Статистика сайта</h4>
        <ul class="stats-list">
            <?php
            $totalArticles = $database->selectOne("SELECT COUNT(*) as count FROM content WHERE status = 'published'", [])['count'] ?? 0;
            $totalCategories = count($categories);
            $totalViews = $database->selectOne("SELECT SUM(views) as total FROM content", [])['total'] ?? 0;
            ?>
            <li>
                <i class="fas fa-file-alt"></i>
                <span>Статей: <?php echo $totalArticles; ?></span>
            </li>
            <li>
                <i class="fas fa-folder"></i>
                <span>Категорий: <?php echo $totalCategories; ?></span>
            </li>
            <li>
                <i class="fas fa-eye"></i>
                <span>Просмотров: <?php echo $totalViews; ?></span>
            </li>
        </ul>
    </div>
</div>

<style>
/* Дополнительные стили для сайдбара */
.post-meta {
    display: flex;
    flex-direction: column;
    font-size: 0.75rem;
    color: var(--muted-text);
    margin-top: 5px;
}

.post-meta span {
    display: flex;
    align-items: center;
    margin-bottom: 3px;
}

.post-meta i {
    margin-right: 5px;
    font-size: 0.7rem;
}

.categories-list li a {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.categories-list li a .count {
    font-size: 0.8rem;
    color: var(--muted-text);
}

.info-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.stats-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.stats-list li {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stats-list li:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.stats-list li i {
    margin-right: 10px;
    color: var(--primary-light);
    width: 20px;
    text-align: center;
}

.recent-comments {
    list-style: none;
    margin: 0;
    padding: 0;
}

.recent-comments li {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.recent-comments li:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.comment-author {
    font-weight: 500;
    margin-bottom: 5px;
}

.comment-author i {
    margin-right: 5px;
    color: var(--primary-light);
}

.comment-text {
    font-size: 0.9rem;
    color: var(--muted-text);
    margin-bottom: 5px;
    font-style: italic;
}

.comment-article {
    font-size: 0.8rem;
}

.comment-article a {
    color: var(--accent-light);
}

.comment-article a:hover {
    color: var(--accent-color);
}
</style>