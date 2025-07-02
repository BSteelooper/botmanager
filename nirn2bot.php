<?php
require 'config/database.php';
require_once 'includes/helpers.php';

// Telegram update binnenhalen
$data = json_decode(file_get_contents("php://input"), true);

$chat_id = $data['message']['chat']['id'] ?? null;
$from_name = $data['message']['from']['first_name'] ?? 'Onbekend';
$gebruikersnaam = $data['message']['from']['username'] ?? null;
$text = $data['message']['text'] ?? '';

if ($chat_id && $text) {
    // Bericht opslaan
    if (trim($text) === '/start') {
        // Negeren en eventueel loggen
        logActie('webhook', "🛑 Start-bericht genegeerd van chat_id $chat_id");
        exit; // stopt verdere verwerking
    }

    // Bericht opslaan (alleen als het géén /start is)
    $stmt = $pdo->prepare("INSERT INTO berichten (chat_id, from_name, gebruikersnaam, message_text)
        VALUES (?, ?, ?, ?)");
    $stmt->execute([$chat_id, $from_name, $gebruikersnaam, $text]);

    // Optioneel: stuur notificatie naar alle goedgekeurde admins
    $notificatie_token = getBotToken('admin_bot');
    $admins = $pdo->query("SELECT chat_id FROM notificatie_ontvangers WHERE goedgekeurd = 1")->fetchAll();
    $adminBot = getBotToken('admin_bot');
    $duur = (int) getInstelling("token_verlooptijd") ?: 600;

    foreach ($admins as $admin) {
       $login_token = bin2hex(random_bytes(32));

        // Token opslaan
        $stmt = $pdo->prepare("INSERT INTO login_tokens (gebruiker_chat_id, token, geldig_tot)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
        $stmt->execute([$admin['chat_id'], $login_token, $duur]);

        $login_link = 'https://' . $_SERVER['HTTP_HOST'] . '/telegram-platform/login.php?token=' . $login_token;

        $bericht = "📩 Nieuw bericht van $from_name:\n$text\n\n🔐 Direct inloggen:\n$login_link";
	file_get_contents("https://api.telegram.org/bot$adminBot/sendMessage?chat_id={$admin['chat_id']}&text=" . urlencode($bericht));
    }
}

