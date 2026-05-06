<?php
/** @var array{username:string,role:string} $user */
/** @var string $activePage */
?>
<aside class="col-md-3 col-lg-2 sidebar py-4 px-3">
    <div class="mb-4">
        <a href="index.php" class="d-flex align-items-center mb-3 text-decoration-none">
            <i class="bi bi-bootstrap-fill text-primary me-2" style="font-size: 1.5rem;"></i>
            <span class="h5 mb-0 text-dark">EquipTrack</span>
        </a>
        <p class="text-muted small mb-0">Signed in as<br><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
        <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['role']); ?> access</p>
    </div>
    <nav class="nav flex-column">
        <!-- Main Navigation -->
        <a class="nav-link py-2 <?php echo ($activePage === 'dashboard' ? 'active' : ''); ?>" href="index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        
        <!-- Admin Section -->
        <?php if ($user['role'] === 'Admin'): ?>
            <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
            <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Admin</div>
            <a class="nav-link py-2 <?php echo ($activePage === 'equipment' ? 'active' : ''); ?>" href="equipment.php"><i class="bi bi-list-ul me-2"></i>Equipment</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'logs' ? 'active' : ''); ?>" href="logs.php"><i class="bi bi-journal-text me-2"></i>Logs</a>
            <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
        <?php endif; ?>
        
        <!-- Common Navigation -->
        <a class="nav-link py-2 <?php echo ($activePage === 'notifications' ? 'active' : ''); ?>" href="notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a>
        <a class="nav-link py-2 <?php echo ($activePage === 'scan' ? 'active' : ''); ?>" href="scan.php"><i class="bi bi-qr-code-scan me-2"></i>Scan</a>
        
        <!-- Logout -->
        <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
        <a class="nav-link py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
</aside>
