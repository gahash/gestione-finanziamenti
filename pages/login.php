<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $valid = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $valid = true;
        } elseif (hash_equals((string) $user['password'], md5($password))) {
            // Compatibilita' con vecchi hash MD5: migrazione automatica a password_hash()
            $valid = true;
            $pdo->prepare("UPDATE utenti SET password = ? WHERE id = ?")
                ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
    }

    if ($valid) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;

        if (!empty($_POST['remember'])) {
            setcookie("remember_username", $_POST['username'], time() + (86400 * 30), "/");
        } else {
            setcookie("remember_username", "", time() - 3600, "/");
        }

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Credenziali non valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Login | Gestione Finanziamenti</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/css/login.css">
</head>
<body>

  <!-- Sfondo video -->
  <video autoplay muted loop playsinline id="bg-video">
    <source src="/video/login-background.mp4" type="video/mp4">
    Il tuo browser non supporta i video HTML5.
  </video>

  <!-- Overlay sfocato -->
  <div class="overlay"></div>

  <!-- Login form -->
  <div class="form-container">
    <div class="login-card">
      <img src="/images/logo.svg" alt="Logo Gestione Finanziamenti">
      <h4>Accedi al Gestionale</h4>

      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'password_cambiata'): ?>
        <p class="success">Password aggiornata con successo. Effettua il login con le nuove credenziali.</p>
      <?php endif; ?>

      <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

      <form method="POST">
        <div class="form-group">
          <label for="username">Email</label>
          <input
            type="text"
            name="username"
            id="username"
            class="form-control"
            placeholder="Inserisci la tua email"
            value="<?= isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : '' ?>"
            required
          >
        </div>

        <div class="form-group password-group">
          <label for="password">Password</label>
          <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            placeholder="Inserisci la tua password"
            required
          >
          <span class="mostra-password" onclick="togglePassword()">Mostra</span>
        </div>

        <div class="form-group checkbox">
          <input type="checkbox" name="remember" id="remember" <?= isset($_COOKIE['remember_username']) ? 'checked' : '' ?>>
          <label for="remember"> Ricorda username</label>
        </div>

        <button type="submit" class="btn-primary">Accedi</button>
      </form>

      <div class="footer">&copy; <?= date("Y") ?> Gestione Finanziamenti</div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById("password");
      const toggle = document.querySelector(".mostra-password");
      if (input.type === "password") {
        input.type = "text";
        toggle.textContent = "Nascondi";
      } else {
        input.type = "password";
        toggle.textContent = "Mostra";
      }
    }
  </script>

</body>
</html>

