<?php
session_start();
require 'config/database.php';
require_once 'includes/helpers.php';

$login_url = null;
$foutmelding = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['gebruikersnaam'])) {
    $gebruikersnaam = trim(ltrim($_POST['gebruikersnaam'], '@'));

    // Opzoeken in database
    $stmt = $pdo->prepare("SELECT chat_id, goedgekeurd FROM notificatie_ontvangers WHERE gebruikersnaam = ?");
    $stmt->execute([$gebruikersnaam]);
    $gebruiker = $stmt->fetch();
    $adminBot = getBotToken('admin_bot');

    if (!$gebruiker) {
        $foutmelding = "❌ Deze gebruikersnaam is niet bekend in het systeem.";
    } elseif (!$gebruiker['goedgekeurd']) {
        $foutmelding = "🔒 Je bent nog niet goedgekeurd door een admin.";
    } else {
        $chat_id = $gebruiker['chat_id'];
        $token = bin2hex(random_bytes(32));
        $duur = (int) getInstelling("token_verlooptijd") ?: 600;

        // One-time token opslaan
        $stmt = $pdo->prepare("INSERT INTO login_tokens (gebruiker_chat_id, token, geldig_tot)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
        $stmt->execute([$chat_id, $token, $duur]);

	$login_url = 'https://' . $_SERVER['HTTP_HOST'] . '/telegram-platform/login.php?token=' . $token;
        $bericht = "🔐 Direct inloggen:\n$login_url";

	file_get_contents("https://api.telegram.org/bot$adminBot/sendMessage?chat_id={$gebruiker['chat_id']}&text=" . urlencode($bericht));
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🔐 Login Start</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; color: #e2e8f0; background-color: #0b1d3a; }
        input { width: 100%; padding: 10px; margin: 10px 0; background-color: #1b2c48; border: 1px solid #425b8a; color: #e2e8f0; border-radius: 4px; }
        button { padding: 10px 20px; background: #228be6; color: white; border: none; border-radius: 4px; }
        button:hover { background: #1c7ed6; cursor: pointer; }
        .alert { padding: 10px; margin-top: 20px; border-radius: 4px; }
        .error { background: #472929; color: #ffbaba; }
        .success { background: #1d4431; color: #a3f7bf; }
    </style>
</head>
<body>

<h2>🔐 Inloggen met Telegram-gebruikersnaam</h2>

<form method="post">
    <label>Jouw Telegram gebruikersnaam:</label><br>
    <input type="text" name="gebruikersnaam" placeholder="@jouwgebruikersnaam" required>
    <button type="submit">🪄 Genereer loginlink</button>
</form>

<?php if ($foutmelding): ?>
    <div class="alert error"><?= htmlspecialchars($foutmelding) ?></div>
<?php elseif ($login_url): ?>
    <div class="alert success">
        ✅ Loginlink aangemaakt (geldig voor <?= htmlspecialchars($duur ?? 600) ?> sec):<br>
<?php /*        <a href="<?= htmlspecialchars($login_url) ?>"><?= htmlspecialchars($login_url) ?></a>
 */ ?>
    </div>
<?php endif; ?>

<p style="font-size: 0.9em; color: #aaa;">
    ℹ️ Je gebruikersnaam is wat je op Telegram ziet als <strong>@voorbeeldnaam</strong>. Je moet al via de bot zijn aangemeld.
</p>

</body>
</html>

