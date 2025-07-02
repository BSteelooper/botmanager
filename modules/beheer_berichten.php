<?php
//if (!in_array($rol, ['admin', 'superadmin'])) {
//    exit("⛔ Alleen voor (super)admins.");
//}

$bericht_id = $_POST['antwoord_bericht_id'] ?? null;
$antwoord = trim($_POST['antwoord_tekst'] ?? '');
$beantwoord = false;
$categorie_id = $_POST['categorie_id'] ?? null;

if ($bericht_id && $antwoord !== '') {
    $stmt = $pdo->prepare("SELECT chat_id FROM berichten WHERE id = ?");
    $stmt->execute([$bericht_id]);
    $ontvanger_chat_id = $stmt->fetchColumn();

    if ($ontvanger_chat_id) {
        $botToken = getBotToken('notificatie_bot');
        $url = "https://api.telegram.org/bot$botToken/sendMessage";
        $params = [
            'chat_id' => $ontvanger_chat_id,
            'text' => $antwoord
        ];
        file_get_contents($url . '?' . http_build_query($params));

	if ($antwoord==''){
		//change category
		    $pdo->prepare("UPDATE berichten SET categorie_id = ? WHERE id = ? ")->execute([$categorie_id, $bericht_id]);
	} else {
		$pdo->prepare("UPDATE berichten SET beantwoord_door_chat_id = ?, beantwoord_op = NOW(), antwoord = ? WHERE id = ?")
            ->execute([$chat_id, $antwoord,  $bericht_id]);

        	logActie('antwoord', "Bericht #$bericht_id beantwoord via CMS", $chat_id);
		$beantwoord = true;
	}
    }
}

// Inladen berichten
$berichten = $pdo->query("
    SELECT b.*, o.naam
    FROM berichten b
    LEFT JOIN notificatie_ontvangers o ON o.chat_id = b.chat_id
    ORDER BY ontvangen_op DESC
")->fetchAll();

$categorieen = $pdo->query("SELECT * FROM categorieen WHERE actief = 1 ORDER BY naam")->fetchAll(PDO::FETCH_ASSOC);

?>

<h3>📥 Ingekomen berichten beheren</h3>

<?php if ($beantwoord): ?>
    <div class="alert success">✅ Antwoord verzonden!</div>
<?php endif; ?>

<table>
    <tr>
        <th>Van</th>
        <th>Bericht</th>
        <th>Ontvangen op</th>
        <th>Antwoord</th>
    </tr>
    <?php foreach ($berichten as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['naam'] ?? '🤖 onbekend') ?><br><small>@<?= htmlspecialchars($b['gebruikersnaam']) ?></small></td>
        <td style="max-width:300px;"><?= nl2br(htmlspecialchars($b['message_text'])) ?></td>
        <td><?= htmlspecialchars($b['ontvangen_op']) ?></td>
        <td>
	    <?php if ($b['beantwoord_op']): ?>
		<?php echo $b['antwoord'];?><br/>
<small><em>Beantwoord op <?= htmlspecialchars($b['beantwoord_op']) ?> door: <?= htmlspecialchars($b['naam']) ?></em></small>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="antwoord_bericht_id" value="<?= $b['id'] ?>">
                    <textarea name="antwoord_tekst" rows="3" placeholder="Antwoord..."></textarea><br>
                    <button type="submit">📤 Verstuur</button>
                </form>
            <?php endif; ?>
        </td>
	<td>
		<form method="post">
			<label>Categorie:</label>
		<select name="categorie_id">
	        <option value="">—</option>
        	<?php foreach ($categorieen as $c): ?>
            		<option value="<?= $c['id'] ?>" <?= $b['categorie_id'] == $c['id'] ? 'selected' : '' ?>>
                	<?= htmlspecialchars($c['naam']) ?>
            	</option>
        	<?php endforeach; ?>
		</select><br>
		<button type="submit">Opslaan</button>
		</form>
	</td>
    </tr>
    <?php endforeach; ?>
</table>

