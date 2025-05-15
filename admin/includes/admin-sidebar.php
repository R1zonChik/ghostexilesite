<div class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Ghost Exile</h2>
        <p>Админ-панель</p>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="<?php echo ADMIN_URL; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Панель управления
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/add-content.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add-content.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Добавить статью
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/manage-content.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-content.php' || basename($_SERVER['PHP_SELF']) == 'edit-content.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Управление статьями
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/manage-categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-folder"></i> Управление категориями
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/upload-image.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'upload-image.php' ? 'active' : ''; ?>">
                    <i class="fas fa-image"></i> Загрузка изображений
                </a>
            </li>
            <?php if (isAdmin()): ?>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/manage-users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Управление пользователями
                    </a>
                </li>
            <?php endif; ?>
            <li class="divider"></li>
            <li>
                <a href="<?php echo SITE_URL; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Перейти на сайт
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="admin-main">
    <div class="admin-header">
        <div class="admin-header-left">
            <h1><?php echo isset($pageTitle) ? $pageTitle : 'Админ-панель'; ?></h1>
        </div>
        <div class="admin-header-right">
            <div class="user-dropdown">
                <div class="user-dropdown-toggle">
                    <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($_SESSION['user_email'] ?? ''))); ?>?s=40&d=mp" alt="User">
                    <span><?php echo $_SESSION['username'] ?? 'Администратор'; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="user-dropdown-menu">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>" target="_blank"><i class="fas fa-external-link-alt"></i> Перейти на сайт</a></li>
                        <li><a href="<?php echo ADMIN_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>