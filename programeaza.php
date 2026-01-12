<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function out($ok, $message, $extra = []) {
    echo json_encode(array_merge(["ok" => $ok, "message" => $message], $extra));
    exit;
}

// === CONEXIUNEA TA (las-o cum o ai în proiect dacă e diferită) ===
$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    out(false, "Eroare conexiune DB.", ["detail" => $e->getMessage()]);
}

// 1) login check
if (!isset($_SESSION['user_id'])) {
    out(false, "Nu ești logat (SESSION lipsă).");
}
$user_id = (int)$_SESSION['user_id'];

// 2) POST fields
$masina = trim($_POST['masina'] ?? '');
$data   = trim($_POST['data'] ?? '');
$ora    = trim($_POST['ora'] ?? '');

if ($masina === '' || $data === '' || $ora === '') {
    out(false, "Lipsesc câmpuri.", ["got" => ["masina"=>$masina, "data"=>$data, "ora"=>$ora]]);
}

// 3) parse date (acceptă: Y-m-d, m/d/Y, d/m/Y)
$dateObj = null;
$formatsDate = ['Y-m-d', 'm/d/Y', 'd/m/Y'];
foreach ($formatsDate as $fmt) {
    $tmp = DateTime::createFromFormat($fmt, $data);
    if ($tmp && $tmp->format($fmt) === $data) { $dateObj = $tmp; break; }
}
if (!$dateObj) out(false, "Format dată invalid: $data");

// 4) parse time (acceptă: H:i, H:i:s, h:i A, h:i:s A)
$timeObj = null;
$formatsTime = ['H:i', 'H:i:s', 'h:i A', 'h:i:s A'];
foreach ($formatsTime as $fmt) {
    $tmp = DateTime::createFromFormat($fmt, $ora);
    if ($tmp) { $timeObj = $tmp; break; }
}
if (!$timeObj) out(false, "Format oră invalid: $ora");

$mysqlDate = $dateObj->format('Y-m-d');
$mysqlTime = $timeObj->format('H:i:s');
$mysqlDT   = $mysqlDate . ' ' . $mysqlTime;

// 5) verificăm tabel/coloane fără să schimbăm DB
try {
    $cols = $pdo->query("SHOW COLUMNS FROM programari")->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    out(false, "Nu există tabelul programari sau nu am acces.", ["detail" => $e->getMessage()]);
}

$hasDataOra = in_array('data_ora', $cols, true);
$hasData    = in_array('data', $cols, true);
$hasOra     = in_array('ora', $cols, true);
$hasMasina  = in_array('masina', $cols, true);
$hasUserId  = in_array('user_id', $cols, true);

if (!$hasMasina || !$hasUserId) {
    out(false, "Tabelul programari nu are coloanele necesare (user_id/masina).", ["cols" => $cols]);
}

try {
    if ($hasDataOra) {
        $stmt = $pdo->prepare("INSERT INTO programari (user_id, masina, data_ora) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $masina, $mysqlDT]);
        out(true, "Programare salvată (data_ora).");
    }

    if ($hasData && $hasOra) {
        $stmt = $pdo->prepare("INSERT INTO programari (user_id, masina, data, ora) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $masina, $mysqlDate, $mysqlTime]);
        out(true, "Programare salvată (data + ora).");
    }

    out(false, "Tabelul programari nu are data_ora și nici data+ora.", ["cols" => $cols]);

} catch (PDOException $e) {
    out(false, "Insert eșuat.", ["detail" => $e->getMessage()]);
}
