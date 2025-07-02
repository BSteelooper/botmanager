<?php
if ($rol !== 'superadmin') {
    exit("⛔ Alleen superadmins mogen instellingen wijzigen");
}

// Opslaan
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    setInstelling("max_sessie_duur", $_POST["max_sessie_duur"]);
    setInstelling("token_verlooptijd", $_POST["token_verlooptijd"]);
    setInstelling("max_admins", $_POST["max_admins"]);
    setInstelling("standaard_categorie_kleur", $_POST["standaard_categorie_kleur"]);

    echo "<div style='color:green;'>✅ Instellingen opgeslagen!</div>";
}
?>

<h3>⚙️ Systeeminstellingen</h3>

<form method="post">
    <label>⏳ Maximale sessieduur (seconden):</label><br>
    <input type="number" name="max_sessie_duur" value="<?= htmlspecialchars(getInstelling("max_sessie_duur")) ?>"><br><br>

    <label>🔐 Token-verlooptijd (seconden):</label><br>
    <input type="number" name="token_verlooptijd" value="<?= htmlspecialchars(getInstelling("token_verlooptijd")) ?>"><br><br>

    <label>👨‍🔧 Maximaal aantal admins:</label><br>
    <input type="number" name="max_admins" value="<?= htmlspecialchars(getInstelling("max_admins")) ?>"><br><br>

    <label>🎨 Standaardkleur nieuwe categorieën:</label><br>
    <input type="color" name="standaard_categorie_kleur" value="<?= htmlspecialchars(getInstelling("standaard_categorie_kleur")) ?>"><br><br>

    <button type="submit">💾 Opslaan</button>
</form>
