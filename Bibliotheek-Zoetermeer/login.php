<?php
session_start();

/* Simpele login (plain password in DB) */
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

/* Login verwerken */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Vul e-mail en wachtwoord in.";
    } else {
        /* User ophalen */
        $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        /* Belangrijk: alleen inloggen als wachtwoord OOK klopt */
        $passwordOk = ($row !== false) && hash_equals($row["password_hash"], $password);

        if ($passwordOk) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["email"] = $row["email"];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Onjuiste e-mail of wachtwoord.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bibliotheek Zoetermeer - Login</title>
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
