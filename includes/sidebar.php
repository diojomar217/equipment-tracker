<?php
/** @var array{username:string,role:string} $user */
/** @var string $activePage */
?>
<button class="btn btn-light d-md-none position-fixed top-0 start-0 m-3 rounded-2" style="z-index: 1040; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Open menu" title="Menu">
    <i class="bi bi-list fs-5"></i>
</button>

<aside class="d-none d-md-block col-md-3 col-lg-2 sidebar py-4 px-3">
    <div class="mb-4 p-3 bg-light rounded">
        <a href="index.php" class="d-flex align-items-center mb-3 text-decoration-none">
            <i class="bi bi-bootstrap-fill text-primary me-2" style="font-size: 1.5rem;"></i>
            <span class="h5 mb-0 text-dark fw-bold">EquipTrack</span>
        </a>
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.2rem;"></i>
            <div>
                <div class="text-muted small mb-0">Signed in as</div>
                <div class="fw-semibold"><?php echo htmlspecialchars($user['name'] ?: $user['username']); ?></div>
            </div>
        </div>
        <div class="badge bg-primary-subtle text-primary small"><?php echo htmlspecialchars($user['role']); ?> Access</div>
    </div>
    <nav class="nav flex-column">
        <!-- Primary Actions -->
        <a class="nav-link py-2 <?php echo ($activePage === 'dashboard' ? 'active' : ''); ?>" href="index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a class="nav-link py-2 <?php echo ($activePage === 'scan' ? 'active' : ''); ?>" href="scan.php"><i class="bi bi-qr-code-scan me-2"></i>Scan QR</a>
        <a class="nav-link py-2 <?php echo ($activePage === 'notifications' ? 'active' : ''); ?>" href="notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a>
        
        <!-- My Equipment (Staff/Admin) -->
        <?php if ($user['role'] === 'Staff' || $user['role'] === 'Admin'): ?>
            <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
            <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">My Equipment</div>
            <a class="nav-link py-2 <?php echo ($activePage === 'my_equipment' ? 'active' : ''); ?>" href="my_equipment.php"><i class="bi bi-person-check me-2"></i>My Equipment</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'staff_equipment' ? 'active' : ''); ?>" href="staff_equipment.php"><i class="bi bi-box me-2"></i>All Equipment</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'staff_logs' ? 'active' : ''); ?>" href="staff_logs.php"><i class="bi bi-journal me-2"></i>Activity Log</a>
        <?php endif; ?>
        
        <!-- Equipment Management (Admin) -->
        <?php if ($user['role'] === 'Admin'): ?>
            <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
            <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Manage Equipment</div>
            <a class="nav-link py-2 <?php echo ($activePage === 'equipment' ? 'active' : ''); ?>" href="equipment.php"><i class="bi bi-gear me-2"></i>Equipment</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'categories' ? 'active' : ''); ?>" href="categories.php"><i class="bi bi-tags me-2"></i>Categories</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'locations' ? 'active' : ''); ?>" href="locations.php"><i class="bi bi-geo-alt me-2"></i>Locations</a>
        <?php endif; ?>
        
        <!-- Administration (Admin) -->
        <?php if ($user['role'] === 'Admin'): ?>
            <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
            <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Admin</div>
            <a class="nav-link py-2 <?php echo ($activePage === 'users' ? 'active' : ''); ?>" href="users.php"><i class="bi bi-people me-2"></i>Users</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'logs' ? 'active' : ''); ?>" href="logs.php"><i class="bi bi-journal-text me-2"></i>Logs</a>
        <?php endif; ?>
        
        <!-- Logout -->
        <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
        <a class="nav-link py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
</aside>

<div class="offcanvas offcanvas-start d-md-none" id="sidebar" tabindex="-1" aria-labelledby="sidebarLabel" data-bs-scroll="true" data-bs-backdrop="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarLabel">
            <i class="bi bi-bootstrap-fill text-primary me-2" style="font-size: 1.5rem;"></i>
            EquipTrack
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body py-4 px-3">
        <div class="mb-4 p-3 bg-light rounded">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.2rem;"></i>
                <div>
                    <div class="text-muted small mb-0">Signed in as</div>
                    <div class="fw-semibold"><?php echo htmlspecialchars($user['name'] ?: $user['username']); ?></div>
                </div>
            </div>
            <div class="badge bg-primary-subtle text-primary small"><?php echo htmlspecialchars($user['role']); ?> Access</div>
        </div>
        <nav class="nav flex-column">
            <!-- Primary Actions -->
            <a class="nav-link py-2 <?php echo ($activePage === 'dashboard' ? 'active' : ''); ?>" href="index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'scan' ? 'active' : ''); ?>" href="scan.php"><i class="bi bi-qr-code-scan me-2"></i>Scan QR</a>
            <a class="nav-link py-2 <?php echo ($activePage === 'notifications' ? 'active' : ''); ?>" href="notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a>
            
            <!-- My Equipment (Staff/Admin) -->
            <?php if ($user['role'] === 'Staff' || $user['role'] === 'Admin'): ?>
                <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
                <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">My Equipment</div>
                <a class="nav-link py-2 <?php echo ($activePage === 'my_equipment' ? 'active' : ''); ?>" href="my_equipment.php"><i class="bi bi-person-check me-2"></i>My Equipment</a>
                <a class="nav-link py-2 <?php echo ($activePage === 'staff_equipment' ? 'active' : ''); ?>" href="staff_equipment.php"><i class="bi bi-box me-2"></i>All Equipment</a>
                <a class="nav-link py-2 <?php echo ($activePage === 'staff_logs' ? 'active' : ''); ?>" href="staff_logs.php"><i class="bi bi-journal me-2"></i>Activity Log</a>
            <?php endif; ?>
            
            <!-- Equipment Management (Admin) -->
            <?php if ($user['role'] === 'Admin'): ?>
                <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
                <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Manage Equipment</div>
                <a class="nav-link py-2 <?php echo ($activePage === 'equipment' ? 'active' : ''); ?>" href="equipment.php"><i class="bi bi-gear me-2"></i>Equipment</a>
                <a class="nav-link py-2 <?php echo ($activePage === 'categories' ? 'active' : ''); ?>" href="categories.php"><i class="bi bi-tags me-2"></i>Categories</a>
                <a class="nav-link py-2 <?php echo ($activePage === 'locations' ? 'active' : ''); ?>" href="locations.php"><i class="bi bi-geo-alt me-2"></i>Locations</a>
            <?php endif; ?>
            
            <!-- Administration (Admin) -->
            <?php if ($user['role'] === 'Admin'): ?>
                <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
                <div class="text-uppercase text-muted small px-2 mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Admin</div>
                <a class="nav-link py-2 <?php echo ($activePage === 'users' ? 'active' : ''); ?>" href="users.php"><i class="bi bi-people me-2"></i>Users</a>
                <a class="nav-link py-2 <?php echo ($activePage === 'logs' ? 'active' : ''); ?>" href="logs.php"><i class="bi bi-journal-text me-2"></i>Logs</a>
            <?php endif; ?>
            
            <!-- Logout -->
            <div class="nav-divider my-2" style="border-top: 1px solid #dee2e6;"></div>
            <a class="nav-link py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </nav>
    </div>
</div>
