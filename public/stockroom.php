<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/auth.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            if (wantsJson()) { jsonError('Your session expired. Open the login page and try again.', 403); }
            throw new InvalidArgumentException('Your session expired. Refresh this page and try again.');
        }
        if (!$canManageInventory) { http_response_code(403); throw new InvalidArgumentException('Your account has view-only access.'); }
        $action = (string) ($_POST['action'] ?? '');
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        if (in_array($action, ['delete', 'adjust', 'update'], true) && (!$id || $id < 1)) {
            throw new InvalidArgumentException('Select a valid product.');
        }
        if ($action === 'create' || $action === 'update') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '' || strlen($name) > 120) {
                throw new InvalidArgumentException('Product name is required and must be at most 120 bytes.');
            }
            $values = [$name, numberInput('quantity'), numberInput('minimum_stock')];
            if ($action === 'create') {
                $statement = $db->prepare('INSERT INTO products (name, quantity, minimum_stock) VALUES (?, ?, ?)');
            } else {
                $values[] = $id;
                $statement = $db->prepare('UPDATE products SET name=?, quantity=?, minimum_stock=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
            }
            $statement->execute($values);
            if ($statement->rowCount() === 0) { throw new InvalidArgumentException('This product no longer exists.'); }
            $_SESSION['flash'] = $action === 'create' ? 'Product added to inventory.' : 'Product updated.';
        } elseif ($action === 'adjust') {
            $amount = numberInput('amount');
            $direction = (string) ($_POST['direction'] ?? '');
            if ($amount < 1 || !in_array($direction, ['in', 'out'], true)) { throw new InvalidArgumentException('Choose stock in or out and enter at least one unit.'); }
            $delta = $direction === 'out' ? -$amount : $amount;
            $statement = $db->prepare('UPDATE products SET quantity=quantity+?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND CAST(quantity AS SIGNED)+? BETWEEN 0 AND 99999999');
            $statement->execute([$delta, $id, $delta]);
            if ($statement->rowCount() === 0) { throw new InvalidArgumentException('Adjustment failed. Check available stock and the product still exists. Maximum stock is 99,999,999.'); }
            $_SESSION['flash'] = 'Stock level updated.';
        } elseif ($action === 'delete') {
            $statement = $db->prepare('DELETE FROM products WHERE id=?');
            $statement->execute([$id]);
            $_SESSION['flash'] = 'Product deleted.';
        } else { throw new InvalidArgumentException('Unknown action.'); }
        redirect();
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log((string) $exception); $error = 'Could not save changes. Please try again.';
    }
    if ($error && wantsJson()) { jsonError($error, http_response_code() === 403 ? 403 : 422); }
}

$search = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? 'all');
if (!in_array($status, ['all', 'low', 'out'], true)) { $status = 'all'; }
$userStockPage = !$isAdmin && $status !== 'all';
$where = [];
$params = [];
if ($search !== '') {
    $where[] = 'name LIKE ? ESCAPE \'!\'';
    $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
    $params = [$term];
}
if ($status === 'low') { $where[] = $isAdmin ? 'quantity <= minimum_stock' : 'quantity > 0 AND quantity <= minimum_stock'; }
if ($status === 'out') { $where[] = 'quantity = 0'; }
$statement = $db->prepare('SELECT * FROM products' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY name');
$statement->execute($params);
$products = $statement->fetchAll();
$stats = $db->query('SELECT COUNT(*) AS products, COALESCE(SUM(quantity),0) AS units, COALESCE(SUM(quantity = 0),0) AS out_stock, COALESCE(SUM(quantity <= minimum_stock),0) AS low FROM products')->fetch();
if (!$isAdmin) { $stats['low'] = (int)$stats['low'] - (int)$stats['out_stock']; }
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inventory-' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Quantity', 'Minimum stock'], ',', '"', '');
    foreach ($products as $product) {
        $safeText = static fn(string $value): string => preg_match('/^[=+@\-\t\r\n]/', $value) ? "'" . $value : $value;
        fputcsv($output, [$safeText($product['name']), $product['quantity'], $product['minimum_stock']], ',', '"', '');
    }
    fclose($output);
    exit;
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$editing = null;
if ($canManageInventory && isset($_GET['edit'])) {
    $statement = $db->prepare('SELECT * FROM products WHERE id=?');
    $statement->execute([(int) $_GET['edit']]);
    $editing = $statement->fetch() ?: null;
}
$formOpen = $canManageInventory && (isset($_GET['add']) || $editing !== null);
if ($canManageInventory && $error && in_array($_POST['action'] ?? '', ['create', 'update'], true)) {
    $formOpen = true;
    $editing = ['id' => $_POST['action'] === 'update' ? (int) ($_POST['id'] ?? 0) : null, 'name' => $_POST['name'] ?? '', 'quantity' => $_POST['quantity'] ?? '', 'minimum_stock' => $_POST['minimum_stock'] ?? ''];
}
$workspaceTitle = $isAdmin ? 'Inventory' : 'Inventory catalog';
$workspaceDescription = $isAdmin ? 'Manage products, track stock, and review accounts.' : 'Find products, check availability, and export the inventory you need.';
if ($userStockPage) {
    $workspaceTitle = $status === 'low' ? 'Low stock' : 'Out of stock';
    $workspaceDescription = $status === 'low' ? 'Items with available units at or below minimum stock.' : 'Items with no units currently available.';
}
beginPage();
require dirname(__DIR__) . '/views/inventory.php';
endPage(['page'=>'inventory', 'user'=>$currentUser, 'products'=>$products, 'stats'=>$stats]);
