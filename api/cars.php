<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

try {
  $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "DB connection error"]);
  exit;
}

$stmt = $pdo->query("
  SELECT id, make, model, year, price, mileage_km, fuel, transmission, power_hp, color, description, image_url
  FROM cars
  ORDER BY id DESC
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
