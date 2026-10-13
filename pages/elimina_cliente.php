<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

error_reporting(E_ALL);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    try {
        // Verifica che il cliente esista
        $stmt = $pdo->prepare("SELECT * FROM clienti WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            // Cancella eventuali rate collegate (opzionale, se desideri evitare orfani)
            $pdo->prepare("DELETE FROM rate WHERE cliente_id = ?")->execute([$id]);

            // Cancella il cliente
            $stmt = $pdo->prepare("DELETE FROM clienti WHERE id = ?");
            $stmt->execute([$id]);
        }

    } catch (PDOException $e) {
        echo "<pre>Errore durante l'eliminazione: " . $e->getMessage() . "</pre>";
        exit;
    }
}

header("Location: clienti.php");
exit;
