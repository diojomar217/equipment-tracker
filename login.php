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
$pageStyles = '';
include __DIR__ . '/includes/head.php';
?>

<body class="login-body">

    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row shadow-lg rounded-4 overflow-hidden login-wrapper">

            <!-- LEFT PANEL (Branding) -->
            <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-center p-5 text-dark login-left">
                <h2 class="fw-bold mb-3">EquipTrack</h2>
                <p class="mb-4 opacity-75">
                    Manage, track, and monitor all equipment in real-time using QR-based scanning system.
                </p>

                <ul class="list-unstyled small">
                    <li class="mb-2">✔ QR Code Tracking</li>
                    <li class="mb-2">✔ Real-time Status Updates</li>
                    <li class="mb-2">✔ Activity Logs</li>
                    <li class="mb-2">✔ Simple &amp; Fast System</li>
                </ul>

                <div class="mt-4 opacity-75 small">
                    © <?= date('Y') ?> Equipment Tracker System
                </div>
            </div>

            <!-- RIGHT PANEL (FORM) -->
            <div class="col-12 col-lg-7 p-5 login-card bg-transparent">
                <div class="card border-0">
                    <div class="card-body">

                        <div class="text-center mb-4">
                            <div class="login-icon mb-3">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h4 class="fw-bold mb-1">Welcome Back</h4>
                            <p class="text-muted small">Sign in to continue</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 small">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="login.php">

                            <!-- Username -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Username</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="username" class="form-control"
                                        placeholder="Enter username"
                                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                        required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock"></i>
                                    </span>

                                    <input type="password" id="password" name="password"
                                        class="form-control"
                                        placeholder="Enter password"
                                        required>

                                    <button class="btn btn-outline-secondary border-0" type="button" id="togglePassword" aria-label="Show password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Button -->
                            <button type="submit" class="btn btn-primary w-100 btn-lg mt-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>

                        </form>

                        <!-- FOOTER INFO -->
                        <div class="mt-4 text-center small text-muted">
                            Default: admin / admin123 • staff / staff123
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <script>
        (function() {
            var toggleBtn = document.getElementById('togglePassword');
            var passwordInput = document.getElementById('password');
            if (!toggleBtn || !passwordInput) return;

            var icon = toggleBtn.querySelector('i');

            toggleBtn.addEventListener('click', function() {
                var isCurrentlyPassword = passwordInput.type === 'password';
                passwordInput.type = isCurrentlyPassword ? 'text' : 'password';

                if (icon) {
                    if (isCurrentlyPassword) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
            });
        })();
    </script>
</body>

</html>