<?php
require_once 'includes/init.php';

// Получение поискового запроса
$query = isset($_GET['q']) ? clean($_GET['q']) : '';

// Поиск статей
$articles = [];
if (!empty($query)) {
    $articles = searchContent($query);
}

$pageTitle = 'Поиск: ' . $query;
include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Поиск</h1>
    
    <div class="search-form mb-4">
        <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Поиск по сайту..." value="<?php echo htmlspecialchars($query); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Найти
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <?php if (!empty($query)): ?>
        <h2>Результаты поиска по запросу: "<?php echo htmlspecialchars($query); ?>"</h2>
        
        <?php if (count($articles) > 0): ?>
            <div class="search-results">
                <p>Найдено результатов: <?php echo count($articles); ?></p>
                
                <div class="articles-list">
                    <?php foreach ($articles as $article): ?>
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
                                                <?php if (isset($article['category_name'])): ?>
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
                                            
                                            <div class="article-excerpt">
                                                <?php echo truncateText(strip_tags($article['content']), 200); ?>
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
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>По вашему запросу ничего не найдено. Попробуйте изменить поисковый запрос.</p>
            </div>
            
            <div class="humor-block">
                <h4>Может быть, призраки спрятали результаты?</h4>
                <p>Попробуйте поискать что-то другое или вернитесь на <a href="<?php echo SITE_URL; ?>">главную страницу</a>.</p>
                <p>Призраки иногда любят играть в прятки с информацией...</p>
                
                <div class="ghost-diary">
                    <h4>Дневник охотника за привидениями. День 42:</h4>
                    <p>"Сегодня я снова искал информацию о <?php echo htmlspecialchars($query); ?>, но мой EMF-датчик показывал только цифру 5, а Spirit Box упорно молчал. Я попытался использовать доску Уиджа, но она написала только 'ПЕРЕФОРМУЛИРУЙ ЗАПРОС, СМЕРТНЫЙ'."</p>
                    
                    <p>Я начинаю подозревать, что в моем компьютере поселился Полтергейст. Он перемещает мои файлы и удаляет результаты поисков. Вчера я оставил включенной камеру ночного видения и заснял, как мой курсор двигался сам по себе и искал "как изгнать охотника за привидениями из дома".</p>
                    
                    <p>Возможно, стоит попробовать другие ключевые слова или проверить правильность написания. Или просто зажечь благовония и надеяться, что призраки будут более сговорчивыми в следующий раз.</p>
                    
                    <p>P.S. Если вы слышите странные звуки из своих колонок после прочтения этого сообщения — бегите. Просто бегите.</p>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <p>Введите поисковый запрос для поиска по сайту.</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Стили для страницы поиска */
.search-form {
    margin-bottom: 30px;
}

.search-form .form-control {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--border-color);
    color: var(--light-text);
    padding: 10px 15px;
    border-radius: 4px 0 0 4px;
}

.search-form .btn-primary {
    background-color: var(--primary-color);
    border: none;
    padding: 10px 20px;
}

.input-group {
    display: flex;
}

.input-group-append {
    display: flex;
}

.humor-block {
    background-color: rgba(106, 27, 154, 0.1);
    border-left: 4px solid var(--primary-color);
    padding: 20px;
    margin: 30px 0;
    border-radius: 4px;
}

.humor-block h4 {
    color: var(--accent-light);
    margin-top: 0;
    margin-bottom: 15px;
}

.ghost-diary {
    margin-top: 20px;
    padding: 20px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    border: 1px dashed var(--primary-light);
}

.ghost-diary h4 {
    color: var(--accent-light);
    margin-bottom: 15px;
}

.ghost-diary p {
    font-style: italic;
    margin-bottom: 15px;
    color: var(--muted-text);
}

.ghost-diary p:last-child {
    color: #ff6b6b;
    font-weight: bold;
}

.article-item {
    background-color: var(--dark-card);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    padding: 20px;
    margin-bottom: 20px;
    transition: transform var(--transition-normal);
}

.article-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.article-body h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    color: var(--muted-text);
    font-size: 0.9rem;
}

.article-excerpt {
    margin-bottom: 15px;
    color: var(--muted-text);
}

.read-more {
    display: inline-flex;
    align-items: center;
    color: var(--accent-light);
    font-weight: 500;
}

.read-more i {
    margin-left: 5px;
    transition: transform 0.3s ease;
}

.read-more:hover i {
    transform: translateX(5px);
}
</style>

<?php include 'includes/footer.php'; ?>