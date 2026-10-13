<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_SESSION['user']['id'];
    $password_corrente = $_POST['password_corrente'];
    $nuova_password = $_POST['nuova_password'];
    $conferma_password = $_POST['conferma_password'];

    if ($nuova_password !== $conferma_password) {
        $errore = "❌ Le nuove password non coincidono.";
    } else {
        // Recupera password attuale
        $stmt = $pdo->prepare("SELECT password FROM utenti WHERE id = ?");
        $stmt->execute([$id]);
        $utente = $stmt->fetch();

        if (!$utente || !password_verify($password_corrente, $utente['password'])) {
            $errore = "❌ La password corrente non è corretta.";
        } else {
            // Aggiorna la password e reindirizza alla login
            $nuova_hash = password_hash($nuova_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE utenti SET password = ? WHERE id = ?");
            $update->execute([$nuova_hash, $id]);

            // Logout forzato
            session_unset();
            session_destroy();
            header("Location: login.php?msg=password_cambiata");
            exit;
        }
    }
}
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card" style="max-width: 500px; margin: 30px auto;">
    <h2><i class="fas fa-key"></i> Cambia Password</h2>

    <?php if ($errore): ?><p style="color:red;"><?= $errore ?></p><?php endif; ?>

    <form method="post">
      <label>Password Attuale:
        <input type="password" name="password_corrente" required>
      </label>
      <label>Nuova Password:
        <input type="password" name="nuova_password" required>
      </label>
      <label>Conferma Nuova Password:
        <input type="password" name="conferma_password" required>
      </label>
      <button type="submit" class="btn">Aggiorna</button>
    </form>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>
