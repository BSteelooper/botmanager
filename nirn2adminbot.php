<?php
require 'config/database.php';
require_once 'includes/helpers.php';

$data = json_decode(file_get_contents("php://input"), true);
$chat_id = $data['message']['chat']['id'] ?? null;
$naam = $data['message']['from']['first_name'] ?? 'Gebruiker';
$gebruikersnaam = $data['message']['from']['username'] ?? null;

if ($chat_id) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO notificatie_ontvangers (chat_id, naam, gebruikersnaam) VALUES (?, ?, ?)");
    $stmt->execute([$chat_id, $naam, $gebruikersnaam]);
    $adminBot = getBotToken('admin_bot');
    
    file_get_contents("https://api.telegram.org/bot$adminBot/sendMessage?chat_id={$chat_id}&text=" . urlencode("📬 Je bent aangemeld. Een admin moet je nog goedkeuren."));
}

