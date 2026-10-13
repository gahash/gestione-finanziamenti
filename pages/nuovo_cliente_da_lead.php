<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'] ?? null;
    $telefono = $_POST['telefono'] ?? null;
    $indirizzo = $_POST['indirizzo'] ?? null;
    $codice_fiscale = $_POST['codice_fiscale'] ?? null;
    $data_nascita = $_POST['data_nascita'] ?? null;
    $acquisto = $_POST['acquisto'] ?? null;
    $importo = $_POST['importo'] !== '' ? (float)$_POST['importo'] : null;
    $pagamento = $_POST['pagamento'];
    $note = $_POST['note'] ?? null;
    $data_registrazione = $_POST['data_registrazione'] ?? date('Y-m-d');
    $lead_id = $_POST['lead_id'];

    try {
        // Inserisci il cliente
        $stmt = $pdo->prepare("INSERT INTO clienti (
            nome, telefono, email, indirizzo, codice_fiscale, data_nascita,
            acquisto, importo, pagamento, note, data_registrazione
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $nome, $telefono, $email, $indirizzo, $codice_fiscale, $data_nascita,
            $acquisto, $importo, $pagamento, $note, $data_registrazione
        ]);

        $cliente_id = $pdo->lastInsertId();

        // Gestione pagamento
        if ($pagamento === 'finanziamento_interno') {
            $rata = (float) $_POST['rata_mensile'];
            $durata = (int) $_POST['durata'];
            $oggi = new DateTime($data_registrazione);

            for ($i = 0; $i < $durata; $i++) {
                $data = (clone $oggi)->modify("+$i months")->format('Y-m-d');
                $pdo->prepare("INSERT INTO rate (cliente_id, numero_rata, importo, data_scadenza, pagata, note)
                    VALUES (?, ?, ?, ?, 0, ?)")
                    ->execute([$cliente_id, $i + 1, $rata, $data, "Rata " . ($i + 1)]);
            }
        } elseif ($pagamento === 'rateale_compass') {
            $pdo->prepare("INSERT INTO rate (cliente_id, numero_rata, importo, data_scadenza, pagata, note)
                VALUES (?, 1, ?, ?, 1, ?)")
                ->execute([$cliente_id, $importo, $data_registrazione, 'Pagamento Compass']);
        }

        // Elimina il lead
        $pdo->prepare("DELETE FROM lead WHERE id = ?")->execute([$lead_id]);

        header("Location: clienti.php?success=1");
        exit;
    } catch (PDOException $e) {
        echo "<pre>Errore DB: " . $e->getMessage() . "</pre>";
    }
} else {
    header("Location: lead.php");
    exit;
}
?>
