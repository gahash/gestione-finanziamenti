<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validazione minima
    if (!isset($_POST['id'], $_POST['numero_rata'], $_POST['importo'], $_POST['data_scadenza'], $_POST['pagata'])) {
        die("⚠️ Dati incompleti per l'aggiornamento.");
    }

    try {
        $stmt = $pdo->prepare("UPDATE rate SET 
            numero_rata = ?, 
            importo = ?, 
            data_scadenza = ?, 
            pagata = ?, 
            note = ? 
            WHERE id = ?");

        $stmt->execute([
            (int) $_POST['numero_rata'],
            (float) $_POST['importo'],
            $_POST['data_scadenza'],
            (int) $_POST['pagata'],
            $_POST['note'] ?? null,
            (int) $_POST['id']
        ]);

        header("Location: pagamenti.php?success=1");
        exit;

    } catch (PDOException $e) {
        echo "<pre>Errore DB: " . $e->getMessage() . "</pre>";
    }

} else {
    header("Location: pagamenti.php");
    exit;
}
?>
