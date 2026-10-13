<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validazione: controlla che almeno nome e data_ricontatto siano forniti
    if (empty($_POST['nome']) || empty($_POST['data_ricontatto'])) {
        header("Location: lead.php?error=missing");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO lead (nome, citta, email, telefono, data_ricontatto, esito) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nome'],
            $_POST['citta'] ?? null,
            $_POST['email'] ?? null,
            $_POST['telefono'] ?? null,
            $_POST['data_ricontatto'],
            $_POST['esito'] ?? null
        ]);

        header("Location: lead.php?success=1");
        exit;
    } catch (PDOException $e) {
        error_log("Errore inserimento lead: " . $e->getMessage());
        header("Location: lead.php?error=db");
        exit;
    }
} else {
    header("Location: lead.php");
    exit;
}
?>
