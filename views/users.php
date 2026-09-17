<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>DENR MIMAROPA · Accounts</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="accounts.css">
</head>
<body>
<?php $sidebarPage = 'accounts'; require __DIR__ . '/partials/sidebar.php'; ?>
<main>
<div class="accounts-page">
    <header class="topbar">
        <a class="text-link" href="stockroom.php">← Inventory</a>
        <?php require __DIR__ . '/partials/account-menu.php'; ?>
    </header>
    <div class="heading"><div><h1>Manage accounts</h1><p>Account names, roles, and current status.</p></div></div>
    <section class="panel">
        <div class="panel-heading"><h2>Accounts</h2></div>
        <?php foreach ($accounts as $account): ?>
            <article class="account-row account-summary">
                <strong><?= e($account['username']) ?></strong>
                <span class="category"><?= e($roles[$account['role']]) ?></span>
                <span class="account-status <?= $account['active'] ? 'good' : 'out' ?>">
                    <span aria-hidden="true"><?= $account['active'] ? '●' : '○' ?></span>
                    <?= $account['active'] ? 'Active' : 'Inactive' ?>
                </span>
            </article>
        <?php endforeach; ?>
    </section>
</div>
</main>
<script src="app.js"></script>
</body>
</html>
