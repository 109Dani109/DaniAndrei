<?php
header('Content-Type: application/json; charset=utf-8');

// Config DB
$host = 'mysql';     // numele containerului MySQL din docker-compose
$port = 3306;
$dbname = 'studenti';
$user = 'user';
$pass = 'password';

// Conectare la DB
$mysqli = new mysqli($host, $user, $pass, $dbname, $port);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->connect_error]);
    exit;
}

// Preluare date
$sql = "SELECT id, make, model, year, price, image_url, description FROM cars ORDER BY id DESC";
$result = $mysqli->query($sql);

$cars = [];
while ($row = $result->fetch_assoc()) {
    $cars[] = $row;
}

echo json_encode($cars);
$mysqli->close();
