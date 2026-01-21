<?php
// ------------------------------------------------------------
// admin.php
// Dit is de admin pagina waar je boeken kunt:
// - bekijken
// - toevoegen
// - bewerken
// - verwijderen
// ------------------------------------------------------------


// 1) Database verbinding (hierin staat meestal $pdo)
require_once "db.php";


// ------------------------------------------------------------
// =====  ACTIES  =====
// Alles wat iets "verandert" in de database:
// - delete (verwijderen)
// - post (toevoegen/bewerken)
// ------------------------------------------------------------


// ----------------------
// ACTIE 1: VERWIJDEREN
// ----------------------
// Als je in de URL ziet: admin.php?delete=5
// dan willen we boek met id=5 verwijderen
if (isset($_GET["delete"])) {

    // id uit de URL halen en omzetten naar een nummer
    $deleteId = (int)$_GET["delete"];

    // SQL: verwijder boek met deze id
    $stmt = $pdo->prepare("DELETE FROM boeken WHERE id = ?");
    $stmt->execute([$deleteId]);

    // Na verwijderen terug naar admin pagina
    header("Location: admin.php");
    exit;
}


// ----------------------
// ACTIE 2: OPSLAAN (POST)
// ----------------------
// Dit kan 2 dingen zijn:
// - Nieuw boek toevoegen
// - Bestaand boek bewerken
$error = "";

// Alleen uitvoeren als het formulier is verstuurd (method="POST")
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Waarden ophalen uit het formulier
    // trim() haalt spaties weg voor/achter
    $titel = trim($_POST["titel"] ?? "");
    $auteur = trim($_POST["auteur"] ?? "");
    $genre = trim($_POST["genre"] ?? "");
    $isbn = trim($_POST["isbn"] ?? "");
    $beschrijving = trim($_POST["beschrijving"] ?? "");
    $afbeelding_url = trim($_POST["afbeelding_url"] ?? "");

    // Jaar: mag leeg zijn -> dan zetten we null
    // (null = "geen waarde" in database)
    $jaar = $_POST["jaar"] ?? "";
    if ($jaar === "") {
        $jaar = null;
    } else {
        $jaar = (int)$jaar;
    }

    // Checkbox: als aangevinkt -> bestaat hij in POST -> 1
    // anders -> 0
    $is_beschikbaar = isset($_POST["is_beschikbaar"]) ? 1 : 0;

    // Simpele check: verplichte velden
    if ($titel === "" || $auteur === "" || $isbn === "") {
        $error = "Vul titel, auteur en ISBN in.";
    } else {

        // Kijken: zit er een id in het formulier?
        // - JA: update (bewerken)
        // - NEE: insert (toevoegen)

        // ------- BEWERKEN -------
        if (!empty($_POST["id"])) {

            $id = (int)$_POST["id"];

            $sql = "
                UPDATE boeken SET
                    titel = ?,
                    auteur = ?,
                    genre = ?,
                    jaar = ?,
                    isbn = ?,
                    beschrijving = ?,
                    afbeelding_url = ?,
                    is_beschikbaar = ?
                WHERE id = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $titel,
                $auteur,
                $genre === "" ? null : $genre,
                $jaar,
                $isbn,
                $beschrijving === "" ? null : $beschrijving,
                $afbeelding_url === "" ? null : $afbeelding_url,
                $is_beschikbaar,
                $id
            ]);

        } else {

            // ------- TOEVOEGEN -------
            $sql = "
                INSERT INTO boeken
                    (titel, auteur, genre, jaar, isbn, beschrijving, afbeelding_url, is_beschikbaar)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $titel,
                $auteur,
                $genre === "" ? null : $genre,
                $jaar,
                $isbn,
                $beschrijving === "" ? null : $beschrijving,
                $afbeelding_url === "" ? null : $afbeelding_url,
                $is_beschikbaar
            ]);
        }

        // Na opslaan terug naar admin pagina
        header("Location: admin.php");
        exit;
    }
}


// ------------------------------------------------------------
// =====  DATA  =====
// Alles wat we nodig hebben om te tonen in HTML:
// - boeken lijst
// - statistieken
// - bepalen of popup open moet
// - data voor het formulier (add/edit)
// ------------------------------------------------------------


