<?php
session_start();

$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$port = 3306;

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}

try {
  $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  die("DB connection error");
}

$userId = (int)$_SESSION['user_id'];

/* User: luam tot ca sa nu depindem de coloane care nu exista */
$u = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$u->execute([":id" => $userId]);
$userRow = $u->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
  session_destroy();
  header("Location: login.html");
  exit;
}

/* Alege un nume de afisat din coloane posibile */
$displayName = "";
if (!empty($userRow["nume"] ?? "")) {
  $displayName = $userRow["nume"];
} elseif (!empty($userRow["username"] ?? "")) {
  $displayName = $userRow["username"];
} elseif (!empty($userRow["name"] ?? "")) {
  $displayName = $userRow["name"];
} elseif (!empty($userRow["fullname"] ?? "")) {
  $displayName = $userRow["fullname"];
}

$email = $userRow["email"] ?? "";

/* Programarea (ultima) */
$appt = null;
try {
  $a = $pdo->prepare("
    SELECT a.appointment_at, c.make, c.model
    FROM appointments a
    JOIN cars c ON c.id = a.car_id
    WHERE a.user_id = :uid
    ORDER BY a.appointment_at DESC
    LIMIT 1
  ");
  $a->execute([":uid" => $userId]);
  $appt = $a->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $appt = null;
}

$car = $data = $ora = "";
if ($appt && !empty($appt["appointment_at"])) {
  $dt = new DateTime($appt["appointment_at"]);
  $data = $dt->format("d.m.Y");
  $ora  = $dt->format("H:i");
  $car  = trim(($appt["make"] ?? "") . " " . ($appt["model"] ?? ""));
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contul meu - AutoLux</title>

  <!-- aceleasi CSS-uri ca restul site-ului -->
  <link rel="stylesheet" href="navBar.css?v=1004">
  <link rel="stylesheet" href="styleCatalog.css?v=1004">

  <style>
    .page-wrap {
      width: min(1100px, calc(100% - 32px));
      margin: 26px auto 60px;
    }

    .account-header {
      margin-top: 16px;
      padding: 18px 18px;
      border-radius: 18px;
    }

    .account-grid {
      display: grid;
      grid-template-columns: 1.2fr .8fr;
      gap: 16px;
      margin-top: 16px;
    }

    @media (max-width: 900px) {
      .account-grid { grid-template-columns: 1fr; }
    }

    .glass-card {
      padding: 18px;
      border-radius: 18px;
    }

    .card-title {
      margin: 0 0 10px 0;
      font-size: 20px;
      font-weight: 700;
    }

    .rows { display: grid; gap: 10px; }

    .row {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      gap: 14px;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.10);
    }

    .row b { opacity: .95; }
    .row span { opacity: .9; overflow-wrap:anywhere; }

    .muted { opacity: .75; }

    .actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    /* Butoane in stil glass + accent rosu */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.14);
      background: rgba(255,255,255,.06);
      color: inherit;
      text-decoration: none;
      cursor: pointer;
      transition: transform .12s ease, background .12s ease, border-color .12s ease;
    }
    .btn:hover { transform: translateY(-1px); background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.22); }

    .btn-primary {
      background: rgba(255, 61, 61, .14);
      border-color: rgba(255, 61, 61, .28);
    }
    .btn-primary:hover {
      background: rgba(255, 61, 61, .18);
      border-color: rgba(255, 61, 61, .36);
    }

    .subtitle {
      margin: 6px 0 0 0;
      opacity: .8;
    }

    .ok-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(46, 204, 113, .10);
      border: 1px solid rgba(46, 204, 113, .22);
      margin-top: 12px;
    }
    .ok-dot {
      width: 10px; height: 10px;
      border-radius: 999px;
      background: #2ecc71;
      box-shadow: 0 0 18px rgba(46,204,113,.55);
      flex: 0 0 auto;
    }
  </style>
</head>
<body>

<nav class="navbar glass">
  <a href="index.html" class="nav-logo">AutoLux</a>

  <ul class="nav-links">
    <li><a href="about.html">Despre Noi</a></li>
    <li><a href="catalog.html">Catalog</a></li>
    <li><a href="locatii.html">Locatii</a></li>
    <li><a href="contact.html">Contact</a></li>
  </ul>

  <button class="login-btn" id="authBtn">
    <a id="authLink" href="login.html">Login</a>
  </button>

  <button class="login-btn" id="logoutBtn" style="display:none;">
    <a id="logoutLink" href="#">Logout</a>
  </button>
</nav>

<div class="page-wrap">
  <header class="account-header glass">
    <h1 style="margin:0;">Contul meu</h1>
    <p class="subtitle">Datele contului tău și programarea făcută la test drive.</p>
  </header>

  <section class="account-grid">
    <!-- Date cont -->
    <div class="glass-card glass">
      <h2 class="card-title">Date cont</h2>

      <div class="rows">
        <div class="row">
          <b>Username/Nume</b>
          <span><?= htmlspecialchars($displayName !== "" ? $displayName : "(necompletat)") ?></span>
        </div>

        <div class="row">
          <b>Email</b>
          <span><?= htmlspecialchars($email) ?></span>
        </div>
      </div>

      <div class="actions">
        <a class="btn btn-primary" href="catalog.html">Programează din nou</a>
        <a class="btn" href="index.html">Înapoi acasă</a>
      </div>
    </div>

    <!-- Programare -->
    <div class="glass-card glass">
      <h2 class="card-title">Programarea ta</h2>

      <?php if ($appt && $car !== ""): ?>
        <div class="rows">
          <div class="row">
            <b>Mașina</b>
            <span><?= htmlspecialchars($car) ?></span>
          </div>
          <div class="row">
            <b>Data</b>
            <span><?= htmlspecialchars($data) ?></span>
          </div>
          <div class="row">
            <b>Ora</b>
            <span><?= htmlspecialchars($ora) ?></span>
          </div>
        </div>

        <div class="ok-badge">
          <span class="ok-dot"></span>
          <span class="muted">Programare salvată în sistem.</span>
        </div>

      <?php else: ?>
        <p class="muted" style="margin:0;">
          Nu ai nicio programare încă. Intră în catalog și apasă „Programează-te”.
        </p>
        <div class="actions">
          <a class="btn btn-primary" href="catalog.html">Mergi la catalog</a>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<script src="navAuth.js"></script>
</body>
</html>
