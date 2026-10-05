<?php
require 'lib.php';
$students = load('students.json');
$groups   = load('groups.json');
 
// Admin only: delete a student (and remove them from any group)
if (is_admin() && isset($_POST['delete'])) {
    $did = $_POST['delete'];
    unset($students[$did]);
    foreach ($groups as $gid => $g) {
        $g['members'] = array_values(array_diff($g['members'], [$did]));
        if ($g['members']) { $groups[$gid] = $g; } else { unset($groups[$gid]); }
    }
    save('students.json', $students);
    save('groups.json', $groups);
    header('Location: students.php');
    exit;
}
 
$fs = $_GET['subject'] ?? '';
$fy = $_GET['year'] ?? '';
$rows = array_filter($students, fn($s) =>
    ($fs === '' || isset($s['subjects'][$fs])) && ($fy === '' || (string)$s['year'] === $fy));
 
page_top(is_admin() ? 'Students (admin view)' : 'Students');
?>
<form method="get">
    Subject: <select name="subject"><option value="">All</option><?php opts(SUBJECTS, $fs); ?></select>
    Year: <select name="year"><option value="">All</option><?php opts([1, 2, 3, 4], $fy); ?></select>
    <button>Search</button>
</form><br>
 
<table>
    <tr>
        <?php if (is_admin()): ?><th>ID</th><?php endif; ?>
        <th>Name</th><th>Year</th><th>Role</th><th>Subjects</th><th>Matched</th>
        <?php if (is_admin()): ?><th>Actions</th><?php endif; ?>
    </tr>
    <?php foreach ($rows as $id => $s): ?>
        <tr>
            <?php if (is_admin()): ?><td><?= h($id) ?></td><?php endif; ?>
            <td><?= h($s['name']) ?></td>
            <td><?= h($s['year']) ?></td>
            <td><?= h($s['role']) ?></td>
            <td><?= subj_text($s['subjects']) ?></td>
            <td><?= $s['matched'] ? 'Yes' : 'No' ?></td>
            <?php if (is_admin()): ?>
                <td>
                    <a href="form.php?id=<?= urlencode($id) ?>">Edit</a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this student?')">
                        <button name="delete" value="<?= h($id) ?>">Delete</button>
                    </form>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (!$rows): ?><p>No students match that search.</p><?php endif; ?>
<?php page_bottom();