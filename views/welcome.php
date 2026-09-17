<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DENR MIMAROPA · Choose your account</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="accounts.css"></head>
<body class="login-body welcome-body"><section class="panel welcome-panel">
<a class="brand" href="stockroom.php"><span class="brand-name">DENR MIMAROPA</span></a>
<header class="welcome-heading"><span class="eyebrow">YOUR INVENTORY WORKSPACE</span><h1>Welcome to DENR MIMAROPA</h1><p>Choose your account type to log in or sign up.</p></header>
<div class="portal-grid">
<?php foreach (['user' => 'User'] as $value => $label): ?>
<section class="portal-card" aria-labelledby="portal-<?= e($value) ?>">
<span class="portal-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?php if ($value === 'user'): ?><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/><?php else: ?><path d="m12 3 9 5v9l-9 5-9-5V8l9-5Z M3 8l9 5 9-5 M12 13v9 M7.5 5.5l9 5v4"/><?php endif; ?></svg></span>
<h2 id="portal-<?= e($value) ?>"><?= e($label) ?></h2><p><?= $value === 'user' ? 'Browse, search, and export inventory.' : 'Manage products and keep stock up to date.' ?></p>
<div class="portal-actions">
<a class="button primary" href="stockroom.php?portal=<?= e($value) ?>" aria-label="Log in as <?= e($label) ?>">Log in <span aria-hidden="true">→</span></a>
<a class="button secondary" href="stockroom.php?portal=<?= e($value) ?>&amp;signup=1" aria-label="Sign up as <?= e($label) ?>">Sign up</a>
</div>
</section>
<?php endforeach; ?>
</div>
<footer class="welcome-admin"><div><strong>Administrator access</strong><p>Sign in to your admin workspace.</p></div><a class="button secondary" href="stockroom.php?portal=admin">Admin login <span aria-hidden="true">→</span></a></footer>
</section></body></html>
