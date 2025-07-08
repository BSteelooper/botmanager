<?php
//if (!in_array($rol, ['admin', 'superadmin'])) {
//    exit("⛔ Alleen voor (super)admins.");
//}

// Categorieën ophalen
$categorieen = $pdo->query("SELECT * FROM categorieen WHERE actief = 1 ORDER BY naam")->fetchAll(PDO::FETCH_ASSOC);

// Categorie wijzigen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_categorie_id'])) {
    $bericht_id = (int) $_POST['set_categorie_id'];
    $categorie_id = $_POST['categorie_id'] ?: null;

    $pdo->prepare("UPDATE berichten SET categorie_id = ? WHERE id = ?")
        ->execute([$categorie_id, $bericht_id]);

    logActie('categorie', "Categorie aangepast voor bericht #$bericht_id", $chat_id);
    echo '<div class="alert success">🏷️ Categorie bijgewerkt.</div>';
}

// Antwoord verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['antwoord_bericht_id'])) {
    $bericht_id = (int) $_POST['antwoord_bericht_id'];
    $antwoord = trim($_POST['antwoord_tekst'] ?? '');
    $categorie_id = $_POST['categorie_id'] ?? null;

    $stmt = $pdo->prepare("SELECT chat_id FROM berichten WHERE id = ?");
    $stmt->execute([$bericht_id]);
    $ontvanger_chat_id = $stmt->fetchColumn();

    if ($ontvanger_chat_id && $antwoord !== '') {
        $botToken = getBotToken('admin_bot');
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query([
            'chat_id' => $ontvanger_chat_id,
            'text' => $antwoord
        ]));

        $pdo->prepare("UPDATE berichten SET beantwoord_door_chat_id = ?, beantwoord_op = NOW(), categorie_id = ? WHERE id = ?")
            ->execute([$chat_id, $categorie_id ?: null, $bericht_id]);

        logActie('antwoord', "Bericht #$bericht_id beantwoord via CMS", $chat_id);
        echo '<div class="alert success">✅ Antwoord verzonden!</div>';
    }
}

// Berichten ophalen en groeperen
$stmt = $pdo->query("SELECT * FROM berichten ORDER BY media_group_id, ontvangen_op DESC");
$alle_berichten = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groepen = [];
foreach ($alle_berichten as $b) {
    $groep = $b['media_group_id'] ?? 'single_' . $b['id'];
    $groepen[$groep][] = $b;
}
?>

<h3>📥 Ingekomen berichten</h3>

<table>
    <tr>
        <th>Van</th>
        <th>Bericht + Afbeeldingen</th>
        <th>Ontvangen op</th>
        <th>Categorie</th>
        <th>Actie</th>
    </tr>
    <?php foreach ($groepen as $groep): 
    // Zoek eerste item met echte tekst (geen [foto])
$eerste = null;
foreach ($groep as $item) {
    if (!in_array(trim($item['message_text']), ['', '[foto]'])) {
        $eerste = $item;
        break;
    }
}
if (!$eerste) {
    $eerste = $groep[0]; // fallback
}
?>
    <tr>
        <td>
    		<?= htmlspecialchars($eerste['from_name'] ?? '🤖 onbekend') ?><br>
    		<small>@<?= htmlspecialchars($eerste['gebruikersnaam']) ?></small>

        	<div style="margin-top:4px; color: #888;">
			<?php if ($eerste['forwarded_from_user']): ?>
			    🔁 Van gebruiker: <strong>@<?= htmlspecialchars($eerste['forwarded_from_user']) ?></strong><br>
			<?php elseif ($eerste['forwarded_from_chat']): ?>
			    🔁 Van kanaal: <strong><?= htmlspecialchars($eerste['forwarded_from_chat']) ?></strong><br>
			<?php elseif ($eerste['forward_sender_name']): ?>
			    🔁 Van groep: <strong><?= htmlspecialchars($eerste['forward_sender_name']) ?></strong><br>
			<?php endif; ?>
		</div>
	</td>
	<td style="max-width: 400px;">
            <?= nl2br(htmlspecialchars($eerste['message_text'])) ?>

            <?php if ($eerste['file_id']): ?>
                <div class="galerij" style="margin-top: 8px;">
                    <?php foreach ($groep as $foto): ?>
                        <?php if ($foto['file_id']): ?>
                        <img src="<?= htmlspecialchars($foto['bestand_pad']) ?>"
			     data-full="<?= htmlspecialchars($foto['bestand_pad']) ?>"
			     style="max-width: 100px; margin: 4px; border-radius: 4px; cursor: zoom-in;">
			<?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($eerste['ontvangen_op']) ?></td>
        <td>
            <form method="post" style="margin:0;">
                <input type="hidden" name="set_categorie_id" value="<?= $eerste['id'] ?>">
                <select name="categorie_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    <?php foreach ($categorieen as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $eerste['categorie_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['naam']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </td>
        <td>
            <?php if ($eerste['beantwoord_op']): ?>
                ✅ Beantwoord op<br><small><?= htmlspecialchars($eerste['beantwoord_op']) ?></small>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="antwoord_bericht_id" value="<?= $eerste['id'] ?>">
                    <textarea name="antwoord_tekst" rows="3" placeholder="Antwoord..." required></textarea><br>
                    <button type="submit">📤 Verstuur</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Lightbox popup -->
<div id="popup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#000a; justify-content:center; align-items:center; z-index:999;">
    <img id="popup-img" src="" style="max-width:90%; max-height:90%; border: 4px solid white; border-radius: 8px;">
</div>

<script>
document.querySelectorAll('.galerij img').forEach(img => {
    img.addEventListener('click', e => {
        e.preventDefault();
        document.getElementById('popup-img').src = img.dataset.full;
        document.getElementById('popup').style.display = 'flex';
    });
});
document.getElementById('popup').addEventListener('click', () => {
    document.getElementById('popup').style.display = 'none';
});
</script>

