<?php
require 'lib.php';
$students = load('students.json');
$editId  = $_POST['orig_id'] ?? $_GET['id'] ?? '';
$editing = $editId !== '' && isset($students[$editId]);
$errors  = [];
 
if ($editing) {
    $cur = $students[$editId];
    $name = $cur['name']; $id = $editId; $year = $cur['year']; $role = $cur['role']; $picked = $cur['subjects'];
} else {
    $name = ''; $id = ''; $year = 1; $role = 'learner'; $picked = [];
}
 
if (isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    if (!$editing) { $id = trim($_POST['id'] ?? ''); }
    $year = (int)($_POST['year'] ?? 0);
    $role = $_POST['role'] ?? '';
    $picked = [];
    foreach (($_POST['subject'] ?? []) as $i => $sub) {
        if ($sub === '') continue;                       // unused row
        $lv = $_POST['level'][$i] ?? '';
        if (!in_array($sub, SUBJECTS, true) || !in_array($lv, LEVELS, true)) { $errors[] = 'Invalid subject or level.'; continue; }
        if (isset($picked[$sub])) { $errors[] = "$sub was picked twice."; continue; }
        $picked[$sub] = $lv;
    }
    if ($name === '') $errors[] = 'Name is required.';
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $id)) $errors[] = 'ID is required (letters, numbers, - or _ only).';
    if (!$editing && isset($students[$id])) $errors[] = "ID $id already exists.";
    if ($year < 1 || $year > 4) $errors[] = 'Year must be 1 to 4.';
    if (!in_array($role, ROLES, true)) $errors[] = 'Pick a role.';
    if (count($picked) < 1) $errors[] = 'Pick at least 1 subject.';
 
    if (!$errors) {
        $students[$id] = [
            'name' => $name, 'year' => $year,
            'matched' => $editing ? $students[$id]['matched'] : false,
            'role' => $role, 'subjects' => $picked,
        ];
        save('students.json', $students);
        header('Location: students.php');
        exit;
    }
}
 
$pairs = [];
foreach ($picked as $k => $v) $pairs[] = [$k, $v];
 
page_top($editing ? 'Edit student' : 'Add student');
foreach ($errors as $e): ?><p class="err"><?= h($e) ?></p><?php endforeach; ?>
 
<form method="post">
    <?php if ($editing): ?><input type="hidden" name="orig_id" value="<?= h($editId) ?>"><?php endif; ?>
    <p>Full name: <input type="text" name="name" value="<?= h($name) ?>"></p>
    <p>Student ID: <input type="text" name="id" value="<?= h($id) ?>" <?= $editing ? 'readonly' : '' ?>></p>
    <p>Year: <select name="year"><?php opts([1, 2, 3, 4], $year); ?></select></p>
    <p>Role: <select name="role"><?php opts(ROLES, $role); ?></select></p>
    <p>Subjects (1 to 6, each with a proficiency):</p>
    <?php for ($i = 0; $i < 6; $i++): ?>
        <p>
            <select name="subject[]"><option value="">-- none --</option><?php opts(SUBJECTS, $pairs[$i][0] ?? ''); ?></select>
            <select name="level[]"><?php opts(LEVELS, $pairs[$i][1] ?? 'beginner'); ?></select>
        </p>
    <?php endfor; ?>
    <button name="save" value="1"><?= $editing ? 'Save changes' : 'Add student' ?></button>
</form>
<?php page_bottom();