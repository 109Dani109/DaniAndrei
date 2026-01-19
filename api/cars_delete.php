<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

function out(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload);
  exit;
}

try {
  $pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (Throwable $e) {
  out(500, ["ok" => false, "message" => "DB connection error"]);
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) out(401, ["ok" => false, "message" => "Not logged in"]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  out(405, ["ok" => false, "message" => "Method not allowed"]);
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$carId = (int)($data["car_id"] ?? 0);
if ($carId <= 0) out(400, ["ok" => false, "message" => "car_id invalid"]);

try {
  // admin?
  $st = $pdo->prepare("SELECT role FROM users WHERE id=:id LIMIT 1");
  $st->execute([":id" => (int)$userId]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  $role = strtolower((string)($u["role"] ?? "user"));
  if ($role !== "admin") out(403, ["ok" => false, "message" => "Admin only"]);

  // ia imaginea ca sa o stergem din disk dupa
  $st = $pdo->prepare("SELECT image_url FROM cars WHERE id=:id LIMIT 1");
  $st->execute([":id" => $carId]);
  $car = $st->fetch(PDO::FETCH_ASSOC);
  if (!$car) out(404, ["ok" => false, "message" => "Masina nu exista"]);

  $imageUrl = (string)($car["image_url"] ?? "");

  $pdo->beginTransaction();

  // ca sa nu pice FK (daca ai)
  $delAp = $pdo->prepare("DELETE FROM appointments WHERE car_id = :cid");
  $delAp->execute([":cid" => $carId]);

  $delCar = $pdo->prepare("DELETE FROM cars WHERE id = :cid");
  $delCar->execute([":cid" => $carId]);

  $pdo->commit();

  // sterge fisierul imaginii (doar daca e din /uploads/cars/)
  if ($imageUrl !== "" && str_starts_with($imageUrl, "/uploads/cars/")) {
    $path = __DIR__ . "/.." . $imageUrl; // /var/www/html + /uploads...
    if (is_file($path)) @unlink($path);
  }

  out(200, ["ok" => true, "message" => "Masina stearsa"]);

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  out(500, ["ok" => false, "message" => "Delete failed: " . $e->getMessage()]);
}
