<?php
require 'lib.php';
$students = load('students.json');
$groups   = load('groups.json');
$errors   = [];
 
if (isset($_POST['create_group'])) {
    $gname   = trim($_POST['gname'] ?? '');
    $subject = $_POST['subject'] ?? '';
    $level   = $_POST['level'] ?? '';
    $members = $_POST['members'] ?? [];
    if ($gname === '') $errors[] = 'Group name is required.';
    if (!in_array($subject, SUBJECTS, true)) $errors[] = 'Pick a subject.';
    if (!in_array($level, LEVELS, true)) $errors[] = 'Pick a level.';
    if (count($members) < 2) $errors[] = 'Pick at least 2 members.';
    foreach ($members as $m) {
        if (!isset($students[$m]) || $students[$m]['matched']) $errors[] = "Student $m is not available.";
    }
    if (!$errors) {
        $n = 1;
        while (isset($groups["G$n"])) $n++;
        $groups["G$n"] = ['name' => $gname, 'subject' => $subject, 'level' => $level, 'members' => array_values($members)];
        foreach ($members as $m) $students[$m]['matched'] = true;
        save('groups.json', $groups);
        save('students.json', $students);
        header('Location: groups.php');
        exit;
    }
}

// Handle student joining a group
if (isset($_POST['join_group'], $_POST['student_id'])) {
    $gid = $_POST['join_group'];
    $sid = $_POST['student_id'];
    if (isset($groups[$gid], $students[$sid])) {
        if (!$students[$sid]['matched'] && !in_array($sid, $groups[$gid]['members'], true)) {
            $groups[$gid]['members'][] = $sid;
            $students[$sid]['matched'] = true;
            save('groups.json', $groups);
            save('students.json', $students);
        }
    }
    header('Location: groups.php');
    exit;
}

// Handle student leaving a group
if (isset($_POST['leave_group'], $_POST['student_id'])) {
    $gid = $_POST['join_group'] ?? $_POST['leave_group'] ?? ''; // can pass via hidden field or button value
    // Alternatively, let's keep it simple: button value holds $gid, and we need the student ID
    $gid = $_POST['leave_group'];
    $sid = $_POST['student_id'];
    if (isset($groups[$gid], $students[$sid])) {
        $key = array_search($sid, $groups[$gid]['members']);
        if ($key !== false) {
            unset($groups[$gid]['members'][$key]);
            $groups[$gid]['members'] = array_values($groups[$gid]['members']);
            $students[$sid]['matched'] = false;
            save('groups.json', $groups);
            save('students.json', $students);
        }
    }
    header('Location: groups.php');
    exit;
}
 
if (is_admin() && isset($_POST['delete_group']) && isset($groups[$_POST['delete_group']])) {
    foreach ($groups[$_POST['delete_group']]['members'] as $m) {
        if (isset($students[$m])) $students[$m]['matched'] = false;
    }
    unset($groups[$_POST['delete_group']]);
    save('groups.json', $groups);
    save('students.json', $students);
    header('Location: groups.php');
    exit;
}
 
page_top('Study groups');
foreach ($groups as $gid => $g): ?>
    <h2><?= h($g['name']) ?> (<?= h($gid) ?>)</h2>
    <p>Subject: <?= h($g['subject']) ?> | Level: <?= h($g['level']) ?> | Members: <?= count($g['members']) ?></p>
    <ul>
        <?php foreach ($g['members'] as $mid): $m = $students[$mid] ?? null; ?>
            <li>
                <?= $m ? h($m['name']) . ' (' . h($m['role']) . ', year ' . h($m['year']) . ', ' . h($g['subject']) . ': ' . h($m['subjects'][$g['subject']] ?? 'n/a') . ')'
                       : 'Unknown student ' . h($mid) ?>
                <!-- Quick leave button for individual member -->
                <form method="post" style="display:inline; margin-left: 10px;">
                    <input type="hidden" name="student_id" value="<?= h($mid) ?>">
                    <button name="leave_group" value="<?= h($gid) ?>">Leave</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Join form for a group -->
    <form method="post" style="margin-bottom: 15px;">
        <select name="student_id">
            <option value="">-- Select student to join --</option>
            <?php foreach ($students as $sid => $s): if (!$s['matched']): ?>
                <option value="<?= h($sid) ?>"><?= h($s['name']) ?> (<?= h($sid) ?>)</option>
            <?php endif; endforeach; ?>
        </select>
        <button name="join_group" value="<?= h($gid) ?>">Join Group</button>
    </form>

    <?php if (is_admin()): ?>
        <form method="post"><button name="delete_group" value="<?= h($gid) ?>">Delete group (frees members)</button></form>
    <?php endif; ?>
<?php endforeach; ?>
<?php if (!$groups): ?><p>No groups yet.</p><?php endif; ?>
 
<hr>
<h2>Create a group</h2>
<?php foreach ($errors as $e): ?><p class="err"><?= h($e) ?></p><?php endforeach; ?>
<form method="post">
    <p>Group name: <input type="text" name="gname"></p>
    <p>Subject: <select name="subject"><?php opts(SUBJECTS, ''); ?></select>
       Level: <select name="level"><?php opts(LEVELS, ''); ?></select></p>
    <p>Members (unmatched students only):</p>
    <?php foreach ($students as $id => $s): if ($s['matched']) continue; ?>
        <label><input type="checkbox" name="members[]" value="<?= h($id) ?>">
            <?= h($s['name']) ?> (<?= h($s['role']) ?>, year <?= h($s['year']) ?>): <?= subj_text($s['subjects']) ?></label><br>
    <?php endforeach; ?>
    <br><button name="create_group" value="1">Create group</button>
</form>
<?php page_bottom();