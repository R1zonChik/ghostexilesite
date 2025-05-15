<?php
require_once 'includes/init.php';

// Получение slug категории из URL
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

if (empty($slug)) {
    redirect(SITE_URL);
}

// Получение категории по slug
$category = getCategoryBySlug($slug);

if (!$category) {
    // Категория не найдена
    header("HTTP/1.0 404 Not Found");
    include 'includes/header.php';
    ?>
    <div class="container">
        <div class="error-page">
            <h1>404</h1>
            <h2>Страница не найдена</h2>
            <p>Запрашиваемая категория не существует или была удалена.</p>
            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">Вернуться на главную</a>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}

// Получение всех статей категории, отсортированных по алфавиту
$sql = "SELECT c.*, cat.name as category_name 
        FROM content c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        WHERE c.category_id = ? AND c.status = 'published' 
        ORDER BY c.title";

$articles = $database->select($sql, [$category['id']]);

// Группировка статей по первой букве для алфавитного указателя
$articlesByLetter = [];
$letters = [];

foreach ($articles as $article) {
    // Получаем первую букву заголовка (с учетом кириллицы)
    $firstLetter = mb_strtoupper(mb_substr($article['title'], 0, 1, 'UTF-8'), 'UTF-8');
    
    // Добавляем букву в массив букв, если её там еще нет
    if (!in_array($firstLetter, $letters)) {
        $letters[] = $firstLetter;
    }
    
    // Добавляем статью в соответствующую группу по букве
    if (!isset($articlesByLetter[$firstLetter])) {
        $articlesByLetter[$firstLetter] = [];
    }
    $articlesByLetter[$firstLetter][] = $article;
}

// Сортируем буквы
sort($letters);

$pageTitle = $category['name'];
$pageDescription = 'Статьи в категории ' . $category['name'];
include 'includes/header.php';
?>

<div class="container">
    <!-- Заголовок категории -->
    <div class="category-header">
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>
        
        <!-- Поле поиска -->
        <div class="search-container">
            <input type="text" id="ghost-search" placeholder="Быстрый поиск призрака..." class="ghost-search">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
    
    <?php if (count($articles) > 0): ?>
        <!-- Алфавитная навигация сверху в черной полосе -->
        <div class="top-alphabet-container">
            <div class="top-alphabet">
                <?php foreach ($letters as $letter): ?>
                    <a href="#letter-<?php echo $letter; ?>" class="alphabet-link"><?php echo $letter; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="category-wrapper">
            <!-- Основной контент со статьями -->
            <main class="article-list">
                <?php foreach ($letters as $letter): ?>
                    <div class="letter-section" id="letter-<?php echo $letter; ?>">
                        <h2 class="letter-heading"><?php echo $letter; ?></h2>
                        <ul class="compact-list">
                            <?php foreach ($articlesByLetter[$letter] as $article): ?>
                                <li class="article-item">
                                    <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $article['slug']; ?>">
                                        <?php if (!empty($article['featured_image'])): ?>
                                            <img src="<?php echo UPLOADS_URL . $article['featured_image']; ?>" alt="" class="article-thumb">
                                        <?php else: ?>
                                            <div class="article-thumb-placeholder"></div>
                                        <?php endif; ?>
                                        <span class="article-title"><?php echo htmlspecialchars($article['title']); ?></span>
                                        <span class="article-views"><?php echo $article['views']; ?> <i class="fas fa-eye"></i></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </main>
            
            <!-- Боковая панель с оглавлением текущей категории -->
            <aside class="guide-sidebar">
                <div class="sidebar-title">Оглавление категории</div>
                
                <!-- Поиск в боковой панели -->
                <div class="sidebar-search-container">
                    <input type="text" id="sidebar-search" placeholder="Поиск в оглавлении..." class="sidebar-search">
                </div>
                
                <ul class="guide-menu">
                    <?php foreach ($articles as $article): ?>
                        <li class="guide-item">
                            <a href="<?php echo SITE_URL; ?>/single.php?slug=<?php echo $article['slug']; ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="back-to-top">
                    <a href="#" id="back-to-top-btn">
                        <i class="fas fa-arrow-up"></i> Наверх
                    </a>
                </div>
            </aside>
        </div>
    <?php else: ?>
        <div class="no-results">
            <div class="alert alert-info">
                <p>В данной категории пока нет статей.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Основн��е стили страницы категории */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Заголовок и поиск */
.category-header {
    text-align: center;
    margin-bottom: 20px;
}

.category-header h1 {
    font-size: 2rem;
    color: #ff5f52;
    margin-bottom: 15px;
    text-shadow: 0 0 10px rgba(255, 95, 82, 0.3);
}

/* Поле поиска */
.search-container {
    position: relative;
    max-width: 300px;
    margin: 0 auto 15px;
}

.ghost-search {
    width: 100%;
    padding: 8px 35px 8px 12px;
    border-radius: 20px;
    border: 1px solid #444;
    background-color: rgba(0, 0, 0, 0.3);
    color: #fff;
    font-size: 14px;
}

.ghost-search:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 95, 82, 0.3);
    border-color: #ff5f52;
}

