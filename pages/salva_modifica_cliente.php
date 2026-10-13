<?php 
require_once "../includes/auth.php";
require_once "../config/db.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        die("ID cliente mancante.");
    }

    try {
        $stmt = $pdo->prepare("UPDATE clienti SET
            nome = ?,
            telefono = ?,
            email = ?,
            indirizzo = ?,
            codice_fiscale = ?,
            data_nascita = ?,
            acquisto = ?,
            importo = ?,
            acconto = ?,
            interessi = ?,
            citta = ?,
            pagamento = ?,
            note = ?,
            data_registrazione = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['nome'],
            $_POST['telefono'],
            $_POST['email'],
            $_POST['indirizzo'],
            $_POST['codice_fiscale'],
            $_POST['data_nascita'],
            $_POST['acquisto'],
            $_POST['importo'] !== '' ? (float)$_POST['importo'] : null,
            $_POST['acconto'] !== '' ? (float)$_POST['acconto'] : 0,
            $_POST['interessi'] !== '' ? (float)$_POST['interessi'] : 0,
            $_POST['citta'],
            $_POST['pagamento'],
            $_POST['note'],
            $_POST['data_registrazione'],
            $_POST['id']
        ]);

        header("Location: clienti.php?success=1");
        exit;

    } catch (PDOException $e) {
        echo "<pre style='color:red;'>ERRORE DB: " . $e->getMessage() . "</pre>";
        echo "<pre>DATI POST:\n" . print_r($_POST, true) . "</pre>";
        exit;
    }

} else {
    header("Location: clienti.php");
    exit;
}
