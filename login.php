<?php
session_start();
require 'config/database.php';
require_once 'includes/helpers.php';

$token = $_GET['token'] ?? '';
$chat_id = null;

if ($token) {
    // Token valideren én ophalen
    $stmt = $pdo->prepare("SELECT gebruiker_chat_id, geldig_tot FROM login_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if ($row) {
        if (strtotime($row['geldig_tot']) > time()) {
            // ✅ Token is geldig — inloggen
            $chat_id = $row['gebruiker_chat_id'];

            // Token verwijderen (one-time use)
//            $pdo->prepare("DELETE FROM login_tokens WHERE token = ?")->execute([$token]);

            // Check of gebruiker goedgekeurd is
            $stmt = $pdo->prepare("SELECT goedgekeurd FROM notificatie_ontvangers WHERE chat_id = ?");
            $stmt->execute([$chat_id]);
            $goedgekeurd = $stmt->fetchColumn();

            if ($goedgekeurd) {
		    $ip = $_SERVER['REMOTE_ADDR'] ?? 'onbekend';
		    $pdo->prepare("UPDATE notificatie_ontvangers SET laatste_ip = ?, laatste_login = Now()  WHERE chat_id = ?")->execute([$ip, $chat_id]);
		    $_SESSION['user_chat_id'] = $chat_id;
                header("Location: dashboard.php");
                exit;
            } else {
                $foutmelding = "⛔ Je bent nog niet goedgekeurd door een admin.";
            }
        } else {
            $pdo->prepare("DELETE FROM login_tokens WHERE token = ?")->execute([$token]);
            $foutmelding = "⌛ Deze loginlink is verlopen.";
        }
    } else {
        $foutmelding = "❌ Ongeldig of al gebruikt token.";
    }
} else {
    $foutmelding = "⚠️ Geen login-token meegegeven.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🔐 Inloggen</title>
</head>
<body>
    <h2>🔐 Login</h2>
    <?php if (isset($foutmelding)): ?>
        <div style="color: darkred; font-weight: bold;"><?= htmlspecialchars($foutmelding) ?></div>
    <?php endif; ?>
    <p><a href="login_start.php">🔄 Probeer opnieuw</a></p>
</body>
</html>

