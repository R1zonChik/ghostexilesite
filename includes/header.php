<?php require_once 'includes/init.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : (defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : 'Полное руководство по игре Ghost Exile'); ?>">
    <link rel="icon" href="https://ghost-exile.site/img/ico_logo.ico" type="image/x-icon" sizes="32x32">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* Стили для шапки сайта */
    .header-logo {
        max-height: 60px;
        width: auto;
    }

    .logo {
        display: flex;
        align-items: center;
        margin-right: 30px;
    }

    .logo a {
        display: flex;
        align-items: center;
    }

    /* BETA-лента */
    .beta-ribbon {
        position: fixed;
        top: 25px;
        right: -45px;
        width: 170px;
        height: 35px;
        background: linear-gradient(135deg, #ff3d00, #ff5f52);
        color: #fff;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 700;
        line-height: 35px;
        letter-spacing: 1.5px;
        transform: rotate(45deg);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.5);
        z-index: 1000;
        text-transform: uppercase;
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    .beta-ribbon:hover {
        background: linear-gradient(135deg, #ff5f52, #ff3d00);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.7);
    }

    .site-header .container {
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    .main-nav {
        margin-left: 20px;
    }

    .search-form {
        margin-left: auto;
    }

    /* Модальное окно выбора языка */
    .lang-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
    }

    .lang-modal.active {
        display: block;
    }

    .lang-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
    }

    .lang-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(30, 30, 30, 0.95);
        border-radius: 10px;
        padding: 30px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
        border: 1px solid #444;
    }

    .lang-modal-content h2 {
        color: #fff;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .lang-flags {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 25px;
    }

    .lang-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 10px;
        color: #fff;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 2.5rem;
        border-radius: 8px;
    }

    .lang-btn span {
        font-size: 1rem;
    }

    .lang-btn:hover:not(:disabled) {
        transform: scale(1.05);
        background-color: rgba(255, 255, 255, 0.1);
    }

    .lang-btn:disabled {
        opacity: 0.5;
        cursor: default;
    }

    .lang-btn.active {
        background-color: rgba(255, 95, 82, 0.3);
        box-shadow: 0 0 15px rgba(255, 95, 82, 0.3);
    }

    .lang-confirm-btn {
        background-color: #ff5f52;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 10px 25px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    .lang-confirm-btn:hover:not(:disabled) {
        background-color: #e54942;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
    }

    .lang-confirm-btn:active:not(:disabled) {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .lang-confirm-btn:disabled {
        background-color: #7a3a36;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .lang-status {
        margin-top: 15px;
        color: #aaa;
        font-size: 0.9rem;
        min-height: 20px;
    }

    /* Дополнительный эффект размытия для всего контента при активной модалке */
    .lang-modal.active ~ .site-header,
    .lang-modal.active ~ .content-wrapper,
    .lang-modal.active ~ .mobile-menu {
        filter: blur(5px);
        transition: filter 0.3s ease;
    }
    </style>
</head>
<body>
    <!-- BETA-лента в углу экрана -->
    <div class="beta-ribbon">BETA</div>

    <!-- Модальное окно выбора языка (размещено перед всем контентом) -->
    <div id="lang-modal" class="lang-modal">
        <div class="lang-modal-overlay"></div>
        <div class="lang-modal-content">
            <h2>Выберите язык / Choose language</h2>
            
            <div class="lang-flags">
                <button id="lang-ru" class="lang-btn" title="Русский">
                    🇷🇺
                    <span>Русский</span>
                </button>
                <button id="lang-en" class="lang-btn" title="English">
                    🇬🇧
                    <span>English</span>
                </button>
            </div>
            
            <button id="lang-confirm" class="lang-confirm-btn" disabled>Подтвердить</button>
            <p id="lang-status" class="lang-status"></p>
        </div>
    </div>

    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>">
                    <img src="https://i.imgur.com/mJOKHnL.png" alt="Ghost Exile Guide" class="header-logo">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">Главная</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php">Об игре</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/navigation.php">Навигация</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/media.php">Медиа</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php">Контакты</a></li>
                </ul>
            </nav>
            
            <div class="search-form">
                <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                    <input type="text" name="q" placeholder="Поиск по сайту...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>
    
    <div class="mobile-menu">
        <div class="close-menu">
            <i class="fas fa-times"></i>
        </div>
        <ul>
            <li><a href="<?php echo SITE_URL; ?>">Главная</a></li>
            <li><a href="<?php echo SITE_URL; ?>/about.php">Об игре</a></li>
            <li><a href="<?php echo SITE_URL; ?>/navigation.php">Навигация</a></li>
            <li><a href="<?php echo SITE_URL; ?>/media.php">Медиа</a></li>
            <li><a href="<?php echo SITE_URL; ?>/contact.php">Контакты</a></li>
        </ul>
        
        <div class="mobile-search">
            <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                <input type="text" name="q" placeholder="Поиск по сайту...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="content-wrapper">