<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/auth.php';
if (!$isAdmin) {
    if (wantsJson()) { jsonError('Only admins can access item assignments.', 403); }
    http_response_code(403); exit('Only admins can access item assignments.');
}
$fields = ['brand'=>['Brand',120], 'description'=>['Description',2000], 'unit'=>['Unit',60], 'serial_number'=>['Serial number',120], 'employee_name'=>['Employee name',160], 'position'=>['Position',160], 'office'=>['Office',160], 'date_received'=>['Date received',10]];
$values = $_SESSION['assignment_values'] ?? [];
$error = $_SESSION['assignment_error'] ?? null;
$flash = $_SESSION['assignment_flash'] ?? null;
$editId = 0;
unset($_SESSION['assignment_values'], $_SESSION['assignment_error'], $_SESSION['assignment_flash']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrfCheck();
        $action = $_POST['action'] ?? '';
        if (!in_array($action, ['save_assignment', 'update_assignment'], true)) { throw new InvalidArgumentException('Unknown action.'); }
        if ($action === 'update_assignment') {
            $editId = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
            if (!$editId) { throw new InvalidArgumentException('Invalid assignment ID.'); }
            $existing = $db->prepare('SELECT id FROM item_assignments WHERE id = ?');
            $existing->execute([$editId]);
            if (!$existing->fetchColumn()) { throw new InvalidArgumentException('Assignment not found.'); }
        }
        foreach ($fields as $key=>[$label,$limit]) {
            $raw = $_POST[$key] ?? '';
            if (!is_string($raw)) { throw new InvalidArgumentException($label . ' must be text.'); }
            $values[$key] = trim($raw);
        }
        $values['serial_number'] = strtoupper($values['serial_number']);
        foreach ($fields as $key=>[$label,$limit]) {
            if ($values[$key] === '' || strlen($values[$key]) > $limit) {
                throw new InvalidArgumentException($label . ' is required and must be at most ' . $limit . ' bytes.');
            }
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['date_received']);
        if (!$date || $date->format('Y-m-d') !== $values['date_received'] || $values['date_received'] < '1000-01-01') {
            throw new InvalidArgumentException('Enter a valid date received.');
        }
        $parameters = array_map(static fn($key)=>$values[$key], array_keys($fields));
        if ($editId) {
            $statement = $db->prepare('UPDATE item_assignments SET brand=?,description=?,unit=?,serial_number=?,employee_name=?,position=?,office=?,date_received=? WHERE id=?');
            $parameters[] = $editId;
        } else {
            $statement = $db->prepare('INSERT INTO item_assignments (brand,description,unit,serial_number,employee_name,position,office,date_received) VALUES (?,?,?,?,?,?,?,?)');
        }
        $statement->execute($parameters);
        $message = $editId ? 'Assignment updated.' : 'Item and employee information saved.';
        if (wantsJson()) { jsonResponse(['ok'=>true,'message'=>$message,'url'=>'assignments.php','csrf'=>$_SESSION['csrf']]); }
        $_SESSION['assignment_flash'] = $message;
        header('Location: assignments.php', true, 303); exit;
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
    catch (PDOException $exception) {
        if ((int)($exception->errorInfo[1] ?? 0) === 1062) { $error = 'That serial number is already registered. Enter a unique serial number.'; }
        else { error_log((string)$exception); $error = 'Could not save the assignment. Please try again.'; }
    }
    if (wantsJson()) { jsonError($error, 422); }
    $_SESSION['assignment_values'] = $values;
    $_SESSION['assignment_error'] = $error;
    header('Location: assignments.php' . ($editId ? '?edit=' . $editId : ''), true, 303); exit;
}
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    $statement = $db->prepare('SELECT * FROM item_assignments WHERE id = ?');
    $statement->execute([$editId ?: 0]);
    $assignmentToEdit = $statement->fetch();
    if (!$assignmentToEdit) {
        if (wantsJson()) { jsonError('Assignment not found.', 404); }
        http_response_code(404);
        $error = 'Assignment not found.';
        $editId = 0;
        $values = [];
    } elseif (!$error) {
        $values = $assignmentToEdit;
    }
}
$assignments = $db->query('SELECT * FROM item_assignments ORDER BY id DESC')->fetchAll();
beginPage();
require dirname(__DIR__) . '/views/assignments.php';
endPage(['page'=>'assignments','assignments'=>$assignments]);
