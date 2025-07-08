<?php
require 'config/database.php';
require_once 'includes/helpers.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['message'])) {
    exit;
}

$message = $data['message'];
$chat_id = $message['chat']['id'] ?? null;
$from_name = trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''));
$gebruikersnaam = $message['from']['username'] ?? null;
$text = $message['text'] ?? $message['caption'] ?? null;
$media_group_id = $message['media_group_id'] ?? null;
$ontvangen_op = date('Y-m-d H:i:s');
$bestand_pad = null;
$file_id = null;

 forwardToTestChannel($data);

// ⛔ Negeer /start
if (isset($text) && strtolower(trim($text)) === '/start') {
    logActie('webhook', "Start-bericht genegeerd van $chat_id");
    exit;
}

// 🔁 Doorgestuurd bericht detecteren
$forwarded_from_user = $message['forward_from']['username'] ?? null;
$forwarded_from_chat = $message['forward_from_chat']['title'] ?? null;
$forward_sender_name = $message['forward_sender_name'] ?? null;

// 📸 Foto opslaan (hoogste resolutie)
if (isset($message['photo'])) {
    $foto = end($message['photo']);
    $file_id = $foto['file_id'];

    $botToken = getBotToken('notificatie_bot');
    $file_info = json_decode(file_get_contents("https://api.telegram.org/bot$botToken/getFile?file_id=$file_id"), true);
    $file_path = $file_info['result']['file_path'] ?? null;

    if ($file_path) {
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $local_filename = 'foto_' . uniqid() . '.' . $ext;
        $bestand_pad = '/uploads/' . $local_filename;
        $file_data = file_get_contents("https://api.telegram.org/file/bot$botToken/$file_path");
        file_put_contents(__DIR__ . '/../' . $bestand_pad, $file_data);
    }

    if (!$text) {
        $text = '[foto]';
    }
}

// 📥 Bericht opslaan
$stmt = $pdo->prepare("
    INSERT INTO berichten 
    (chat_id, from_name, gebruikersnaam, message_text, ontvangen_op, file_id, bestand_pad, media_group_id, forwarded_from_user, forwarded_from_chat, forward_sender_name)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $chat_id,
    $from_name,
    $gebruikersnaam,
    $text,
    $ontvangen_op,
    $file_id,
    $bestand_pad,
    $media_group_id,
    $forwarded_from_user,
    $forwarded_from_chat,
    $forward_sender_name
]);


// 🔔 Admins notificeren
$admins = $pdo->query("SELECT chat_id FROM notificatie_ontvangers WHERE rol IN ('admin','superadmin')")->fetchAll(PDO::FETCH_COLUMN);
foreach ($admins as $admin_chat_id) {

	       $login_token = bin2hex(random_bytes(32));

        // Token opslaan
        $stmt = $pdo->prepare("INSERT INTO login_tokens (gebruiker_chat_id, token, geldig_tot)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
        $stmt->execute([$admin['chat_id'], $login_token, $duur]);

        $login_link = 'https://' . $_SERVER['HTTP_HOST'] . '/telegram-platform/login.php?token=' . $login_token;


    $bericht = "📩 Nieuw bericht van $from_name";
    if ($gebruikersnaam) $bericht .= " (@$gebruikersnaam)";
    if ($isForwarded) $bericht  = "\n\n🔁 Doorgestuurd van " . ($forwarded_user ?: $forwarded_chat);

    $bericht .= ":\n\n" . $text;

    $bericht .= "\n\n" . $login_link;

    $botToken = getBotToken('admin_bot');
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query([
        'chat_id' => $admin_chat_id,
        'text' => $bericht
    ]));
}

logActie('webhook', "Bericht ontvangen van $from_name", $chat_id);

