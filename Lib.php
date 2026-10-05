<?php
session_start();
const SUBJECTS = ['Math', 'English', 'History', 'Geography', 'Art', 'Computer Science', 'Business'];
const LEVELS   = ['beginner', 'intermediate', 'advanced'];
const ROLES    = ['learner', 'tutor'];
 
function load($f) { return file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : []; }
function save($f, $d) { file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT)); }
function h($s) { return htmlspecialchars((string)$s); }
function is_admin() { return !empty($_SESSION['admin']); }
function opts($list, $sel) {
    foreach ($list as $v) echo '<option' . ((string)$v === (string)$sel ? ' selected' : '') . '>' . h($v) . '</option>';
}
function subj_text($sub) {
    $o = [];
    foreach ($sub as $k => $v) $o[] = h($k) . ' (' . h($v) . ')';
    return implode(', ', $o);
}
 
// Admin mode toggle (one button, no password: fine for a test project only)
if (isset($_POST['toggle_admin'])) {
    $_SESSION['admin'] = !is_admin();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
 
function page_top($title) { ?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title><?= h($title) ?> - Study Group Matcher</title>
<style>body{font-family:sans-serif;margin:20px}table{border-collapse:collapse}td,th{border:1px solid #999;padding:6px;vertical-align:top}.err{color:#b00}</style>
</head><body>
<p><a href="students.php">Students</a> | <a href="groups.php">Groups</a> | <a href="matches.php">Matches</a> | <a href="form.php">Add student</a>
<form method="post" style="display:inline;margin-left:20px"><button name="toggle_admin" value="1">Admin mode: <?= is_admin() ? 'ON' : 'OFF' ?></button></form></p>
<h1><?= h($title) ?></h1>
<?php }
 
function page_bottom() { echo '</body></html>'; }
 