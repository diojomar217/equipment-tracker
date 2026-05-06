<?php
require_once __DIR__ . '/config/auth.php';

if (auth_is_logged_in()) {
    auth_redirect_by_role();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/api/auth_helper.php';

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $user = auth_verify_user($connection, $username, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            $connection->close();
            auth_redirect_by_role();
        } else {
            $error = 'Invalid username or password.';
        }
    }
    $connection->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Login - Equipment Tracker';
$pageStyles = '<style>body { background-color: #f8f9fa; } .login-card { max-width: 400px; margin: 80px auto; }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <div class="container login-card">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title mb-4">Equipment Tracker Login</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post" action="login.php">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                <p class="text-muted mt-4 mb-0">Default Admin: admin / admin123</p>
                <p class="text-muted mb-0">Default Staff: staff / staff123</p>
            </div>
        </div>
    </div>
</body>
</html>