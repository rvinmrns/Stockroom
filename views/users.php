<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Stockroom · Accounts</title>
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
    <?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($flash): ?><div class="notice success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <nav class="account-filters" aria-label="Account lists">
        <a class="button <?= $accountView === 'active' ? 'primary' : '' ?>" href="users.php?view=active" <?= $accountView === 'active' ? 'aria-current="page"' : '' ?>>Active users</a>
        <a class="button <?= $accountView === 'archived' ? 'primary' : '' ?>" href="users.php?view=archived" <?= $accountView === 'archived' ? 'aria-current="page"' : '' ?>>Archived accounts</a>
    </nav>
    <?php $active = $accountView === 'active' ? 1 : 0; $heading = $active ? 'Active users' : 'Archived accounts'; ?>
    <?php $group = array_filter($accounts, static fn($account) => (int)$account['active'] === $active); ?>
    <section class="panel">
        <div class="panel-heading"><h2><?= e($heading) ?></h2><span class="count"><?= count($group) ?> accounts</span></div>
        <?php foreach ($group as $account): ?>
            <article class="account-row account-summary">
                <strong><?= e($account['username']) ?></strong>
                <span class="category"><?= e($roles[$account['role']]) ?></span>
                <span class="account-status <?= $account['active'] ? 'good' : 'out' ?>">
                    <span aria-hidden="true"><?= $account['active'] ? '●' : '○' ?></span>
                    <?= $account['active'] ? 'Active' : 'Inactive' ?>
                </span>
                <?php if ($account['role'] === 'user'): ?>
                <form method="post" action="<?= e($accountsUrl) ?>">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <input type="hidden" name="id" value="<?= (int)$account['id'] ?>">
                    <input type="hidden" name="action" value="<?= $active ? 'deactivate_account' : 'reactivate_account' ?>">
                    <button class="button" type="submit" aria-label="<?= $active ? 'Deactivate' : 'Reactivate' ?> <?= e($account['username']) ?>"><?= $active ? 'Deactivate' : 'Reactivate' ?></button>
                </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$group): ?><div class="empty-state"><p><?= $active ? 'No active accounts.' : 'No archived accounts.' ?></p></div><?php endif; ?>
    </section>
</div>
</main>
<script src="app.js"></script>
</body>
</html>
