<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Abilita errori per debug in sviluppo (opzionale, da rimuovere in produzione)
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['esito'])) {
    $id = (int) $_POST['id'];
    $esito = trim($_POST['esito']);

    try {
        $stmt = $pdo->prepare("UPDATE lead SET esito = ? WHERE id = ?");
        $stmt->execute([$esito, $id]);

        // Redirect dopo aggiornamento
        header("Location: lead.php?success=esito_aggiornato");
        exit;
    } catch (PDOException $e) {
        echo "<pre>Errore DB: " . $e->getMessage() . "</pre>";
    }
} else {
    // Accesso non valido, redirect
    header("Location: lead.php");
    exit;
}
?>
