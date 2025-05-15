<?php
require_once 'bootstrap.php';

// Проверка прав администратора
if (!isAdmin()) {
    setFlashMessage('error', 'У вас нет прав для управления пользователями');
    redirect(ADMIN_URL);
}

$errors = [];
$success = false;
$editMode = false;
$user = [
    'id' => 0,
    'username' => '',
    'email' => '',
    'role' => 'editor'
];

// Обработка удаления
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Нельзя удалить самого себя
    if ($id == $_SESSION['user_id']) {
        $errors[] = 'Вы не можете удалить свою учетную запись';
    } else {
        $database->delete('users', 'id = ?', ['id' => $id]);
        $_SESSION['user_deleted'] = true;
        redirect(ADMIN_URL . '/manage-users.php?deleted=1');
    }
}

// Проверка успешного удаления
if (isset($_GET['deleted']) && $_GET['deleted'] == 1 && isset($_SESSION['user_deleted'])) {
    $success = 'Пользователь успешно удален';
    unset($_SESSION['user_deleted']);
}

// Обработка редактирования
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $user = $database->selectOne("SELECT id, username, email, role FROM users WHERE id = ?", [$id]);
    
    if ($user) {
        $editMode = true;
    } else {
        redirect(ADMIN_URL . '/manage-users.php');
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'editor';
    
    // Валидация
    if (empty($username)) {
        $errors[] = 'Имя пользователя обязательно';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный email';
    }
    
    // Проверка уникальности имени пользователя и email
    $existingUsername = $database->selectOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
    if ($existingUsername) {
        $errors[] = 'Имя пользователя уже занято';
    }
    
    $existingEmail = $database->selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
    if ($existingEmail) {
        $errors[] = 'Email уже используется';
    }
    
    // Сохранение данных
    if (empty($errors)) {
        $data = [
            'username' => $username,
            'email' => $email,
            'role' => $role
        ];
        
        // Если указан новый пароль, хешируем его
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        if ($id > 0) {
            // Обновление
            $database->update('users', $data, 'id = ?', ['id' => $id]);
            $_SESSION['user_updated'] = true;
            redirect(ADMIN_URL . '/manage-users.php?updated=1');
        } else {
            // Добавление (пароль обязателен)
            if (empty($password)) {
                $errors[] = 'Пароль обязателен для нового пользователя';
            } else {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $database->insert('users', $data);
                $_SESSION['user_added'] = true;
                redirect(ADMIN_URL . '/manage-users.php?added=1');
            }
        }
    }
}

// Проверка успешного добавления/обновления
if (isset($_GET['added']) && $_GET['added'] == 1 && isset($_SESSION['user_added'])) {
    $success = 'Пользователь успешно добавлен';
    unset($_SESSION['user_added']);
} elseif (isset($_GET['updated']) && $_GET['updated'] == 1 && isset($_SESSION['user_updated'])) {
    $success = 'Пользователь успешно обновлен';
    unset($_SESSION['user_updated']);
}

// Получение списка пользователей
$users = $database->select("SELECT id, username, email, role, created_at FROM users ORDER BY username", []);

$pageTitle = 'Управление пользователями';
include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h2>Управление пользователями</h2>
    </div>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            Пользователь успешно удален!
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
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
    
    <div class="admin-grid">
        <div class="admin-form-container">
            <h3><?php echo $editMode ? 'Редактирование пользователя' : 'Добавление пользователя'; ?></h3>
            <form action="" method="POST" class="admin-form">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" <?php echo (!$editMode) ? 'required' : ''; ?>>
                    <?php if ($editMode): ?>
                        <small>Оставьте пустым, чтобы сохранить текущий пароль</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="role">Роль</label>
                    <select id="role" name="role">
                        <option value="editor" <?php echo ($user['role'] == 'editor') ? 'selected' : ''; ?>>Редактор</option>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Администратор</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editMode ? 'Обновить пользователя' : 'Добавить пользователя'; ?>
                    </button>
                    
                    <?php if ($editMode): ?>
                        <a href="<?php echo ADMIN_URL; ?>/manage-users.php" class="btn btn-secondary">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="admin-list-container">
            <h3>Список пользователей</h3>
            
            <?php if (count($users) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Имя пользователя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($u['role'] == 'admin') ? 'badge-primary' : 'badge-secondary'; ?>">
                                        <?php echo ($u['role'] == 'admin') ? 'Администратор' : 'Редактор'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/manage-users.php?edit=<?php echo $u['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="<?php echo ADMIN_URL; ?>/manage-users.php?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Нет пользователей для отображения.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>