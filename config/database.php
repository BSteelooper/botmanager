<?php
$pdo = new PDO('mysql:host=localhost;dbname=c54nirn2bot', 'c54nirn2bot', 'Uzb2#Nz4LEi');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 🌐 Instelling ophalen
function getInstelling($sleutel) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT waarde FROM systeeminstellingen WHERE sleutel = ?");
    $stmt->execute([$sleutel]);
    return $stmt->fetchColumn() ?? '';
}

// 🔄 Instelling opslaan
function setInstelling($sleutel, $waarde) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO systeeminstellingen (sleutel, waarde)
        VALUES (?, ?) ON DUPLICATE KEY UPDATE waarde = VALUES(waarde)");
    $stmt->execute([$sleutel, $waarde]);
}

