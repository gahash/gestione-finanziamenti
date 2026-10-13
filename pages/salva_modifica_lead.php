<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Validazione
  $id = $_POST['id'] ?? null;
  $nome = trim($_POST['nome'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $data_ricontatto = $_POST['data_ricontatto'] ?? '';
  $esito = $_POST['esito'] ?? '';

  if (!$id || $nome === '' || $data_ricontatto === '') {
    header("Location: modifica_lead.php?id=$id&error=missing");
    exit;
  }

  try {
    $stmt = $pdo->prepare("UPDATE lead 
      SET nome = ?, email = ?, telefono = ?, data_ricontatto = ?, esito = ? 
      WHERE id = ?");
    $stmt->execute([
      $nome,
      $email,
      $telefono,
      $data_ricontatto,
      $esito,
      $id
    ]);

    header("Location: lead.php?success=1");
    exit;

  } catch (PDOException $e) {
    error_log("Errore salvataggio lead: " . $e->getMessage());
    header("Location: modifica_lead.php?id=$id&error=db");
    exit;
  }
} else {
  header("Location: lead.php");
  exit;
}
