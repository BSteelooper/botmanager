<?php
session_start();
require 'config/database.php';
require_once 'includes/helpers.php';

$chat_id = $_SESSION['user_chat_id'] ?? null;
if (!$chat_id) { header("Location: login.php"); exit; }

$stmt = $pdo->prepare("SELECT rol FROM notificatie_ontvangers WHERE chat_id = ?");
$stmt->execute([$chat_id]);
$rol = $stmt->fetchColumn();

//if (!in_array($rol, ['admin', 'superadmin'])) {
//    exit("⛔ Geen publicatierechten");
//}

$kanalen = $pdo->query("SELECT * FROM telegram_kanalen WHERE actief = 1")->fetchAll();
$berichten = $pdo->query("
    SELECT * FROM berichten 
    WHERE publiceerbaar = 1 
      AND id NOT IN (SELECT bericht_id FROM publicatie_log)
    ORDER BY ontvangen_op DESC
")->fetchAll();

$success = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bericht_id'], $_POST['kanaal_id'])) {
    $bericht_id = (int) $_POST['bericht_id'];
    $kanaal_id = (int) $_POST['kanaal_id'];

    // Vier-ogenprincipe
    $stmt = $pdo->prepare("SELECT publiceerbaar_gemarkeerd_door_chat_id FROM berichten WHERE id = ?");
    $stmt->execute([$bericht_id]);
    $markeerder = $stmt->fetchColumn();
    if ($chat_id == $markeerder) {
        exit("⛔ Je kunt geen bericht publiceren dat je zelf hebt goedgekeurd.");
    }

    $stmt = $pdo->prepare("SELECT message_text FROM berichten WHERE id = ?");
    $stmt->execute([$bericht_id]);
    $tekst = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT chat_id FROM telegram_kanalen WHERE id = ?");
    $stmt->execute([$kanaal_id]);
    $kanaal_chat_id = $stmt->fetchColumn();

    $botToken = getBotToken('publish_bot');

    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $params = [
        'chat_id' => $kanaal_chat_id,
        'text' => $tekst
    ];
    file_get_contents($url . '?' . http_build_query($params));

    // Loggen
    $pdo->prepare("INSERT INTO publicatie_log (bericht_id, kanaal_id, verzonden_door_chat_id, verzonden_door_naam)
        VALUES (?, ?, ?, ?)")->execute([
        $bericht_id, $kanaal_id, $chat_id, 'Jij'
    ]);

    logActie('publicatie', "Bericht #$bericht_id gepubliceerd via publish.php", $chat_id);
    $success = "✅ Bericht gepubliceerd naar kanaal!";
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>📢 Publiceer</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<h2>📢 Publiceer goedgekeurde berichten</h2>

<div class="nav-tabs">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="beheer.php?tab=berichten">🛠️ Beheer</a>    
    <a href="publish.php" class="active">📢 Publiceer</a>
    <a href="logout.php">🔓 Uitloggen</a>
</div>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" style="margin-top: 30px;">
    <label>Kies een bericht:</label>
    <select name="bericht_id" required>
        <?php foreach ($berichten as $b): ?>
            <option value="<?= $b['id'] ?>">
                <?= htmlspecialchars(mb_strimwidth($b['message_text'], 0, 60, '...')) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Kies kanaal:</label>
    <select name="kanaal_id" required>
        <?php foreach ($kanalen as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['naam']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">🚀 Publiceer</button>
</form>

<?php if (!$berichten): ?>
    <p style="margin-top: 40px; color: gray;">ℹ️ Geen berichten klaar voor publicatie.</p>
<?php endif; ?>

</body>
</html>

