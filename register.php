<?php
$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

$error = "";
$success = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "Eroare conexiune la baza de date.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {

    $nume = trim($_POST["nume"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($password !== $confirm_password) {
        $error = "Parolele nu coincid!";
    } else {

        // 🔒 verificăm și email, și username
        $check = $pdo->prepare(
            "SELECT username, email FROM users 
             WHERE email = :email OR username = :username
             LIMIT 1"
        );
        $check->execute([
            ":email" => $email,
            ":username" => $nume
        ]);

        if ($row = $check->fetch(PDO::FETCH_ASSOC)) {

            if ($row["email"] === $email) {
                $error = "Email deja folosit!";
            } else if ($row["username"] === $nume) {
                $error = "Nume de utilizator deja folosit!";
            } else {
                $error = "Email sau nume utilizator deja folosit!";
            }

        } else {
            // totul e ok → inserăm
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = $pdo->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (:username, :email, :password)"
            );
            $query->execute([
                ":username" => $nume,
                ":email" => $email,
                ":password" => $hashed_password
            ]);

            $success = "Cont creat cu succes!";
        }
    }
}
?>

<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Înregistrare - AutoLux</title>

  <link rel="stylesheet" href="registerStyle.css">
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
  </nav>

  <section class="register-container glass">

    <?php if (!$success): ?>
      <h1>Înregistrare</h1>
      <p>Creează un cont AutoLux</p>

      <?php if ($error): ?>
        <div class="form-message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="register-form" method="POST" action="register.php">
        <label for="nume">Username</label>
        <input type="text" id="nume" name="nume" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Parolă</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirmă parola</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" class="register-btn">Creează cont</button>

        <p class="login-link">
          Ai deja cont? <a href="login.html">Autentifică-te aici</a>
        </p>
      </form>

    <?php else: ?>
      <h1>Succes</h1>
      <p>Cont creat cu succes!</p>

      <div class="form-message success"><?= htmlspecialchars($success) ?></div>

      <a class="register-btn link-btn" href="login.html">
        Mergi la autentificare
      </a>
    <?php endif; ?>

  </section>

</body>
</html>
