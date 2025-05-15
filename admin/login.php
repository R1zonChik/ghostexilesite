<?php
// Используем абсолютные пути
$rootPath = dirname(__DIR__);
require_once $rootPath . '/includes/config.php';
require_once $rootPath . '/includes/functions.php';

// Включаем отображение всех ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Если пользователь уже авторизован, перенаправляем на главную страницу админки
if (isset($_SESSION['user_id'])) {
    redirect(ADMIN_URL);
}

$errors = [];
$username = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Валидация
    if (empty($username)) {
        $errors[] = 'Введите имя пользователя';
    }
    
    if (empty($password)) {
        $errors[] = 'Введите пароль';
    }
    
    if (empty($errors)) {
        // Поиск пользователя в базе данных
        try {
            // Прямой запрос к базе данных для отладки
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Для отладки: проверяем хеш пароля
                $stored_hash = $user['password'];
                
                // Проверка с использованием password_verify
                if (password_verify($password, $stored_hash)) {
                    // Авторизация успешна
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Перенаправление на главную страницу админки
                    redirect(ADMIN_URL);
                } else {
                    // Временное решение: проверка прямого совпадения (если пароль хранится в открытом виде)
                    if ($password === $stored_hash) {
                        // Авторизация успешна (пароль в открытом виде)
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        // Перенаправление на главную страницу админки
                        redirect(ADMIN_URL);
                    } else {
                        $errors[] = 'Неверное имя пользователя или пароль';
                    }
                }
            } else {
                $errors[] = 'Неверное имя пользователя или пароль';
            }
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

// Получение флеш-сообщения, если есть
$flashMessage = null;
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Определение CSS-переменных для стилей
$cssVars = [
    '--primary-color' => '#6a1b9a',
    '--primary-dark' => '#4a148c',
    '--primary-light' => '#9c4dcc',
    '--accent-color' => '#e040fb',
    '--accent-light' => '#ea80fc',
    '--dark-bg' => '#121212',
    '--dark-surface' => '#1e1e1e',
    '--dark-card' => '#252525',
    '--light-text' => '#ffffff',
    '--muted-text' => '#b0b0b0',
    '--border-color' => '#333333',
    '--shadow-sm' => '0 2px 4px rgba(0, 0, 0, 0.3)',
    '--shadow-md' => '0 4px 8px rgba(0, 0, 0, 0.4)',
    '--shadow-lg' => '0 8px 16px rgba(0, 0, 0, 0.5)'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            <?php foreach ($cssVars as $var => $value): ?>
                <?php echo $var; ?>: <?php echo $value; ?>;
            <?php endforeach; ?>
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: var(--dark-bg);
            background-image: url('https://ghost-exile.site/img/3840_2140.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            color: var(--light-text);
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }
        
        .login-container {
            background-color: var(--dark-card);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border: 1px solid var(--border-color);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: var(--light-text);
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .login-header p {
            color: var(--muted-text);
            margin: 0;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-text);
            font-weight: 500;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--light-text);
            transition: all 0.3s ease;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .login-form button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: var(--primary-color);
            color: var(--light-text);
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .login-form button:hover {
            background-color: var(--primary-dark);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-footer a {
            color: var(--accent-light);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-footer a:hover {
            color: var(--accent-color);
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Ghost Exile Guide</h1>
            <p>Вход в админ-панель</p>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type']; ?>">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Войти</button>
            </div>
        </form>
        
        <div class="login-footer">
            <a href="<?php echo SITE_URL; ?>">Вернуться на сайт</a>
        </div>
    </div>
</body>
</html>