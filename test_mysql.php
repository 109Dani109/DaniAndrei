<?php

$host = 'mysql';
$db   = 'studenti';      // baza ta
$user = 'user';
$pass = 'password';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Conexiune reușită!</h2>";

    $stmt = $pdo->query("SELECT * FROM test_users");

    echo "<h3>Date din test_users:</h3>";
    echo "<table border='2' cellpadding='6' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "Eroare: " . $e->getMessage();
}
?>
