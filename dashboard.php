<?php
session_start();
require 'config/database.php';
require_once 'includes/helpers.php';

$chat_id = $_SESSION['user_chat_id'] ?? null;
if (!$chat_id) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT naam, rol FROM notificatie_ontvangers WHERE chat_id = ?");
$stmt->execute([$chat_id]);
$user = $stmt->fetch();

//if (!$user || !in_array($user['rol'], ['admin', 'superadmin'])) {
//    exit("⛔ Geen toegang tot het dashboard.");
//}

$rol = $user['rol'];
$naam = $user['naam'];

// Statistieken ophalen
$totaal_berichten = $pdo->query("SELECT COUNT(*) FROM berichten")->fetchColumn();
$te_publiceren = $pdo->query("SELECT COUNT(*) FROM berichten WHERE publiceerbaar = 1")->fetchColumn();
$aantal_gebruikers = $pdo->query("SELECT COUNT(*) FROM notificatie_ontvangers")->fetchColumn();
$aantal_kanalen = $pdo->query("SELECT COUNT(*) FROM telegram_kanalen")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>📊 Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<h2>📊 Welkom terug, <?= htmlspecialchars($naam) ?>!</h2>

<div class="nav-tabs">
    <a href="dashboard.php" class="active">🏠 Dashboard</a>
    <a href="beheer.php?tab=berichten">🛠️ Beheer</a>
    <a href="publish.php">📢 Publiceer</a>
    <a href="logout.php">🔓 Uitloggen</a>
</div>

<div style="margin-top: 30px; display: flex; flex-wrap: wrap; gap: 20px;">

    <div style="flex:1; min-width: 220px; background-color:#132b50; padding: 20px; border-radius: 8px;">
        <h3>📥 Berichten</h3>
        <p>Totaal ontvangen: <strong><?= $totaal_berichten ?></strong></p>
        <p>Te publiceren: <strong><?= $te_publiceren ?></strong></p>
    </div>
<?php if (in_array($user['rol'], ['admin', 'superadmin'])){ ?>
    <div style="flex:1; min-width: 220px; background-color:#132b50; padding: 20px; border-radius: 8px;">
        <h3>👥 Gebruikers</h3>
        <p>Aantal geregistreerd: <strong><?= $aantal_gebruikers ?></strong></p>
    </div>

    <div style="flex:1; min-width: 220px; background-color:#132b50; padding: 20px; border-radius: 8px;">
        <h3>📡 Kanalen</h3>
        <p>Aantal actief: <strong><?= $aantal_kanalen ?></strong></p>
    </div>
<?php } ?>
</div>

    <hr>
    <h3>📜 Laatste 5 berichten (preview)</h3>
    <table>
        <tr>
            <th>Van</th>
            <th>Bericht</th>
            <th>Ontvangen op</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT from_name, message_text, ontvangen_op FROM berichten ORDER BY ontvangen_op DESC LIMIT 5");
        foreach ($stmt as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['from_name']) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($row['message_text'], 0, 60, '...')) ?></td>
            <td><?= htmlspecialchars($row['ontvangen_op']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>

