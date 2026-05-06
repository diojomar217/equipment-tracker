<aside class="col-md-3 col-lg-2 sidebar py-4 px-3">
    <div class="mb-4">
        <a href="index.php" class="d-flex align-items-center mb-3 text-decoration-none">
            <i class="bi bi-bootstrap-fill text-white mr-2" style="font-size: 1.5rem;"></i>
            <span class="h5 mb-0 text-white">EquipTrack</span>
        </a>
        <p class="text-muted small mb-0 text-white-50">Signed in as<br><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
        <p class="text-muted small mb-0 text-white-50"><?php echo htmlspecialchars($user['role']); ?> access</p>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link py-2 <?php echo ($activePage === 'dashboard' ? 'active' : ''); ?>" href="index.php"><i class="bi bi-speedometer2 mr-2"></i>Dashboard</a>
        <?php if ($user['role'] === 'Admin'): ?>
            <a class="nav-link py-2 <?php echo ($activePage === 'equipment' ? 'active' : ''); ?>" href="equipment.php"><i class="bi bi-list-ul mr-2"></i>Equipment</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'logs' ? 'active' : ''); ?>" href="logs.php"><i class="bi bi-journal-text mr-2"></i>Logs</a>
        <?php endif; ?>
        <a class="nav-link py-2 <?php echo ($activePage === 'notifications' ? 'active' : ''); ?>" href="notifications.php"><i class="bi bi-bell mr-2"></i>Notifications</a>
        <a class="nav-link py-2 <?php echo ($activePage === 'scan' ? 'active' : ''); ?>" href="scan.php"><i class="bi bi-qr-code-scan mr-2"></i>Scan</a>
        <a class="nav-link py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right mr-2"></i>Logout</a>
    </nav>
</aside>
