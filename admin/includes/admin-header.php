<?php
// Проверка подключения bootstrap.php
if (!function_exists('isLoggedIn')) {
    die('Ошибка: файл bootstrap.php не подключен');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME . ' Админ-панель' : SITE_NAME . ' Админ-панель'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/gzr6vzdc9dwdn70lt7h7ljuqdvuajb8l0t7mh0mu91ivd753/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <?php if (function_exists('initTinyMCE')) echo initTinyMCE(); ?>

</head>
<body class="admin-body">
    <div class="admin-wrapper">