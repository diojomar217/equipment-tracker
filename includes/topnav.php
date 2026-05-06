<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?php echo htmlspecialchars($brandUrl ?? 'equipment.php'); ?>">
            <span class="brand-mark">ET</span>
            Equipment Tracker
        </a>

        <div class="navbar-nav align-items-center ms-auto">
            <span class="navbar-text text-muted me-3">
                Signed in as <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
            </span>

            <?php if ($user['role'] === 'Admin'): ?>
                <a class="nav-item nav-link" href="equipment.php">Equipment</a>
                <a class="nav-item nav-link" href="logs.php">Logs</a>
            <?php endif; ?>

            <a class="nav-item nav-link" href="scan.php">Scan</a>
            <a class="nav-item nav-link text-danger" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
