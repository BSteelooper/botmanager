<?php
if (!in_array($rol, ['admin', 'superadmin'])) {
    exit("⛔ Geen toegang tot publicatiebeheer");
}

$query = "
SELECT pl.*, 
       b.message_text, 
       b.from_name, 
       b.ontvangen_op AS bericht_datum,
       k.naam AS kanaal_naam,
       k.chat_id AS kanaal_chat_id
FROM publicatie_log pl
LEFT JOIN berichten b ON pl.bericht_id = b.id
LEFT JOIN telegram_kanalen k ON pl.kanaal_id = k.id
ORDER BY pl.verzonden_op DESC
LIMIT 100
";
$logs = $pdo->query($query)->fetchAll();
?>

<h3>📢 Publicatielogboek</h3>

<table border="1" cellpadding="6" cellspacing="0" style="font-size: 0.9em;">
<tr>
    <th>Datum</th>
    <th>Kanaal</th>
    <th>Gepubliceerd door</th>
    <th>Originele afzender</th>
    <th>Berichtfragment</th>
</tr>
<?php foreach ($logs as $log): ?>
<tr>
    <td><?= $log['verzonden_op'] ?></td>
    <td>
        <?= htmlspecialchars($log['kanaal_naam']) ?><br>
        <small style="color:#666;"><?= htmlspecialchars($log['kanaal_chat_id']) ?></small>
    </td>
    <td><?= htmlspecialchars($log['verzonden_door_naam']) ?></td>
    <td><?= htmlspecialchars($log['from_name']) ?><br>
        <small><?= $log['bericht_datum'] ?></small>
    </td>
    <td>
        <?= nl2br(htmlspecialchars(mb_strimwidth($log['message_text'], 0, 200, '…'))) ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
