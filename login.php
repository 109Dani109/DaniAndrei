<?php
session_start();

$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

$error = "";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "Eroare conexiune la baza de date.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {
    $usernameOrEmail = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($usernameOrEmail === "" || $password === "") {
        $error = "Completează toate câmpurile.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = :u OR email = :u LIMIT 1");
        $stmt->execute([":u" => $usernameOrEmail]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            $error = "Utilizator inexistent.";
        } else if (!password_verify($password, $userRow["password"])) {
            $error = "Parolă greșită.";
        } else {
            $_SESSION["user_id"] = (int)$userRow["id"];
            $_SESSION["username"] = $userRow["username"];
            $_SESSION["email"] = $userRow["email"];

            header("Location: index.html");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - AutoLux</title>
  <link rel="stylesheet" href="styleLogin.css">
  <link rel="stylesheet" href="navBar.css">
</head>
<body>

  <nav class="navbar glass">
    <a href="index.html" class="nav-logo">AutoLux</a>

    <ul class="nav-links">
      <li><a href="about.html">Despre Noi</a></li>
      <li><a href="catalog.html">Catalog</a></li>
      <li><a href="locatii.html">Locații</a></li>
      <li><a href="contact.html">Contact</a></li>
    </ul>

    <button class="login-btn" id="authBtn">
      <a id="authLink" href="login.php">Login</a>
    </button>

    <button class="login-btn" id="logoutBtn" style="display:none;">
      <a id="logoutLink" href="logout.php">Logout</a>
    </button>
  </nav>

  <section class="login-container glass">
    <h1>Autentificare</h1>
    <p>Introduceți datele pentru a vă conecta la contul AutoLux</p>

    <?php if ($error): ?>
      <div class="form-message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="login.php">
      <label for="username">Nume utilizator sau Email</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Parolă</label>
      <input type="password" id="password" name="password" required>

      <button type="submit" class="send-btn">Conectează-te</button>

      <p class="register-link">Nu ai cont? <a href="register.php">Înregistrează-te aici</a></p>
    </form>
  </section>

  <script src="navAuth.js"></script>
</body>
</html>
