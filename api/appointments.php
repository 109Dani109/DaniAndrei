<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

function out(int $code, array $arr) {
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

try {
  $pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  out(500, ["ok" => false, "message" => "DB connection error"]);
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  out(401, ["ok" => false, "message" => "Trebuie sa fii logat."]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  out(405, ["ok" => false, "message" => "Method not allowed"]);
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data)) {
  out(400, ["ok" => false, "message" => "Body invalid (nu e JSON)."]);
}

$carId = (int)($data["car_id"] ?? 0);
$date  = trim($data["date"] ?? "");
$time  = trim($data["time"] ?? "");

if ($carId <= 0) out(400, ["ok" => false, "message" => "car_id invalid."]);
if ($date === "" || $time === "") out(400, ["ok" => false, "message" => "Lipseste data sau ora."]);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  out(400, ["ok" => false, "message" => "Format data invalid (YYYY-MM-DD)."]);
}
if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
  out(400, ["ok" => false, "message" => "Format ora invalid (HH:MM)."]);
}

$dt = DateTime::createFromFormat("Y-m-d H:i", "$date $time");
if (!$dt) {
  out(400, ["ok" => false, "message" => "Data/ora invalida."]);
}

/* =====================================================
   🔒 REGULA IMPORTANTĂ: DOAR DIN 30 ÎN 30 DE MINUTE
   ===================================================== */
$minute = (int)$dt->format("i");
if ($minute !== 0 && $minute !== 30) {
  out(400, [
    "ok" => false,
    "message" => "Ora trebuie sa fie din 30 in 30 de minute (ex: 10:00, 10:30)."
  ]);
}

try {
  // verifică existența mașinii
  $chk = $pdo->prepare("SELECT id FROM cars WHERE id = :id LIMIT 1");
  $chk->execute([":id" => $carId]);
  if (!$chk->fetch()) {
    out(400, ["ok" => false, "message" => "Masina nu exista."]);
  }

  // 🔁 ATOMIC: o singură programare per user
  $pdo->beginTransaction();

  // șterge programarea veche (dacă există)
  $del = $pdo->prepare("DELETE FROM appointments WHERE user_id = :uid");
  $del->execute([":uid" => (int)$userId]);

  // inserează programarea nouă
  $ins = $pdo->prepare("
    INSERT INTO appointments (user_id, car_id, appointment_at)
    VALUES (:user_id, :car_id, :appointment_at)
  ");
  $ins->execute([
    ":user_id" => (int)$userId,
    ":car_id" => $carId,
    ":appointment_at" => $dt->format("Y-m-d H:i:s"),
  ]);

  $pdo->commit();

  out(200, [
    "ok" => true,
    "message" => "Programarea ta a fost actualizata cu succes!"
  ]);

} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  out(500, ["ok" => false, "message" => "Eroare la salvarea programarii."]);
}
