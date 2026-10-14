<?php
require_once "../config/db.php";
require_once "../includes/auth.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'];
        $telefono = $_POST['telefono'] ?? null;
        $email = $_POST['email'] ?? null;
        $indirizzo = $_POST['indirizzo'] ?? null;
        $codice_fiscale = $_POST['codice_fiscale'] ?? null;
        $data_nascita = $_POST['data_nascita'] ?? null;
        $acquisto = $_POST['acquisto'] ?? null;
        $importo = isset($_POST['importo']) && $_POST['importo'] !== '' ? (float)$_POST['importo'] : null;
        $acconto = isset($_POST['acconto']) && $_POST['acconto'] !== '' ? (float)$_POST['acconto'] : 0;
        $interessi = isset($_POST['interessi']) && $_POST['interessi'] !== '' ? (float)$_POST['interessi'] : 0;
        $pagamento = $_POST['pagamento'];
        $note = $_POST['note'] ?? null;
        $data_registrazione = $_POST['data_registrazione'] ?? date('Y-m-d');

        // Inserisci cliente
        $stmt = $pdo->prepare("INSERT INTO clienti (
            nome, telefono, email, indirizzo, codice_fiscale, data_nascita,
            acquisto, importo, acconto, interessi, pagamento, note, data_registrazione
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nome, $telefono, $email, $indirizzo, $codice_fiscale, $data_nascita,
            $acquisto, $importo, $acconto, $interessi, $pagamento, $note, $data_registrazione
        ]);

        $cliente_id = $pdo->lastInsertId();

        // Se pagamento rateale interno
        if ($pagamento === 'finanziamento_interno') {
            $rata = isset($_POST['rata_mensile']) ? (float)$_POST['rata_mensile'] : 0;
            $durata = isset($_POST['durata']) ? (int)$_POST['durata'] : 0;
            $oggi = new DateTime($data_registrazione);

            for ($i = 0; $i < $durata; $i++) {
                $data_scadenza = (clone $oggi)->modify("+$i months");
                $stmt = $pdo->prepare("INSERT INTO rate (
                    cliente_id, numero_rata, importo, data_scadenza, pagata, note
                ) VALUES (?, ?, ?, ?, 0, ?)");
                $stmt->execute([
                    $cliente_id,
                    $i + 1,
                    $rata,
                    $data_scadenza->format('Y-m-d'),
                    "Rata " . ($i + 1) . " - Finanziamento Interno"
                ]);
            }
        } elseif ($pagamento === 'rateale_compass') {
            $stmt = $pdo->prepare("INSERT INTO rate (
                cliente_id, numero_rata, importo, data_scadenza, pagata, note
            ) VALUES (?, 1, ?, ?, 1, ?)");
            $stmt->execute([
                $cliente_id,
                $importo,
                $data_registrazione,
                'Pagamento Compass completato'
            ]);
        }

        // ✅ REDIRECT FUNZIONANTE: deve venire PRIMA di qualsiasi HTML
        header("Location: clienti.php");
        exit;

    } catch (PDOException $e) {
        echo "<pre style='color:red;'>Errore DB: " . $e->getMessage() . "</pre>";
        exit;
    }
}
