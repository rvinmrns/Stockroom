<aside class="sidebar">
    <a class="brand" href="stockroom.php"><span class="brand-icon">▦</span><span class="brand-name">DENR MIMAROPA</span></a>
    <div class="workspace"><span class="workspace-icon">S</span><div><?= e($roles[$currentUser['role']]) ?><small><?= $canManageInventory ? 'Inventory management' : 'Browse inventory' ?></small></div></div>
    <div class="nav-label">WORKSPACE</div>
    <nav class="sidebar-links" aria-label="Workspace">
        <a class="nav-item <?= $sidebarPage === 'inventory' ? 'active' : '' ?>" href="stockroom.php"><span aria-hidden="true">▦</span> Inventory<?php if (isset($stats)): ?><span class="nav-count"><?= e($stats['products']) ?></span><?php endif; ?></a>
        <?php if (!$isAdmin): ?>
        <a class="nav-item" href="stockroom.php?status=low">Low stock</a>
        <a class="nav-item" href="stockroom.php?status=out">Out of stock</a>
        <?php else: ?>
        <a class="nav-item <?= $sidebarPage === 'accounts' ? 'active' : '' ?>" href="users.php">Manage accounts</a>
        <a class="nav-item <?= $sidebarPage === 'assignments' ? 'active' : '' ?>" href="assignments.php">Item assignments</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-bottom"><span class="avatar">ME</span><div><?= e($currentUser['username']) ?><small><?= e($roles[$currentUser['role']]) ?></small></div></div>
        <form class="sidebar-signout" method="post" action="stockroom.php">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="action" value="logout">
            <button class="button secondary" type="submit">Sign out <span aria-hidden="true">→</span></button>
        </form>
    </div>
</aside>
