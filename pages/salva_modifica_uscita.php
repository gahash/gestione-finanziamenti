<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE uscite SET descrizione=?, importo=?, note=?, tipo=?, data_uscita=? WHERE id=?");
    $stmt->execute([
        $_POST['descrizione'],
        $_POST['importo'],
        $_POST['note'],
        $_POST['tipo'],
        $_POST['data_uscita'],
        $_POST['id']
    ]);
    echo "ok";
    exit;
}
echo "errore";
