<?php
if (!in_array($rol, ['admin', 'superadmin'])) {
    exit("⛔ Geen toegang tot botbeheer");
}

// Toevoegen of bijwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uniek_sleutel'])) {
    $naam = trim($_POST['naam']);
    $sleutel = trim($_POST['uniek_sleutel']);

    if ($rol === 'superadmin') {
        $token = trim($_POST['token']);
        $stmt = $pdo->prepare("INSERT INTO telegram_bots (naam, token, uniek_sleutel)
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE naam = VALUES(naam), token = VALUES(token), actief = 1");
        $stmt->execute([$naam, $token, $sleutel]);
    } else {
        $stmt = $pdo->prepare("UPDATE telegram_bots SET naam = ? WHERE uniek_sleutel = ?");
        $stmt->execute([$naam, $sleutel]);
    }

    echo "<div class='notice-success'>✅ Bot opgeslagen!</div>";
}

// Activeren/deactiveren
if (isset($_GET['deactiveer'])) {
    $pdo->prepare("UPDATE telegram_bots SET actief = 0 WHERE id = ?")->execute([$_GET['deactiveer']]);
}
if (isset($_GET['activeer'])) {
    $pdo->prepare("UPDATE telegram_bots SET actief = 1 WHERE id = ?")->execute([$_GET['activeer']]);
}

// Ping-bot (alleen voor superadmin)
$ping_result = null;
if (isset($_GET['ping']) && $rol === 'superadmin') {
    $stmt = $pdo->prepare("SELECT token FROM telegram_bots WHERE id = ?");
    $stmt->execute([$_GET['ping']]);
    $token = $stmt->fetchColumn();

    if ($token) {
        $response = @file_get_contents("https://api.telegram.org/bot$token/getMe");
        if ($response && strpos($response, '"ok":true') !== false) {
            $ping_result = "✅ Ping gelukt: bot is actief.";
        } else {
            $ping_result = "❌ Ping mislukt: kon geen geldig antwoord krijgen.";
        }
    } else {
        $ping_result = "⚠️ Geen token gevonden.";
    }
}

$bots = $pdo->query("SELECT * FROM telegram_bots ORDER BY toegevoegd_op DESC")->fetchAll();
?>

<h3>🤖 Telegram Bots beheren</h3>

<?php if ($ping_result): ?>
    <div class="notice-success"><?= htmlspecialchars($ping_result) ?></div>
<?php endif; ?>

<form method="post">
    <label>🔤 Naam (bijv. Adminbot):</label>
    <input type="text" name="naam" required>
    <label>🔑 Unieke sleutel (bv. admin_bot):</label>
    <input type="text" name="uniek_sleutel" placeholder="uniek_sleutel" required>

    <?php if ($rol === 'superadmin'): ?>
        <label>🔐 Bot token:</label>
        <input type="password" name="token" placeholder="bot123:ABC-xyz..." required>
    <?php else: ?>
        <p style="color:gray; font-size:0.9em;">🔒 Alleen superadmins kunnen tokens beheren.</p>
    <?php endif; ?>

    <button type="submit">💾 Opslaan</button>
</form>

<hr>

<table>
    <tr>
        <th>Naam</th>
        <th>Sleutel</th>
        <th>Token</th>
        <th>Status</th>
        <th>Acties</th>
    </tr>
    <?php foreach ($bots as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['naam']) ?></td>
        <td><code><?= htmlspecialchars($b['uniek_sleutel']) ?></code></td>
        <td>
            <?php if ($rol === 'superadmin'): ?>
                <code><?= htmlspecialchars($b['token']) ?></code>
            <?php else: ?>
                <code>••••••••••••••</code>
            <?php endif; ?>
        </td>
        <td><?= $b['actief'] ? '✅ Actief' : '⛔ Inactief' ?></td>
        <td>
            <?php if ($b['actief']): ?>
                <a href="?tab=bots&deactiveer=<?= $b['id'] ?>">⛔ Deactiveren</a>
            <?php else: ?>
                <a href="?tab=bots&activeer=<?= $b['id'] ?>">✅ Activeren</a>
            <?php endif; ?>

            <?php if ($rol === 'superadmin'): ?>
                &nbsp;|&nbsp;
                <a href="?tab=bots&ping=<?= $b['id'] ?>">📡 Ping</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
