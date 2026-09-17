<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>DENR MIMAROPA · <?= $isSignup ? 'Sign up' : 'Sign in' ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="accounts.css">
</head>
<body class="login-body">
<section class="panel login-panel">
    <a class="brand" href="stockroom.php"><span class="brand-name">DENR MIMAROPA</span></a>
    <h1><?= $needsSetup ? 'Welcome to DENR MIMAROPA' : ($portalRole !== '' ? e($roles[$portalRole]) . ($isSignup ? ' sign up' : ' login') : ($isSignup ? 'Create your account' : 'Welcome back')) ?></h1>
    <p><?= $needsSetup ? 'Create your first admin account to get started.' : ($isSignup ? 'Create a user account to browse inventory.' : 'Sign in to access your inventory workspace.') ?></p>
    <?php if ($authError): ?><div class="notice error" role="alert"><?= e($authError) ?></div><?php endif; ?>
    <form method="post" action="stockroom.php?<?= e(($isSignup ? 'signup=1' : 'login=1') . ($portalQuery !== '' ? '&' . $portalQuery : '')) ?>" class="account-form">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="<?= $needsSetup ? 'setup' : ($isSignup ? 'signup' : 'login') ?>">
        <label>Username<input name="username" required autocomplete="username" maxlength="40" <?= $needsSetup || $isSignup ? 'minlength="3" pattern="[a-zA-Z0-9_.-]{3,40}"' : '' ?> value="<?= e($authUsername) ?>"></label>
        <label>Password<input type="password" name="password" required autocomplete="<?= $needsSetup || $isSignup ? 'new-password' : 'current-password' ?>" <?= $needsSetup || $isSignup ? 'minlength="5" maxlength="72"' : '' ?>></label>
        <?php if ($isSignup): ?>
            <input type="hidden" name="role" value="user">
            <small>Users can view and export inventory. Admins manage products and stock.</small>
        <?php endif; ?>
        <?php if ($needsSetup || $isSignup): ?><small>Username: 3–40 letters, numbers, dots, underscores or hyphens. Password: at least 5 characters.</small><?php endif; ?>
        <button class="button primary"><?= $needsSetup ? 'Create admin account' : ($isSignup ? 'Sign up' : 'Sign in') ?></button>
        <?php if (!$needsSetup): ?>
            <a class="text-link" href="<?= $isSignup ? 'stockroom.php' : 'stockroom.php?signup=1' ?>"><?= $isSignup ? 'Already have an account? Log in' : 'New here? Sign up' ?></a>
        <?php endif; ?>
    </form>
</section>
<script src="app.js"></script>
</body>
</html>
