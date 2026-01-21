<?php

//de database verbinding
require_once "db.php";
// Dit zorgt ervoor dat PHP kan onthouden wie is ingelogd
// Zonder session kun je geen login bijhouden

//session_start();
session_destroy();

// Controleren of de gebruiker al is ingelogd
// Als er al een user_id bestaat, hoeft de login niet opnieuw
if (isset($_SESSION["user_id"])) {
    header("Location: ../html/index.html");
    exit;
}


// Variabele om foutmeldingen in op te slaan
$error = "";


// Deze code wordt alleen uitgevoerd als het loginformulier is verstuurd
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // De ingevulde waarden uit het formulier ophalen
    // Dit zijn de inputvelden met name="email" en name="password"
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Controleren of beide velden zijn ingevuld
    if ($email == "" || $password == "") {

        $error = "Vul e-mail en wachtwoord in";

    } else {

        // Zoeken naar een gebruiker met dit e-mailadres
        // We halen het id, email en wachtwoord uit de database
        $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);

        // De gevonden gebruiker opslaan in een variabele
        $user = $stmt->fetch();

        // Controleren of de gebruiker bestaat en het wachtwoord klopt
        if ($user && $user["password_hash"] == $password) {

            // De gebruiker is ingelogd, dit slaan we op in de session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["email"] = $user["email"];

            // Na succesvol inloggen doorsturen 
            header("Location: ../html/index.html");
            exit;

        } else {
            // Als de gebruiker niet bestaat of het wachtwoord fout is
            $error = "Email of wachtwoord is fout";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bibliotheek Zoetermeer - Login</title>
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
        <h2>Inloggen</h2>

        <?php if ($error): ?>
            <div style="background:#ffe5e5; color:#900; padding:10px; border-radius:8px; margin-bottom:12px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>E-mailadres</label>
                <input type="email" name="email" placeholder="naam@voorbeeld.nl" required
                       value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Wachtwoord</label>
                <input type="password" name="password" placeholder="Je wachtwoord" required>
            </div>

            <button type="submit" class="btn" style="width:100%;">Login</button>
        </form>

        <a href="SignUp.php" style="color:#ff8c00; font-size:14px; display:block; margin-top:20px; text-decoration:none;">
            Nog geen account? Registreren
        </a>

        <a href="#" style="color:#ff8c00; font-size:14px; display:block; margin-top:20px; text-decoration:none;">
            Wachtwoord vergeten?
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
