<?php

$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    die("Eroare conexiune: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nume = trim($_POST["nume"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // 1. Validare parola = confirmare
    if ($password !== $confirm_password) {
        die("<h2 style='color:red;'>Parolele nu coincid!</h2><a href='register.html'>Înapoi</a>");
    }

    // 2. Validare email unic
    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $check->execute([":email" => $email]);

    if ($check->rowCount() > 0) {
        die("<h2 style='color:red;'>Email deja folosit!</h2><a href='register.html'>Înapoi</a>");
    }

    // 3. Hash parola
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 4. Inserare utilizator
    $query = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $query->execute([
        ":username" => $nume,
        ":email" => $email,
        ":password" => $hashed_password
    ]);

    echo "<h2>Cont creat cu succes!</h2>";
    echo "<a href='login.html'>Mergi la autentificare</a>";
}
?>
