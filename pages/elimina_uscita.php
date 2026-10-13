<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verifica presenza ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID non valido.";
    exit;
}

$id = (int)$_GET['id'];

// Controlla se l'uscita esiste
$stmt = $pdo->prepare("SELECT id FROM uscite WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) {
    echo "Spesa non trovata.";
    exit;
}

// Elimina l'uscita
$delete = $pdo->prepare("DELETE FROM uscite WHERE id = ?");
$delete->execute([$id]);

// Reindirizza con conferma
header("Location: uscite.php?eliminata=1");
exit;
