<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/auth.php';
if (!$isAdmin) { if (wantsJson()) { jsonError('Only admins can view accounts.', 403); } http_response_code(403); exit('Only admins can view accounts.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(405);
    header('Allow: GET, HEAD');
    if (wantsJson()) { jsonError('This account list is read-only.', 405); }
    exit('This account list is read-only.');
}
$accounts = $db->query('SELECT username, role, active FROM users ORDER BY username')->fetchAll();
beginPage();
require dirname(__DIR__) . '/views/users.php';
endPage(['page'=>'accounts', 'accounts'=>$accounts]);
