<?php
if (!in_array($rol, ['admin', 'superadmin'])) {
    exit("⛔ Geen toegang tot categoriebeheer");
}

// Categorie opslaan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nieuwe_categorie'])) {
    $naam = trim($_POST['nieuwe_categorie']);
    $kleur = $_POST['kleur'] ?? '#cccccc';

    $stmt = $pdo->prepare("INSERT IGNORE INTO categorieen (naam, kleur) VALUES (?, ?)");
    $stmt->execute([$naam, $kleur]);
}

// Activeren/deactiveren
if (isset($_GET['deactiveer'])) {
    $pdo->prepare("UPDATE categorieen SET actief = 0 WHERE id = ?")->execute([$_GET['deactiveer']]);
}
if (isset($_GET['activeer'])) {
    $pdo->prepare("UPDATE categorieen SET actief = 1 WHERE id = ?")->execute([$_GET['activeer']]);
}

$categorieen = $pdo->query("SELECT * FROM categorieen ORDER BY actief DESC, naam ASC")->fetchAll();
?>

<h3>🏷️ Categoriebeheer</h3>

<form method="post">
    Naam categorie: 
    <input type="text" name="nieuwe_categorie" required>
    Kleur:
    <input type="color" name="kleur" value="#cccccc">
    <button type="submit">➕ Toevoegen</button>
</form>

<hr>

<table border="1" cellpadding="6" cellspacing="0">
<tr>
    <th>Naam</th>
    <th>Kleur</th>
    <th>Status</th>
    <th>Actie</th>
</tr>
<?php foreach ($categorieen as $c): ?>
<tr>
    <td><?= htmlspecialchars($c['naam']) ?></td>
    <td>
        <div style="background:<?= $c['kleur'] ?>; width:30px; height:15px; display:inline-block;"></div>
        <?= htmlspecialchars($c['kleur']) ?>
    </td>
    <td><?= $c['actief'] ? '✅ Actief' : '⛔ Inactief' ?></td>
    <td>
        <?php if ($c['actief']): ?>
            <a href="?tab=categorieen&deactiveer=<?= $c['id'] ?>">⛔ Deactiveren</a>
        <?php else: ?>
            <a href="?tab=categorieen&activeer=<?= $c['id'] ?>">✅ Activeren</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
