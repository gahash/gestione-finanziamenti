<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$id = $_GET['id'] ?? null;
if (!$id) {
  header("Location: pagamenti.php");
  exit;
}

$stmt = $pdo->prepare("DELETE FROM rate WHERE id = ?");
$stmt->execute([$id]);

header("Location: pagamenti.php");
exit;
