<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Stockroom · Item assignments</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="accounts.css"></head>
<body>
<?php $sidebarPage = 'assignments'; require __DIR__ . '/partials/sidebar.php'; ?>
<main><div class="accounts-page">
<header class="topbar"><a class="text-link" href="stockroom.php">← Inventory</a><?php require __DIR__ . '/partials/account-menu.php'; ?></header>
<div class="heading"><div><div class="eyebrow">ADMIN WORKSPACE</div><h1>Item assignments</h1><p>Record an item and the employee who receives it.</p></div></div>
<?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($flash): ?><div class="notice success" role="status"><?= e($flash) ?></div><?php endif; ?>
<section class="panel product-form">
<div class="panel-heading"><h2><?= $editId ? 'Edit assignment' : 'Item and employee information' ?></h2><span class="count">All fields required</span></div>
<form method="post" action="assignments.php">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="<?= $editId ? 'update_assignment' : 'save_assignment' ?>">
<?php if ($editId): ?><input type="hidden" name="id" value="<?= (int)$editId ?>"><?php endif; ?>
<div class="form-grid assignment-fields">
<label>Brand<input name="brand" maxlength="120" required value="<?= e($values['brand'] ?? '') ?>" placeholder="e.g. Dell"></label>
<label>Unit<input name="unit" maxlength="60" required value="<?= e($values['unit'] ?? '') ?>" placeholder="e.g. piece, set, unit"></label>
<label>Serial number<input name="serial_number" maxlength="120" required value="<?= e($values['serial_number'] ?? '') ?>" placeholder="Unique manufacturer serial number"><small>Each serial number can be registered only once.</small></label>
<label class="assignment-description">Description<textarea name="description" maxlength="2000" rows="3" required placeholder="Item model, specifications, or other details"><?= e($values['description'] ?? '') ?></textarea></label>
<label>Employee name<input name="employee_name" maxlength="160" required value="<?= e($values['employee_name'] ?? '') ?>" placeholder="Full name"></label>
<label>Position<input name="position" maxlength="160" required value="<?= e($values['position'] ?? '') ?>" placeholder="Employee position"></label>
<label>Office<input name="office" maxlength="160" required value="<?= e($values['office'] ?? '') ?>" placeholder="Assigned office"></label>
<label>Date received<input type="text" name="date_received" data-date-input inputmode="numeric" maxlength="10" placeholder="DD-MM-YYYY" title="Enter the date as DD-MM-YYYY." required value="<?= e($values['date_received'] ?? '') ?>"></label>
</div><div class="form-footer"><span>Serial numbers are checked against saved assignments.</span><?php if ($editId): ?><a class="button" href="assignments.php">Cancel</a><?php endif; ?><button class="button primary" type="submit"><?= $editId ? 'Save changes' : 'Save' ?></button></div>
</form></section>
<section class="panel"><div class="panel-heading"><h2>Saved assignments</h2><span class="count"><?= count($assignments) ?> records</span></div>
<div class="table-wrap"><table><thead><tr><th>Item</th><th>Unit</th><th>Serial number</th><th>Employee</th><th>Position and office</th><th>Date received</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($assignments as $assignment): ?><tr>
<td class="assignment-item"><strong><?= e($assignment['brand']) ?></strong><p><?= nl2br(e($assignment['description'])) ?></p></td>
<td><?= e($assignment['unit']) ?></td><td><?= e($assignment['serial_number']) ?></td><td><?= e($assignment['employee_name']) ?></td>
<td class="assignment-item"><strong><?= e($assignment['position']) ?></strong><p><?= e($assignment['office']) ?></p></td><td><?= e(DateTimeImmutable::createFromFormat('!Y-m-d', $assignment['date_received'])->format('d-m-Y')) ?></td>
<td><a class="button" href="assignments.php?edit=<?= (int)$assignment['id'] ?>" aria-label="Edit assignment <?= e($assignment['serial_number']) ?>">Edit</a></td>
</tr><?php endforeach; ?>
<?php if (!$assignments): ?><tr><td colspan="7"><div class="empty-state"><h3>No assignments yet</h3><p>Complete the form above to register the first item.</p></div></td></tr><?php endif; ?>
</tbody></table></div></section>
</div></main>
<script src="app.js"></script></body></html>
