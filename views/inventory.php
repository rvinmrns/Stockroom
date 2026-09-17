<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DENR MIMAROPA · <?= e($workspaceTitle) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="accounts.css">
</head>
<body class="role-<?= e($currentUser['role']) ?>">
<?php $sidebarPage = 'inventory'; require __DIR__ . '/partials/sidebar.php'; ?>
<main>
    <header class="topbar"><?php require __DIR__ . '/partials/account-menu.php'; ?><span class="today"><?= e(date('M j, Y')) ?></span></header>
    <div class="content">
        <div class="heading"><div><div class="eyebrow"><?= e($roles[$currentUser['role']]) ?> WORKSPACE</div><h1><?= e($workspaceTitle) ?></h1><p><?= e($workspaceDescription) ?></p></div><?php if ($canManageInventory): ?><a class="button primary" href="?add=1">＋ Add product</a><?php endif; ?></div>
        <?php if ($flash): ?><div class="notice success" role="status"><?= e($flash) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
        <?php if (!$isAdmin): ?>
        <section class="role-welcome" aria-label="Your workspace">
            <div><span class="role-label">YOUR PRODUCT DIRECTORY</span><h2>Hello, <?= e($currentUser['username']) ?>.</h2><p>Explore the catalog below. Stock badges help you see what is available at a glance.</p></div>
            <div class="role-shortcuts"><a class="button secondary" href="stockroom.php?status=low">View low stock · <?= e($stats['low']) ?></a><a class="button secondary" href="stockroom.php?status=out">Out of stock</a></div>
        </section>
        <?php endif; ?>
        <section class="stats" aria-label="Inventory overview">
            <article class="stat"><span>Total products <span class="stat-icon">▦</span></span><strong><?= number_format((int) $stats['products']) ?></strong><small>Unique items in your catalog</small></article>
            <article class="stat"><span>Units in stock <span class="stat-icon">▤</span></span><strong><?= number_format((int) $stats['units']) ?></strong><small>Across all your products</small></article>
            <article class="stat"><span>Out of stock <span class="stat-icon warning">!</span></span><strong><?= number_format((int) $stats['out_stock']) ?></strong><small>No units currently available</small></article>
            <article class="stat"><span>Low-stock items <span class="stat-icon warning">!</span></span><strong><?= number_format((int) $stats['low']) ?></strong><small><span class="small-dot"></span> At or below minimum stock</small></article>
        </section>

        <?php if ($formOpen): ?>
        <section class="panel product-form">
            <div class="panel-heading"><h2><?= !empty($editing['id']) ? 'Edit product' : 'Add a product' ?></h2><a href="stockroom.php" class="text-link">Cancel</a></div>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="<?= !empty($editing['id']) ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
                <div class="form-grid">
                    <label>Product name<input name="name" maxlength="120" required placeholder="e.g. Wireless keyboard" value="<?= e($editing['name'] ?? '') ?>"></label>
                    <label>Quantity<input type="number" name="quantity" min="0" max="99999999" required value="<?= e($editing['quantity'] ?? 0) ?>"></label>
                    <label>Minimum stock<input type="number" name="minimum_stock" min="0" max="99999999" required value="<?= e($editing['minimum_stock'] ?? 5) ?>"></label>
                </div>
                <div class="form-footer"><span>Enter the product name and stock quantities.</span><button class="button primary">Save product</button></div>
            </form>
        </section>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-heading"><div class="catalog-title"><h2><?= $canManageInventory ? 'Product inventory' : 'Browse products' ?></h2><span class="count"><?= count($products) ?> items</span></div><a class="button secondary" href="?<?= e(http_build_query(['q' => $search, 'status' => $status, 'export' => 'csv'])) ?>">↓ Export CSV</a></div>
            <form class="filters" method="get"><div class="search-box"><span aria-hidden="true">⌕</span><input type="search" name="q" aria-label="Search products" placeholder="Search by product name…" value="<?= e($search) ?>"></div><select name="status" aria-label="Stock status"><option value="all">All stock levels</option><option value="low" <?= $status === 'low' ? 'selected' : '' ?>>Low stock</option><option value="out" <?= $status === 'out' ? 'selected' : '' ?>>Out of stock</option></select><button class="button secondary">Filter</button><?php if ($search !== '' || $status !== 'all'): ?><a class="text-link" href="stockroom.php">Clear</a><?php endif; ?></form>
            <?php if (!$canManageInventory): ?>
                <?php require __DIR__ . '/partials/catalog.php'; ?>
            <?php else: ?>
            <p class="stock-instructions">Use <strong>Edit</strong> for product details and <strong>Stock</strong> to receive or issue units.</p>
            <div class="table-wrap"><table><thead><tr><th>Product</th><th>Stock level</th><th class="actions-heading">Actions</th></tr></thead><tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><div class="product-cell"><span class="product-icon"><?= e(strtoupper(substr($product['name'], 0, 1))) ?></span><div><strong><?= e($product['name']) ?></strong></div></div></td>
                    <td><div class="stock-number"><?= number_format($product['quantity']) ?> <span>units</span></div><span class="badge <?= $product['quantity'] == 0 ? 'out' : ($product['quantity'] <= $product['minimum_stock'] ? 'low' : 'good') ?>"><?= $product['quantity'] == 0 ? 'Out of stock' : ($product['quantity'] <= $product['minimum_stock'] ? 'Low stock' : 'In stock') ?></span></td>
                    <td><?php if ($canManageInventory): ?><div class="row-actions"><a class="text-link" href="?edit=<?= e($product['id']) ?>">Edit</a><details><summary>Stock</summary><form method="post" class="adjust-form"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="adjust"><input type="hidden" name="id" value="<?= e($product['id']) ?>"><select name="direction" aria-label="Stock direction"><option value="in">Stock in</option><option value="out">Stock out</option></select><input type="number" name="amount" min="1" max="99999999" value="1" required aria-label="Units to adjust"><button class="button secondary">Apply</button></form></details><form method="post" class="delete-form"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($product['id']) ?>"><button class="delete-button" aria-label="Delete <?= e($product['name']) ?>">Delete</button></form></div><?php else: ?><span class="count">View only</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$products): ?><tr><td colspan="3"><div class="empty-state"><span class="empty-icon">▦</span><h3><?= $search !== '' || $status !== 'all' ? 'No matching products' : 'Your inventory starts here' ?></h3><p><?= $search !== '' || $status !== 'all' ? 'Try another search or clear your filters.' : ($canManageInventory ? 'Add your first product to start keeping track of your inventory.' : 'Your admin can add products here.') ?></p><?php if ($canManageInventory): ?><a class="button primary" href="?add=1">＋ Add product</a><?php endif; ?></div></td></tr><?php endif; ?>
            </tbody></table></div>
            <?php endif; ?>
            <div class="table-footer">Showing <?= count($products) ?> of <?= e($stats['products']) ?> products<span>Made for a more organized day.</span></div>
        </section>
        <footer class="page-footer"><span class="small-dot green"></span> <?= $canManageInventory ? 'Your inventory is saved automatically after every change.' : 'Inventory is maintained by your admin.' ?></footer>
    </div>
</main>
<script src="app.js"></script>
</body>
</html>