// 1) Popup standaard dicht
$showModal = false;

// 2) Als je edit doet, zetten we hier het boek in
$edit = null;


// ----------------------
// Popup openen voor "add"
// ----------------------
// URL: admin.php?add=1
if (isset($_GET["add"])) {
    $showModal = true; // lege popup
}


// ----------------------
// Popup openen voor "edit"
// ----------------------
// URL: admin.php?edit=5
if (isset($_GET["edit"])) {

    $showModal = true;

    $editId = (int)$_GET["edit"];

    // Boek ophalen dat je wilt bewerken
    $stmt = $pdo->prepare("SELECT * FROM boeken WHERE id = ?");
    $stmt->execute([$editId]);

    // Resultaat opslaan
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);

    // Als het boek niet bestaat -> terug naar admin
    if (!$edit) {
        header("Location: admin.php");
        exit;
    }
}


// ----------------------
// Alle boeken ophalen
// ----------------------
$boeken = $pdo->query("SELECT * FROM boeken ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);


// ----------------------
// Statistieken berekenen
// ----------------------
$totaal = count($boeken);

$beschikbaar = 0;
foreach ($boeken as $boek) {
    if ((int)$boek["is_beschikbaar"] === 1) {
        $beschikbaar++;
    }
}

$uitgeleend = $totaal - $beschikbaar;


// ----------------------
// Defaults voor het formulier
// Als je edit -> zet waarden van dat boek erin
// Als je add -> alles leeg (behalve beschikbaar = 1)
// ----------------------
$form = [
    "id" => $edit["id"] ?? "",
    "titel" => $edit["titel"] ?? "",
    "auteur" => $edit["auteur"] ?? "",
    "genre" => $edit["genre"] ?? "",
    "jaar" => $edit["jaar"] ?? "",
    "isbn" => $edit["isbn"] ?? "",
    "beschrijving" => $edit["beschrijving"] ?? "",
    "afbeelding_url" => $edit["afbeelding_url"] ?? "",
    "is_beschikbaar" => isset($edit) ? (int)$edit["is_beschikbaar"] : 1
];
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Admin</title>

<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>

<header>
    <div class="container header-content">
        <div class="logo-wrapper">
            <a href="../Html/index.html"><img src="../images/logo.png" alt="Logo" class="logo-img"></a>
            <div class="logo-text">BibliotheekZM</div>
        </div>

        <div class="nav-and-login">
            <nav>
                <a href="../Html/index.html" class="nav-item">Home</a>
                <a href="../Html/index.html/#genre-section" class="nav-item">Genre</a>
                <a href="../Html/quiz.html" class="nav-item">Quiz</a>
                <a href="../Html/contact.html" class="nav-item">Contact</a>
                <a href="admin.php" class="nav-item active">Admin</a>
            </nav>

            <a href="../php/login.php" class="btn">Log In</a>
            <button id="night-mode-toggle" class="night-btn">🌙</button>
        </div>
    </div>
</header>

<main class="container admin-main">

    <div class="admin-header-card">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Beheer de boekcollectie van de bibliotheek</p>
        </div>

        <!-- add=1 zorgt dat popup open gaat -->
        <a class="btn" href="admin.php?add=1">+ Nieuw Boek Toevoegen</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <div class="label">Totaal Boeken</div>
                <div class="value"><?= $totaal ?></div>
            </div>
            <div class="stat-icon icon-orange">📚</div>
        </div>

        <div class="stat-card">
            <div>
                <div class="label">Beschikbaar</div>
                <div class="value"><?= $beschikbaar ?></div>
            </div>
            <div class="stat-icon icon-green">📖</div>
        </div>

        <div class="stat-card">
            <div>
                <div class="label">Uitgeleend</div>
                <div class="value"><?= $uitgeleend ?></div>
            </div>
            <div class="stat-icon icon-red">📕</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">Alle Boeken</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Auteur</th>
                        <th>Genre</th>
                        <th>Jaar</th>
                        <th>ISBN</th>
                        <th>Status</th>
                        <th style="text-align:right;">Acties</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($boeken as $b): ?>
                    <tr>
                        <td>
                            <div class="bookcell">
                                <?php if (!empty($b["afbeelding_url"])): ?>
                                    <img class="thumb" src="<?= htmlspecialchars($b["afbeelding_url"]) ?>" alt="">
                                <?php else: ?>
                                    <div class="thumb"></div>
                                <?php endif; ?>

                                <strong><?= htmlspecialchars($b["titel"]) ?></strong>
                            </div>
                        </td>

                        <td><?= htmlspecialchars($b["auteur"]) ?></td>
                        <td><?= htmlspecialchars($b["genre"] ?? "") ?></td>
                        <td><?= htmlspecialchars($b["jaar"] ?? "") ?></td>
                        <td><?= htmlspecialchars($b["isbn"]) ?></td>

                        <td>
                            <?php if ((int)$b["is_beschikbaar"] === 1): ?>
                                <span class="badge ok">Beschikbaar</span>
                            <?php else: ?>
                                <span class="badge no">Uitgeleend</span>
                            <?php endif; ?>
                        </td>

                        <td class="actions" style="text-align:right;">
                            <!-- edit=id opent popup met gevulde data -->
                            <a class="iconbtn" href="admin.php?edit=<?= (int)$b["id"] ?>" title="Bewerk">✏️</a>

                            <!-- delete=id verwijdert (met confirm popup) -->
                            <a class="iconbtn"
                               href="admin.php?delete=<?= (int)$b["id"] ?>"
                               onclick="return confirm('Verwijderen?');"
                               title="Verwijder">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($boeken) === 0): ?>
                    <tr><td colspan="7">Geen boeken gevonden.</td></tr>
                <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

