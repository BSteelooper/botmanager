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
//    exit("⛔ Geen toegang tot de beheeromgeving.");
//}

$rol = $user['rol'];
$naam = $user['naam'];
$tab = $_GET['tab'] ?? 'gebruikers';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>🛠️ Beheeromgeving</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<h2>🛠️ Welkom in de beheeromgeving, <?= htmlspecialchars($naam) ?>!</h2>

<div class="nav-tabs">
    <a href="dashboard.php">Dashboard</a>
    <?php if ($user && in_array($user['rol'], ['admin', 'superadmin'])) {  ?><a href="?tab=gebruikers" class="<?= $tab === 'gebruikers' ? 'active' : '' ?>">👥 Gebruikers</a><?php } ?>
    <a href="?tab=berichten" class="<?= $tab === 'berichten' ? 'active' : '' ?>">📬 Berichten</a>
    <?php if ($user && in_array($user['rol'], ['admin', 'superadmin'])) {  ?><a href="?tab=categorieen" class="<?= $tab === 'categorieen' ? 'active' : '' ?>">🏷️ Categorieën</a><?php } ?>
    <?php if ($user && in_array($user['rol'], ['admin', 'superadmin'])) {  ?><a href="?tab=kanalen" class="<?= $tab === 'kanalen' ? 'active' : '' ?>">📡 Kanalen</a><?php } ?>
    <?php if ($rol === 'admin'): ?>
        <a href="?tab=publicaties" class="<?= $tab === 'publicaties' ? 'active' : '' ?>">📢 Publicaties</a>
        <a href="?tab=bots" class="<?= $tab === 'bots' ? 'active' : '' ?>">🤖 Bots</a>
    <?php endif; ?>
    <?php if ($rol === 'superadmin'): ?>
        <a href="?tab=publicaties" class="<?= $tab === 'publicaties' ? 'active' : '' ?>">📢 Publicaties</a>
        <a href="?tab=logs" class="<?= $tab === 'logs' ? 'active' : '' ?>">📜 Logs</a>
        <a href="?tab=instellingen" class="<?= $tab === 'instellingen' ? 'active' : '' ?>">⚙️ Instellingen</a>
        <a href="?tab=bots" class="<?= $tab === 'bots' ? 'active' : '' ?>">🤖 Bots</a>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php
    switch ($tab) {
	case 'berichten':
    	    include 'modules/beheer_berichten.php';
            break;
        case 'gebruikers':
            include 'modules/beheer_gebruikers.php';
            break;
        case 'categorieen':
            include 'modules/beheer_categorieen.php';
            break;
        case 'kanalen':
            include 'modules/beheer_kanalen.php';
            break;
        case 'publicaties':
            if ($rol === 'superadmin') {
                include 'modules/beheer_publicaties.php';
            }
            break;
        case 'logs':
            if ($rol === 'superadmin') {
                include 'modules/beheer_logs.php';
            }
            break;
        case 'instellingen':
            if ($rol === 'superadmin') {
                include 'modules/beheer_instellingen.php';
            }
            break;
        case 'bots':
            include 'modules/beheer_bots.php';
            break;
	default:
            echo "<p>❓ Ongeldige tab gekozen.</p>";
    }
    ?>
</div>

</body>
</html>

