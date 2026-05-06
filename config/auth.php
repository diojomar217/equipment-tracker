<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_user() {
    return $_SESSION['user'] ?? null;
}

function auth_is_logged_in() {
    return !empty($_SESSION['user']);
}

function auth_require_login($isApi = false) {
    if (!auth_is_logged_in()) {
        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
            exit;
        }

        header('Location: login.php');
        exit;
    }
}

function auth_require_role($roles, $isApi = false) {
    auth_require_login($isApi);
    $user = auth_user();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $allowed, true)) {
        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden.']);
            exit;
        }

        http_response_code(403);
        echo 'Access denied.';
        exit;
    }
}

function auth_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function auth_redirect_by_role() {
    if (!auth_is_logged_in()) {
        return;
    }

    $role = auth_user()['role'];
    if ($role === 'Admin') {
        header('Location: equipment.php');
    } else {
        header('Location: scan.php');
    }
    exit;
}

// Auth database helpers have been moved to /api/auth_helper.php.
// Keep this file focused on session/auth utilities only.
