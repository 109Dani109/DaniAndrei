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

// ca să nu mai primești HTML fatal/warnings în loc de JSON:
set_error_handler(function($severity, $message, $file, $line) {
  out(500, ["ok"=>false, "message"=>"PHP warning/error: $message (line $line)"]);
});
set_exception_handler(function($e) {
  out(500, ["ok"=>false, "message"=>"Exception: ".$e->getMessage()]);
});

$pdo = new PDO(
  "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
  $user,
  $pass,
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) out(401, ["ok"=>false, "message"=>"Not logged in"]);

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
$stmt->execute([":id" => (int)$userId]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

$role = strtolower((string)($u["role"] ?? "user"));
if ($role !== "admin") out(403, ["ok"=>false, "message"=>"Access denied (admin only)"]);

// Input
$make = trim($_POST["make"] ?? "");
$model = trim($_POST["model"] ?? "");
$description = trim($_POST["description"] ?? "");
$priceRaw = $_POST["price"] ?? "";

$yearRaw = $_POST["year"] ?? "";
$mileageRaw = $_POST["mileage_km"] ?? "";
$fuel = trim($_POST["fuel"] ?? "");
$transmission = trim($_POST["transmission"] ?? "");
$powerRaw = $_POST["power_hp"] ?? "";
$color = trim($_POST["color"] ?? "");

// Validări minime
if ($make === "" || $model === "" || $description === "" || $priceRaw === "") {
  out(400, ["ok"=>false, "message"=>"Completează make, model, price, description."]);
}
if ($mileageRaw === "" || $mileageRaw === null) {
  // la tine mileage_km e NOT NULL -> îl cerem
  out(400, ["ok"=>false, "message"=>"Kilometrajul (mileage_km) este obligatoriu (DB: NOT NULL)."]);
}

$price = (float)$priceRaw;
$year = ($yearRaw === "" || $yearRaw === null) ? null : (int)$yearRaw;
$mileage_km = (int)$mileageRaw;
$power_hp = ($powerRaw === "" || $powerRaw === null) ? null : (int)$powerRaw;

// Upload imagine
if (!isset($_FILES["image"]) || ($_FILES["image"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  out(400, ["ok"=>false, "message"=>"Imaginea este obligatorie."]);
}

$img = $_FILES["image"];
if (($img["size"] ?? 0) > 5 * 1024 * 1024) {
  out(400, ["ok"=>false, "message"=>"Imagine prea mare (max 5MB)."]);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($img["tmp_name"]);

$allowed = [
  "image/jpeg" => "jpg",
  "image/png"  => "png",
  "image/webp" => "webp"
];
if (!isset($allowed[$mime])) {
  out(400, ["ok"=>false, "message"=>"Format invalid (JPG/PNG/WEBP)."]);
}

$uploadDir = __DIR__ . "/../uploads/cars";
if (!is_dir($uploadDir)) {
  if (!mkdir($uploadDir, 0775, true)) {
    out(500, ["ok"=>false, "message"=>"Nu pot crea folderul uploads/cars"]);
  }
}

$filename = "car_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $allowed[$mime];
$destPath = $uploadDir . "/" . $filename;

if (!move_uploaded_file($img["tmp_name"], $destPath)) {
  out(500, ["ok"=>false, "message"=>"Nu s-a putut salva imaginea."]);
}

$image_url = "/uploads/cars/" . $filename;

// INSERT
$sql = "INSERT INTO cars
  (make, model, year, mileage_km, fuel, transmission, power_hp, color, price, description, image_url)
VALUES
  (:make, :model, :year, :mileage_km, :fuel, :transmission, :power_hp, :color, :price, :description, :image_url)";

$stmt = $pdo->prepare($sql);

try {
  $stmt->execute([
    ":make" => $make,
    ":model" => $model,
    ":year" => $year,                 // poate fi NULL dacă DB permite
    ":mileage_km" => $mileage_km,     // NU mai e NULL
    ":fuel" => ($fuel === "" ? null : $fuel),
    ":transmission" => ($transmission === "" ? null : $transmission),
    ":power_hp" => $power_hp,
    ":color" => ($color === "" ? null : $color),
    ":price" => $price,
    ":description" => $description,
    ":image_url" => $image_url
  ]);
} catch (PDOException $e) {
  // IMPORTANT: dacă DB are alte NOT NULL / coloane diferite, aici îți spune exact
  out(500, ["ok"=>false, "message"=>"DB insert failed: ".$e->getMessage()]);
}

out(200, [
  "ok" => true,
  "message" => "Anunț creat",
  "image_url" => $image_url
]);
