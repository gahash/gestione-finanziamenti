<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Abilita segnalazione errori per debug (disabilita in produzione)
error_reporting(E_ALL);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validazione basilare
        if (empty($_POST['descrizione']) || empty($_POST['importo']) || empty($_POST['tipo']) || empty($_POST['data_uscita'])) {
            throw new Exception("Tutti i campi obbligatori devono essere compilati.");
        }

        // Prepara e esegue l'inserimento
        $stmt = $pdo->prepare("INSERT INTO uscite (descrizione, importo, note, tipo, data_uscita)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['descrizione'],
            (float)$_POST['importo'],
            $_POST['note'] ?? null,
            $_POST['tipo'],
            $_POST['data_uscita']
        ]);

        // Redirect dopo il salvataggio
        header("Location: uscite.php?success=1");
        exit;

    } catch (Exception $e) {
        // In caso di errore, stampa messaggio
        echo "<p style='color: red;'>Errore: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href='uscite.php'>Torna indietro</a></p>";
        exit;
    }
} else {
    // Accesso diretto non consentito
    header("Location: uscite.php");
    exit;
}
