<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $stmt = $pdo->prepare("UPDATE rate SET
    numero_rata = ?,
    importo = ?,
    data_scadenza = ?,
    pagata = ?,
    note = ?
    WHERE id = ?");

  $stmt->execute([
    $_POST['numero_rata'],
    $_POST['importo'],
    $_POST['data_scadenza'],
    $_POST['pagata'],
    $_POST['note'],
    $_POST['id']
  ]);

  header("Location: pagamenti.php"); // o scadenziario.php
  exit;
} else {
  echo "Errore nella richiesta.";
}