</main>


<!-- ------------------------------------------------------------
     POPUP (MODAL)
     open class wordt gezet als $showModal true is
------------------------------------------------------------- -->
<div class="modal-overlay <?= $showModal ? "open" : "" ?>" id="modal">
    <div class="modal">

        <div class="modal-head">
            <h2><?= $edit ? "Boek bewerken" : "Nieuw boek toevoegen" ?></h2>
            <p>Vul de velden in en klik op Opslaan.</p>
        </div>

        <div class="modal-body">

            <!-- Error tonen als iets fout is -->
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!--
                Form action:
                - bij edit: admin.php?edit=ID (popup blijft open bij fout)
                - bij add : admin.php?add=1
            -->
            <form method="post" action="admin.php<?= $edit ? "?edit=".(int)$form["id"] : "?add=1" ?>">

                <!-- Alleen bij edit sturen we verborgen id mee -->
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= (int)$form["id"] ?>">
                <?php endif; ?>

                <div class="grid2">
                    <div class="form-group">
                        <label>Titel *</label>
                        <input name="titel" required value="<?= htmlspecialchars($form["titel"]) ?>">
                    </div>

                    <div class="form-group">
                        <label>Auteur *</label>
                        <input name="auteur" required value="<?= htmlspecialchars($form["auteur"]) ?>">
                    </div>

                    <div class="form-group">
                        <label>Genre</label>
                        <input name="genre" value="<?= htmlspecialchars($form["genre"]) ?>">
                    </div>

                    <div class="form-group">
                        <label>Jaar</label>
                        <input type="number" name="jaar" value="<?= htmlspecialchars((string)$form["jaar"]) ?>">
                    </div>

                    <div class="form-group">
                        <label>ISBN *</label>
                        <input name="isbn" required value="<?= htmlspecialchars($form["isbn"]) ?>">
                    </div>

                    <div class="form-group">
                        <label>Afbeelding URL</label>
                        <input name="afbeelding_url" value="<?= htmlspecialchars($form["afbeelding_url"]) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Beschrijving</label>
                    <textarea name="beschrijving"><?= htmlspecialchars($form["beschrijving"]) ?></textarea>
                </div>

                <div class="form-group" style="display:flex; gap:10px; align-items:center;">
                    <input type="checkbox" id="is_beschikbaar" name="is_beschikbaar" <?= $form["is_beschikbaar"] ? "checked" : "" ?>>
                    <label for="is_beschikbaar" style="margin:0;">Boek is beschikbaar</label>
                </div>

                <div class="modal-foot">
                    <a class="link-cancel" href="admin.php">Annuleren</a>
                    <button class="btn" type="submit"><?= $edit ? "Opslaan" : "Toevoegen" ?></button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="../Js/script.js"></script>
</body>
</html>
