<?php
if ($rol !== 'superadmin') {
    exit("⛔ Alleen toegankelijk voor superadmins.");
}

$stmt = $pdo->query("
    SELECT log.id, log.type, log.omschrijving, log.aanmaker_chat_id, log.aangemaakt_op, ontv.naam
    FROM log_acties AS log
    LEFT JOIN notificatie_ontvangers AS ontv ON ontv.chat_id = log.aanmaker_chat_id
    ORDER BY log.aangemaakt_op DESC
    LIMIT 100
");

$logs = $stmt->fetchAll();
?>

<h3>📜 Laatste systeemacties</h3>

<?php if (!$logs): ?>
    <p>ℹ️ Nog geen logs geregistreerd.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Type</th>
            <th>Omschrijving</th>
            <th>Gebruiker</th>
            <th>Datum/tijd</th>
        </tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><code><?= htmlspecialchars($log['type']) ?></code></td>
            <td><?= htmlspecialchars($log['omschrijving']) ?></td>
            <td><?= htmlspecialchars($log['naam'] ?? '🤖 Systeem') ?></td>
            <td><?= date('Y-m-d H:i', strtotime($log['aangemaakt_op'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

