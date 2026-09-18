<?php
declare(strict_types=1);

$roles = ['user' => 'User', 'admin' => 'Admin'];

function csrfCheck(): void {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        if (wantsJson()) { jsonError('Your session expired. Open the login page and try again.', 403); }
        throw new InvalidArgumentException('Your session expired. Refresh and try again.');
    }
}
function accountFields(): array {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
        throw new InvalidArgumentException('Use 3–40 letters, numbers, dots, underscores or hyphens for the username.');
    }
    if (strlen($password) < 5 || strlen($password) > 72) {
        throw new InvalidArgumentException('Use a password between 5 and 72 bytes.');
    }
    return [$username, password_hash($password, PASSWORD_DEFAULT)];
}

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $statement = $db->prepare('SELECT id, username, role FROM users WHERE id=? AND active=1');
    $statement->execute([$_SESSION['user_id']]);
    $currentUser = $statement->fetch() ?: null;
    if (!$currentUser) { unset($_SESSION['user_id']); }
}
$needsSetup = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;
$authError = $_SESSION['auth_error'] ?? null;
$authUsername = $_SESSION['auth_username'] ?? '';
$signupRole = $_SESSION['signup_role'] ?? 'user';
$isSignup = !$needsSetup && ($_GET['signup'] ?? '') === '1';
$portalRole = $isSignup ? (string) ($_GET['portal'] ?? '') : '';
if (!isset($roles[$portalRole])) { $portalRole = ''; }
$portalQuery = $portalRole !== '' ? 'portal=' . rawurlencode($portalRole) : '';
unset($_SESSION['auth_error'], $_SESSION['auth_username'], $_SESSION['signup_role']);
$authAction = (string) ($_POST['action'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($authAction, ['setup', 'signup', 'login', 'logout'], true)) {
    try {
        csrfCheck();
        if ($authAction === 'logout') {
            $_SESSION = [];
            session_regenerate_id(true);
            redirect();
        }
        if ($authAction === 'setup') {
            if (!$needsSetup) { throw new InvalidArgumentException('Setup is already complete. Please sign in.'); }
            [$username, $hash] = accountFields();
            // Serialize first-account creation so concurrent requests cannot create a second bootstrap admin.
            $setupLock = 'stockroom_setup_' . (getenv('DB_NAME') ?: 'inventory_hq');
            $lock = $db->prepare('SELECT GET_LOCK(?, 5)');
            $lock->execute([$setupLock]);
            if ((int) $lock->fetchColumn() !== 1) { throw new InvalidArgumentException('Setup is busy. Please try again.'); }
            try {
                if ((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) {
                    throw new InvalidArgumentException('Setup is already complete. Please sign in.');
                }
                $db->prepare("INSERT INTO users (username,password_hash,role) VALUES (?,?,'admin')")->execute([$username, $hash]);
                $userId = (int) $db->lastInsertId();
            } finally {
                $db->prepare('SELECT RELEASE_LOCK(?)')->execute([$setupLock]);
            }
        } elseif ($authAction === 'signup') {
            if ($needsSetup) { throw new InvalidArgumentException('Create the first admin account before signing up.'); }
            $signupRole = (string) ($_POST['role'] ?? 'user');
            if ($signupRole !== 'user') {
                throw new InvalidArgumentException('New accounts must use the User role.');
            }
            if ($portalRole !== '' && $portalRole !== $signupRole) { throw new InvalidArgumentException('Choose the matching account portal.'); }
            [$username, $hash] = accountFields();
            $db->prepare('INSERT INTO users (username,password_hash,role) VALUES (?,?,?)')->execute([$username, $hash, $signupRole]);
            $userId = (int) $db->lastInsertId();
        } else {
            $statement = $db->prepare('SELECT * FROM users WHERE username=?');
            $statement->execute([trim((string) ($_POST['username'] ?? ''))]);
            $account = $statement->fetch();
            if (!$account || !password_verify((string) ($_POST['password'] ?? ''), $account['password_hash'])) {
                throw new InvalidArgumentException('Invalid username or password.');
            }
            if (!(int) $account['active']) {
                throw new InvalidArgumentException('Your account has been deactivated. Please contact the administrator.');
            }
            $userId = (int) $account['id'];
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        redirect();
    } catch (InvalidArgumentException $exception) { $authError = $exception->getMessage(); }
    catch (PDOException $exception) {
        error_log((string) $exception);
        $authError = (int) ($exception->errorInfo[1] ?? 0) === 1062 ? 'That username is already taken. Choose another username.' : 'Unable to sign in or create your account. Please try again.';
    }
    // Show validation errors on a GET page so refresh cannot resubmit credentials.
    if (wantsJson()) { jsonError($authError, $authAction === 'login' ? 401 : 422); }
    $_SESSION['auth_error'] = $authError;
    $_SESSION['auth_username'] = is_string($_POST['username'] ?? null) ? substr($_POST['username'], 0, 40) : '';
    $_SESSION['signup_role'] = 'user';
    $query = ($authAction === 'signup' ? 'signup=1' : 'login=1') . ($portalQuery !== '' ? '&' . $portalQuery : '');
    header('Location: stockroom.php?' . $query, true, 303);
    exit;
}
if (!$currentUser) {
    if (wantsJson() && $_SERVER['REQUEST_METHOD'] === 'POST') { jsonError('Please sign in to continue.', 401); }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { http_response_code(401); }
    beginPage();
    require dirname(__DIR__) . '/views/login.php';
    endPage(['page'=>'login', 'signup'=>$isSignup, 'setup'=>$needsSetup]);
    exit;
}
if ($authError) { if (wantsJson()) { jsonError($authError, 403); } http_response_code(403); exit(e($authError)); }
$isAdmin = $currentUser['role'] === 'admin';
$canManageInventory = $isAdmin;
