<?php
require_once "db.php";

/* ===== ACTIES ===== */

// Verwijderen
if (isset($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM boeken WHERE id = ?");
    $stmt->execute([(int)$_GET["delete"]]);
    header("Location: admin.php");
    exit;
}

// Opslaan (toevoegen / bewerken)
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titel = trim($_POST["titel"] ?? "");
    $auteur = trim($_POST["auteur"] ?? "");
    $genre = trim($_POST["genre"] ?? "");
    $jaar = ($_POST["jaar"] ?? "") !== "" ? (int)$_POST["jaar"] : null;
    $isbn = trim($_POST["isbn"] ?? "");
    $beschrijving = trim($_POST["beschrijving"] ?? "");
    $afbeelding_url = trim($_POST["afbeelding_url"] ?? "");
    $is_beschikbaar = isset($_POST["is_beschikbaar"]) ? 1 : 0;

    if ($titel === "" || $auteur === "" || $isbn === "") {
        $error = "Vul titel, auteur en ISBN in.";
    } else {
        // update of insert
        if (!empty($_POST["id"])) {
            $id = (int)$_POST["id"];
            $stmt = $pdo->prepare("
                UPDATE boeken SET
                    titel=?, auteur=?, genre=?, jaar=?, isbn=?,
                    beschrijving=?, afbeelding_url=?, is_beschikbaar=?
                WHERE id=?
            ");
            $stmt->execute([
                $titel, $auteur, $genre ?: null, $jaar, $isbn,
                $beschrijving ?: null, $afbeelding_url ?: null, $is_beschikbaar, $id
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO boeken
                    (titel,auteur,genre,jaar,isbn,beschrijving,afbeelding_url,is_beschikbaar)
                VALUES (?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $titel, $auteur, $genre ?: null, $jaar, $isbn,
                $beschrijving ?: null, $afbeelding_url ?: null, $is_beschikbaar
            ]);
        }

        header("Location: admin.php");
        exit;
    }
}

/* ===== DATA ===== */

// Popup open?
$showModal = false;
$edit = null;

if (isset($_GET["add"])) {
    $showModal = true; // lege popup
}

if (isset($_GET["edit"])) {
    $showModal = true;
    $stmt = $pdo->prepare("SELECT * FROM boeken WHERE id=?");
    $stmt->execute([(int)$_GET["edit"]]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$edit) {
        // als id niet bestaat -> terug
        header("Location: admin.php");
        exit;
    }
}

$boeken = $pdo->query("SELECT * FROM boeken ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$totaal = count($boeken);
$beschikbaar = 0;
foreach ($boeken as $b) {
    if ((int)$b["is_beschikbaar"] === 1) $beschikbaar++;
}
$uitgeleend = $totaal - $beschikbaar;

// defaults voor form
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

<style>
/* kleine extra's voor dashboard + popup, gebruikt jouw CSS variables */
.admin-main { padding: 50px 0; }
.admin-header-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 15px;
    padding: 28px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}
.admin-header-card h1 { margin:0; font-size: 34px; }
.admin-header-card p { margin:6px 0 0; color: #666; }

.stats-grid {
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}
.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 15px;
    padding: 22px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.stat-card .label { font-size: 14px; color:#666; }
.stat-card .value { font-size: 34px; font-weight:700; margin-top:6px; }
.stat-icon {
    width:44px; height:44px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-weight:700;
}
.icon-orange { background:#ffe9d5; color: var(--accent); }
.icon-green { background:#dcfce7; color:#166534; }
.icon-red { background:#fee2e2; color:#991b1b; }

.panel {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    overflow:hidden;
}
.panel-head {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border-color);
    font-weight: 700;
}
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse: collapse; }
th, td { padding: 14px 12px; border-bottom: 1px solid var(--border-color); text-align:left; font-size: 14px; }
th { background: #f6f6f6; font-weight:700; }
body.dark-mode th { background: #1a1a1a; }

.bookcell { display:flex; align-items:center; gap:12px; }
.thumb {
    width: 40px; height: 56px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border-color);
    background: #eee;
}
.badge {
    display:inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}
.badge.ok { background:#dcfce7; color:#166534; }
.badge.no { background:#fee2e2; color:#991b1b; }

.actions { white-space:nowrap; }
.iconbtn {
    display:inline-block;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    text-decoration:none;
    color: var(--text-main);
    background: transparent;
    margin-left: 6px;
}
.iconbtn:hover { border-color: var(--accent); }

/* ===== Popup (modal) ===== */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.35);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 999;
}
.modal-overlay.open { display:flex; }

.modal {
    width: min(850px, 100%);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    overflow: hidden;
}
.modal-head {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border-color);
}
.modal-head h2 { margin:0; }
.modal-head p { margin:6px 0 0; color:#666; }

.modal-body { padding: 22px; }

.grid2 { display:grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 14px; }
.form-group label { display:block; font-weight:700; margin-bottom:6px; }
.form-group input, .form-group textarea {
    width:100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: var(--bg-body);
    color: var(--text-main);
}
.form-group textarea { min-height: 90px; resize: vertical; }

.modal-foot {
    padding: 18px 22px;
    border-top: 1px solid var(--border-color);
    display:flex;
    gap: 12px;
    justify-content: flex-end;
}
.link-cancel { color: var(--accent); text-decoration: underline; padding: 12px 0; }

.error {
    background:#fee2e2;
    border:1px solid #fecaca;
    color:#991b1b;
    padding:10px 12px;
    border-radius:10px;
    margin-bottom: 12px;
}
</style>
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
                            <a class="iconbtn" href="admin.php?edit=<?= (int)$b["id"] ?>" title="Bewerk">✏️</a>
                            <a class="iconbtn" href="admin.php?delete=<?= (int)$b["id"] ?>"
                               onclick="return confirm('Verwijderen?');" title="Verwijder">🗑️</a>
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

<!-- Popup -->
<div class="modal-overlay <?= $showModal ? "open" : "" ?>" id="modal">
    <div class="modal">
        <div class="modal-head">
            <h2><?= $edit ? "Boek bewerken" : "Nieuw boek toevoegen" ?></h2>
            <p>Vul de velden in en klik op Opslaan.</p>
        </div>

        <div class="modal-body">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="admin.php<?= $edit ? "?edit=".(int)$form["id"] : "?add=1" ?>">
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
