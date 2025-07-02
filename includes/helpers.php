<?php
// 🏷️ Rol als visuele badge weergeven
function rolBadge($rol) {
    switch ($rol) {
        case 'superadmin':
            return '<span style="color: purple;">🛡️ Superadmin</span>';
        case 'admin':
            return '<span style="color: darkgreen;">⚙️ Admin</span>';
        default:
            return '<span style="color: gray;">👤 Gebruiker</span>';
    }
}

// 📅 Datum mooi formatten (optioneel)
function formatDatum($timestamp) {
    return date("d-m-Y H:i", strtotime($timestamp));
}

// 📜 Beperken tot max tekens (bijvoorbeeld voor berichtenfragment)
function kortFragment($tekst, $max = 100) {
    $tekst = strip_tags($tekst);
    return mb_strimwidth($tekst, 0, $max, '…');
}

function getBotToken(string $sleutel): string {
    global $pdo;
    $stmt = $pdo->prepare("SELECT token FROM telegram_bots WHERE uniek_sleutel = ? AND actief = 1");
    $stmt->execute([$sleutel]);
    return $stmt->fetchColumn() ?? '';
}


function logActie(string $type, string $omschrijving, ?int $chat_id = null): void {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO log_acties (type, omschrijving, aanmaker_chat_id) VALUES (?, ?, ?)");
    $stmt->execute([$type, $omschrijving, $chat_id]);
}

