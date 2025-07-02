<?php
if (!in_array($rol, ['admin', 'superadmin'])) {
    exit("⛔ Geen toegang tot kanaalbeheer");
}

// Toevoegen van nieuw kanaal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['naam'], $_POST['chat_id'])) {
    $naam = trim($_POST['naam']);
    $chat_id = trim($_POST['chat_id']);

    $stmt = $pdo->prepare("INSERT INTO telegram_kanalen (naam, chat_id, actief) VALUES (?, ?, 1)");
    $stmt->execute([$naam, $chat_id]);
}

// Kanaal activeren/deactiveren
if (isset($_GET['deactiveer'])) {
    $pdo->prepare("UPDATE telegram_kanalen SET actief = 0 WHERE id = ?")->execute([$_GET['deactiveer']]);
}
if (isset($_GET['activeer'])) {
    $pdo->prepare("UPDATE telegram_kanalen SET actief = 1 WHERE id = ?")->execute([$_GET['activeer']]);
}

$kanalen = $pdo->query("SELECT * FROM telegram_kanalen ORDER BY actief DESC, naam ASC")->fetchAll();
?>

<h3>📡 Telegramkanalen beheren</h3>

<form method="post">
    Kanaalnaam: 
    <input type="text" name="naam" placeholder="Bijv. Algemeen nieuws" required>
    Chat ID of @kanaal: 
    <input type="text" name="chat_id" placeholder="@mijnkanaal of -100..." required>
    <button type="submit">➕ Toevoegen</button>
</form>

<hr>

<table border="1" cellpadding="6" cellspacing="0">
<tr>
    <th>Naam</th>
    <th>Chat ID</th>
    <th>Status</th>
    <th>Actie</th>
</tr>
<?php foreach ($kanalen as $k): ?>
<tr>
    <td><?= htmlspecialchars($k['naam']) ?></td>
    <td><?= htmlspecialchars($k['chat_id']) ?></td>
    <td><?= $k['actief'] ? '✅ Actief' : '⛔ Inactief' ?></td>
    <td>
        <?php if ($k['actief']): ?>
            <a href="?tab=kanalen&deactiveer=<?= $k['id'] ?>">⛔ Deactiveren</a>
        <?php else: ?>
            <a href="?tab=kanalen&activeer=<?= $k['id'] ?>">✅ Activeren</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
