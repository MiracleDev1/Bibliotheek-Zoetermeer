<?php
//de database verbinding
require_once "db.php";
// Dit is nodig om later te kunnen onthouden dat iemand is ingelogd

//session_start();
session_destroy();

// Check of de gebruiker al is ingelogd
// Als er al een user_id in de session staat, hoeft registreren niet
if (isset($_SESSION["user_id"])) {
    header("Location: ../html/index.html");
    exit;
}


// Variabelen om foutmeldingen in op te slaan
$error = "";
$success = "";


// Alleen uitvoeren als iemand het formulier heeft verstuurd
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // De ingevulde waarden uit het formulier ophalen
    // Dit zijn de inputs met name="email", name="password" en name="password2"
    $email = $_POST["email"];
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    // Controleren of alle velden zijn ingevuld
    if ($email == "" || $password == "" || $password2 == "") {

        $error = "Vul alle velden in";

    // Controleren of beide wachtwoorden hetzelfde zijn
    } elseif ($password != $password2) {

        $error = "Wachtwoorden komen niet overeen";

    } else {

        // Kijken of dit e-mailadres al in de database staat
        // We zoeken een gebruiker met dit email adres
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        // Als er iets wordt gevonden, bestaat de gebruiker al
        $user = $stmt->fetch();

        if ($user) {

            // Email bestaat al, dus niet opnieuw registreren
            $error = "Dit e-mailadres bestaat al";

        } else {

            // Nieuwe gebruiker toevoegen aan de database
            // Email en wachtwoord worden opgeslagen
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
            $stmt->execute([$email, $password]);

            // De nieuwe gebruiker direct inloggen
            // lastInsertId geeft het id van de nieuwe gebruiker
            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["email"] = $email;

            // Na registreren doorsturen 
            header("Location: ../html/index.html");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bibliotheek Zoetermeer - Registreren</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <div class="container header-content">
        <div class="logo-wrapper">
            <a href="../html/index.html" class="btn" style="border-radius:50%; width:40px; height:40px; padding:10px;">&#10094;</a>
            <a href="../html/index.html"><img src="../images/logo.png" alt="Logo" class="logo-img"></a>
        </div>
    </div>
</header>

<main class="login-wrapper-main">
    <div class="login-card">
        <h2>Registreren</h2>

        <?php if ($error): ?>
            <div style="background:#ffe5e5; color:#900; padding:10px; border-radius:8px; margin-bottom:12px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:#e5ffe5; color:#060; padding:10px; border-radius:8px; margin-bottom:12px;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <div class="form-group">
                <label>E-mailadres</label>
                <input type="email" name="email" placeholder="naam@voorbeeld.nl" required
                       value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Wachtwoord</label>
                <input type="password" name="password" placeholder="Minimaal 6 tekens" required>
            </div>

            <div class="form-group">
                <label>Herhaal wachtwoord</label>
                <input type="password" name="password2" placeholder="Herhaal je wachtwoord" required>
            </div>

            <button type="submit" class="btn" style="width:100%;">Account maken</button>
        </form>

        <a href="login.php" style="color:#ff8c00; font-size:14px; display:block; margin-top:20px; text-decoration:none;">
            Heb je al een account? Inloggen
        </a>
    </div>
</main>

<footer>
    <div class="container footer-flex">
        <div><strong>Library Zoetermeer</strong><p>Forum 6, Zoetermeer</p></div>
        <div><strong>Contact</strong><p>info@library.nl</p></div>
    </div>
</footer>
</body>
</html>