.search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #ff5f52;
}

/* Алфавитная навигация в черной полосе */
.top-alphabet-container {
    background-color: rgba(0, 0, 0, 0.7);
    border-radius: 8px;
    padding: 12px 0;
    margin: 20px auto;
    max-width: 900px;
    overflow-x: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.top-alphabet {
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    gap: 12px;
    padding: 0 20px;
    min-width: max-content;
}

.alphabet-link {
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 36px;
    height: 36px;
    background-color: rgba(255, 95, 82, 0.2);
    border-radius: 50%;
    color: #fff;
    font-weight: bold;
    text-decoration: none;
    transition: all 0.2s ease;
}

.alphabet-link:hover {
    background-color: rgba(255, 95, 82, 0.8);
    transform: scale(1.1);
}

/* Двухколоночный макет */
.category-wrapper {
    display: flex;
    gap: 20px;
}

/* Основной список статей */
.article-list {
    flex: 1;
}

.letter-section {
    margin-bottom: 20px;
}

.letter-heading {
    font-size: 1.5rem;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid rgba(255, 95, 82, 0.5);
    color: #ff5f52;
}

.compact-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.article-item {
    margin-bottom: 5px;
}

.article-item a {
    display: flex;
    align-items: center;
    padding: 8px 10px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.2s;
}

.article-item a:hover {
    background-color: rgba(255, 95, 82, 0.1);
}

.article-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 10px;
}

.article-thumb-placeholder {
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    margin-right: 10px;
}

.article-title {
    flex: 1;
    font-size: 0.95rem;
}

.article-views {
    font-size: 0.8rem;
    color: #aaa;
    margin-left: 10px;
    white-space: nowrap;
}

/* Боковая панель с оглавлением */
.guide-sidebar {
    width: 220px;
    position: sticky;
    top: 20px;
    align-self: flex-start;
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 15px;
    max-height: 80vh;
    overflow-y: auto;
}

.sidebar-title {
    font-size: 1rem;
    color: #ff5f52;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #444;
    text-align: center;
    font-weight: bold;
}

/* Поиск в боковой панели */
.sidebar-search-container {
    margin-bottom: 10px;
}

.sidebar-search {
    width: 100%;
    padding: 6px 10px;
    border-radius: 4px;
    border: 1px solid #444;
    background-color: rgba(0, 0, 0, 0.3);
    color: #fff;
    font-size: 12px;
}

.sidebar-search:focus {
    outline: none;
    border-color: #ff5f52;
}

.guide-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.guide-item {
    margin-bottom: 8px;
}

.guide-item a {
    display: block;
    color: #fff;
    text-decoration: none;
    font-size: 0.9rem;
    padding: 5px 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.guide-item a:hover {
    background-color: rgba(255, 95, 82, 0.2);
    color: #ff5f52;
}

.back-to-top {
    margin-top: 20px;
    text-align: center;
}

.back-to-top a {
    display: inline-block;
    padding: 8px 12px;
    background-color: #ff5f52;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.8rem;
    transition: background-color 0.2s;
}

.back-to-top a:hover {
    background-color: #e54942;
}

/* Адаптивность */
@media (max-width: 768px) {
    .category-wrapper {
        flex-direction: column;
    }
    
    .guide-sidebar {
        width: 100%;
        position: static;
        margin-top: 20px;
        max-height: none;
    }
    
    .alphabet-link {
        min-width: 30px;
        height: 30px;
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Поиск по заголовкам в основном списке
    const searchInput = document.getElementById('ghost-search');
    searchInput.addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const articleItems = document.querySelectorAll('.article-item');
        
        articleItems.forEach(function(item) {
            const title = item.querySelector('.article-title').textContent.toLowerCase();
            if (title.includes(searchText)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Скрываем пустые секции букв
        document.querySelectorAll('.letter-section').forEach(function(section) {
            const visibleArticles = section.querySelectorAll('.article-item[style=""]').length;
            if (visibleArticles === 0) {
                section.style.display = 'none';
            } else {
                section.style.display = '';
            }
        });
    });
    
    // Поиск в боковой панели
    const sidebarSearch = document.getElementById('sidebar-search');
    sidebarSearch.addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const guideItems = document.querySelectorAll('.guide-item');
        
        guideItems.forEach(function(item) {
            const title = item.querySelector('a').textContent.toLowerCase();
            if (title.includes(searchText)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Плавная прокрутка для алфавитных ссылок
    document.querySelectorAll('.alphabet-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const headerOffset = 80; // Высота фиксированного хедера, если есть
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Кнопка "Наверх"
    document.getElementById('back-to-top-btn').addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>