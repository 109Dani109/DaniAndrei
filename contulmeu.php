<?php
session_start();
// aici poți prelua userul din sesiune
$username = $_SESSION['username'] ?? 'Andrei Popescu';
$email = $_SESSION['email'] ?? 'andrei.popescu@email.com';
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contul Meu - AutoLux</title>
  <link rel="stylesheet" href="navBar.css">
  <link rel="stylesheet" href="styleContulMeu.css">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <!-- Info utilizator -->
  <section class="user-section glass">
    <h2>Bun venit, <span class="username"><?php echo htmlspecialchars($username); ?></span> 👋</h2>
    <p>Email: <span class="email"><?php echo htmlspecialchars($email); ?></span></p>
  </section>

  <!-- Programare vizionare -->
  <section class="programare glass">
    <h3>Programează o vizionare</h3>
    <p>Selectează mașina dorită și stabilește data și ora.</p>
    
    <form class="form-programare">
      <label for="car">Alege mașina:</label>
      <select id="car" name="car" required>
        <option value="">Selectează...</option>
        <option value="dacia-spring">Dacia Spring</option>
        <option value="electra-ev">Electra EV</option>
        <option value="urban-suv">Urban SUV</option>
        <option value="clasic-touring">Clasic Touring</option>
      </select>

      <div class="datetime">
        <div>
          <label for="date">Data vizionării:</label>
          <input type="date" id="date" name="date" required>
        </div>
        <div>
          <label for="time">Ora vizionării:</label>
          <input type="time" id="time" name="time" required>
        </div>
      </div>

      <label for="message">Mesaj opțional:</label>
      <textarea id="message" name="message" rows="3" placeholder="Ex: Aș dori o prezentare detaliată despre interior."></textarea>

      <button type="submit" class="submit-btn">Trimite cererea</button>
    </form>
  </section>
</body>
</html>
