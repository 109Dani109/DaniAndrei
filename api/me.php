<?php
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
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (Throwable $e) {
  out(500, ["ok" => false, "message" => "DB connection error"]);
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) out(401, ["ok" => false, "message" => "Not logged in"]);

$stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = :id LIMIT 1");
$stmt->execute([":id" => (int)$userId]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$me) out(401, ["ok" => false, "message" => "Invalid session (user not found)"]);

$email = $me["email"] ?? "";
$username = $me["username"] ?? "";

$displayName = "User";
if (!empty($username)) $displayName = $username;
elseif ($email && strpos($email, "@") !== false) $displayName = explode("@", $email)[0];
elseif ($email) $displayName = $email;

$role = strtolower((string)($me["role"] ?? "user"));

out(200, [
  "ok" => true,
  "user" => [
    "id" => (int)$me["id"],
    "username" => $username,
    "email" => $email,
    "display_name" => $displayName,
    "role" => $role
  ]
]);
