<?php
require_once "db.php";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") exit;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  $stmt = $pdo->query("SELECT id, nume, an, media FROM studenti ORDER BY id DESC");
  echo json_encode($stmt->fetchAll());
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  $nume  = trim($data["nume"] ?? "");
  $an    = (int)($data["an"] ?? 0);
  $media = (float)($data["media"] ?? -1);

  if ($nume === "" || $an < 1 || $an > 4 || $media < 0 || $media > 10) {
    http_response_code(400);
    echo json_encode(["error" => "Date invalide"]);
    exit;
  }

  $stmt = $pdo->prepare("INSERT INTO studenti (nume, an, media) VALUES (?, ?, ?)");
  $stmt->execute([$nume, $an, $media]);

  echo json_encode(["ok" => true, "id" => (int)$pdo->lastInsertId()]);
  exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
