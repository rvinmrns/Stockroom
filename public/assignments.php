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
unset($_SESSION['assignment_values'], $_SESSION['assignment_error'], $_SESSION['assignment_flash']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrfCheck();
        if (($_POST['action'] ?? '') !== 'save_assignment') { throw new InvalidArgumentException('Unknown action.'); }
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
        $statement = $db->prepare('INSERT INTO item_assignments (brand,description,unit,serial_number,employee_name,position,office,date_received) VALUES (?,?,?,?,?,?,?,?)');
        $statement->execute(array_map(static fn($key)=>$values[$key], array_keys($fields)));
        $message = 'Item and employee information saved.';
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
    header('Location: assignments.php', true, 303); exit;
}
$assignments = $db->query('SELECT * FROM item_assignments ORDER BY id DESC')->fetchAll();
beginPage();
require dirname(__DIR__) . '/views/assignments.php';
endPage(['page'=>'assignments','assignments'=>$assignments]);
