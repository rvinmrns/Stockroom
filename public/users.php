<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/auth.php';
if (!$isAdmin) { if (wantsJson()) { jsonError('Only admins can view accounts.', 403); } http_response_code(403); exit('Only admins can view accounts.'); }
$accountView = ($_GET['view'] ?? '') === 'archived' ? 'archived' : 'active';
$accountsUrl = 'users.php?view=' . $accountView;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrfCheck();
        $action = $_POST['action'] ?? '';
        if (!in_array($action, ['deactivate_account', 'reactivate_account'], true)) {
            throw new InvalidArgumentException('Unknown account action.');
        }
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        if (!$id) { throw new InvalidArgumentException('Invalid account.'); }
        $statement = $db->prepare("UPDATE users SET active=? WHERE id=? AND role='user'");
        $statement->execute([$action === 'reactivate_account' ? 1 : 0, $id]);
        if (!$statement->rowCount()) { throw new InvalidArgumentException('User account not found. Admin accounts cannot be deactivated here.'); }
        $message = $action === 'reactivate_account' ? 'Account reactivated.' : 'Account moved to Archived accounts.';
        if (wantsJson()) { jsonResponse(['ok'=>true, 'message'=>$message, 'url'=>$accountsUrl, 'csrf'=>$_SESSION['csrf']]); }
        $_SESSION['accounts_flash'] = $message;
    } catch (InvalidArgumentException $exception) {
        if (wantsJson()) { jsonError($exception->getMessage(), 422); }
        $_SESSION['accounts_error'] = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log((string)$exception);
        if (wantsJson()) { jsonError('Could not update the account. Please try again.', 500); }
        $_SESSION['accounts_error'] = 'Could not update the account. Please try again.';
    }
    header('Location: ' . $accountsUrl, true, 303); exit;
}
$flash = $_SESSION['accounts_flash'] ?? null;
$error = $_SESSION['accounts_error'] ?? null;
unset($_SESSION['accounts_flash'], $_SESSION['accounts_error']);
$accounts = $db->query("SELECT id, username, role, active FROM users WHERE role='user' ORDER BY username")->fetchAll();
beginPage();
require dirname(__DIR__) . '/views/users.php';
endPage(['page'=>'accounts', 'view'=>$accountView, 'accounts'=>$accounts]);
