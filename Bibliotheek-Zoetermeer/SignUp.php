<?php
session_start();

/* DB settings */
$host = "localhost";
$db   = "bibliotheek";
$user = "root";
$pass = "";
$charset = "utf8mb4";
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

/* DB connect */
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database verbinding mislukt");
}

/* Als al ingelogd -> dashboard */
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

/* Signup verwerken */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    // Basis checks
    if ($email === "" || $password === "" || $password2 === "") {
        $error = "Vul alle velden in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Vul een geldig e-mailadres in.";
    } elseif (strlen($password) < 6) {
        $error = "Wachtwoord moet minimaal 6 tekens zijn.";
    } elseif ($password !== $password2) {
        $error = "Wachtwoorden komen niet overeen.";
    } else {
        // Bestaat email al?
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $error = "Dit e-mailadres is al geregistreerd.";
        } else {
            // Wachtwoord opslaan
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :password)");
            $stmt->execute([
                "email" => $email,
                "password" => $password
            ]);

            // Optie A: direct inloggen
            $newId = $pdo->lastInsertId();
            $_SESSION["user_id"] = $newId;
            $_SESSION["email"] = $email;

            header("Location: dashboard.php");
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container header-content">
        <div class="logo-wrapper">
            <a href="index.html" class="btn" style="border-radius:50%; width:40px; height:40px; padding:10px;">&#10094;</a>
            <span class="logo-text">Bibliotheek</span>
            <a href="index.html"><img src="./images/logo.png" alt="Logo" class="logo-img"></a>
            <span class="logo-text">Zoetermeer</span>
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
