<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

header('Content-Type: application/json');
error_reporting(E_ALL);

try {
    $nome = $_POST['nome'];
    $telefono = $_POST['telefono'] ?? null;
    $email = $_POST['email'] ?? null;
    $indirizzo = $_POST['indirizzo'] ?? null;
    $codice_fiscale = $_POST['codice_fiscale'] ?? null;
    $data_nascita = $_POST['data_nascita'] ?? null;
    $acquisto = $_POST['acquisto'] ?? null;
    $importo = $_POST['importo'] !== '' ? (float)$_POST['importo'] : null;
    $acconto = $_POST['acconto'] !== '' ? (float)$_POST['acconto'] : 0;
    $interessi = $_POST['interessi'] !== '' ? (float)$_POST['interessi'] : 0;
    $pagamento = $_POST['pagamento'];
    $note = $_POST['note'] ?? null;
    $data_registrazione = $_POST['data_registrazione'] ?? date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO clienti (
        nome, telefono, email, indirizzo, codice_fiscale, data_nascita,
        acquisto, importo, acconto, interessi, pagamento, note, data_registrazione
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $nome, $telefono, $email, $indirizzo, $codice_fiscale, $data_nascita,
        $acquisto, $importo, $acconto, $interessi, $pagamento, $note, $data_registrazione
    ]);

    echo json_encode(["success" => true, "message" => "Cliente aggiunto con successo."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Errore: " . $e->getMessage()]);
}
