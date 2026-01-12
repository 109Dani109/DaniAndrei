<?php
$host = "mysql_lab6";   // numele serviciului din compose
$db   = "studenti_db";
$user = "student";
$pass = "studentpass";

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (Exception $e) {
  http_response_code(500);
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode(["error" => "DB connection failed", "details" => $e->getMessage()]);
  exit;
}
