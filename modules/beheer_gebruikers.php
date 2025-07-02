<?php
if (!in_array($rol, ['admin', 'superadmin'])) {
    exit("⛔ Geen toegang tot gebruikersbeheer");
}

$gebruikers = $pdo->query("SELECT * FROM notificatie_ontvangers ORDER BY aangemeld_op DESC")->fetchAll();

// Goedkeuren/intrekken/promotie
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE notificatie_ontvangers SET goedgekeurd = 1 WHERE chat_id = ?")->execute([$_GET['approve']]);
}
if (isset($_GET['revoke'])) {
    $pdo->prepare("UPDATE notificatie_ontvangers SET goedgekeurd = 0 WHERE chat_id = ?")->execute([$_GET['revoke']]);
}
if (isset($_GET['promote']) && $rol === 'superadmin') {
    $target_id = $_GET['promote'];
    $stmt = $pdo->prepare("SELECT chat_id FROM notificatie_ontvangers WHERE chat_id = ?");
    $stmt->execute([$target_id]);
    $target_chat_id = $stmt->fetchColumn();
    if ($target_chat_id !== $_SESSION['user_chat_id']) {
        $pdo->prepare("UPDATE notificatie_ontvangers SET rol = 'admin' WHERE chat_id = ?")->execute([$target_id]);
    }
}
if (isset($_GET['demote']) && $rol === 'superadmin') {
    $target_id = $_GET['demote'];
    $stmt = $pdo->prepare("SELECT chat_id FROM notificatie_ontvangers WHERE chat_id = ?");
    $stmt->execute([$target_id]);
    $target_chat_id = $stmt->fetchColumn();
    if ($target_chat_id !== $_SESSION['user_chat_id']) {
        $pdo->prepare("UPDATE notificatie_ontvangers SET rol = 'gebruiker' WHERE chat_id = ?")->execute([$target_id]);
    }
}

$gebruikers = $pdo->query("SELECT * FROM notificatie_ontvangers ORDER BY aangemeld_op DESC")->fetchAll();

?>

<h3>👥 Gebruikersbeheer</h3>
<table border="1" cellpadding="6" cellspacing="0">
<tr>
    <th>Naam</th>
    <th>Chat ID</th>
    <th>Status</th>
    <th>Rol</th>
    <th>Login</th>
    <th>Actie</th>
</tr>
<?php foreach ($gebruikers as $g): ?>
<tr>
    <td><?= htmlspecialchars($g['naam']) ?></td>
    <td><?= htmlspecialchars($g['chat_id']) ?></td>
    <td><?= $g['goedgekeurd'] ? '✅ Goedgekeurd' : '⛔ Niet' ?></td>
    <td><?= rolBadge($g['rol']) ?></td>
    <td>
        <?= $g['laatste_login'] ?? '–' ?><br>
        <small style="color:#666;"><?= $g['laatste_ip'] ?></small>
    </td>
    <td>
        <?php if ($rol === 'superadmin' && $g['chat_id'] !== $_SESSION['user_chat_id']): ?>
            <?php if (!$g['goedgekeurd']): ?>
                <a href="?tab=gebruikers&approve=<?= $g['chat_id'] ?>">✔ Goedkeuren</a>
            <?php else: ?>
               <a href="?tab=gebruikers&revoke=<?= $g['chat_id'] ?>">🗙 Intrekken</a>
            <?php endif; ?>
            <?php if ($g['rol'] === 'admin'): ?>
                <a href="?tab=gebruikers&demote=<?= $g['chat_id'] ?>">⬇ Verwijder admin</a>
            <?php else: ?>
                <a href="?tab=gebruikers&promote=<?= $g['chat_id'] ?>">⬆ Maak admin</a>
            <?php endif; ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
