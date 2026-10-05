<?php
require 'lib.php';
$students = load('students.json');
$sel = $_GET['id'] ?? '';
$me  = $students[$sel] ?? null;
page_top('Ranked matches');
?>
<form method="get">
    Find matches for:
    <select name="id"><option value="">-- choose --</option>
        <?php foreach ($students as $id => $s): ?>
            <option value="<?= h($id) ?>" <?= $id === $sel ? 'selected' : '' ?>><?= h($s['name']) ?> (<?= h($id) ?>)</option>
        <?php endforeach; ?>
    </select>
    <button>Rank</button>
</form>
<p>Scoring: +3 per shared subject, +1 more if the level is the same, +2 if one is a tutor and one a learner, +1 for the same year. Matched students are excluded.</p>
 
<?php if ($me):
    $ranked = [];
    foreach ($students as $id => $o) {
        if ($id === $sel || $o['matched']) continue;
        $score = 0; $shared = [];
        foreach ($me['subjects'] as $sub => $lv) {
            if (isset($o['subjects'][$sub])) {
                $score += 3;
                if ($o['subjects'][$sub] === $lv) $score += 1;
                $shared[] = h($sub) . ' (' . h($lv) . ' / ' . h($o['subjects'][$sub]) . ')';
            }
        }
        if (!$shared) continue;
        if ($me['role'] !== $o['role']) $score += 2;
        if ($me['year'] === $o['year']) $score += 1;
        $ranked[$id] = ['score' => $score, 'shared' => $shared, 's' => $o];
    }
    uasort($ranked, fn($a, $b) => $b['score'] <=> $a['score']);
?>
    <h2>Best matches for <?= h($me['name']) ?></h2>
    <?php if ($me['matched']): ?><p>Note: this student is already in a group.</p><?php endif; ?>
    <table>
        <tr><th>Rank</th><th>Name</th><th>ID</th><th>Year</th><th>Role</th><th>Score</th><th>Shared subjects (you / them)</th></tr>
        <?php $r = 1; foreach ($ranked as $id => $m): ?>
            <tr><td><?= $r++ ?></td><td><?= h($m['s']['name']) ?></td><td><?= h($id) ?></td><td><?= h($m['s']['year']) ?></td>
                <td><?= h($m['s']['role']) ?></td><td><?= $m['score'] ?></td><td><?= implode('<br>', $m['shared']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php if (!$ranked): ?><p>No unmatched students share a subject with this student.</p><?php endif; ?>
    <p><a href="groups.php">Go to Groups to form a group</a></p>
<?php endif; ?>
<?php page_bottom();
 