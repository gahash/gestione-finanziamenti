<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Dati principali
        $importo_totale = isset($_POST['importo']) ? (float)$_POST['importo'] : 0;
        $acconto = isset($_POST['acconto']) ? (float)$_POST['acconto'] : 0;
        $interessi = isset($_POST['interessi']) ? (float)$_POST['interessi'] : 0;
        $pagamento = $_POST['pagamento'] ?? 'immediato';
        $data_registrazione = $_POST['data_registrazione'] ?? date('Y-m-d');
        $citta = $_POST['citta'] ?? null;

        // Calcolo residuo comprensivo di interessi
        $importo_residuo = ($importo_totale - $acconto) + $interessi;

        // Salvataggio cliente
        $stmt = $pdo->prepare("INSERT INTO clienti (
            nome, telefono, email, indirizzo, codice_fiscale, data_nascita,
            acquisto, importo, acconto, interessi, pagamento, note, data_registrazione, citta
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $_POST['nome'],
            $_POST['telefono'] ?? null,
            $_POST['email'] ?? null,
            $_POST['indirizzo'] ?? null,
            $_POST['codice_fiscale'] ?? null,
            $_POST['data_nascita'] ?? null,
            $_POST['acquisto'] ?? null,
            $importo_totale,
            $acconto,
            $interessi,
            $pagamento,
            $_POST['note'] ?? null,
            $data_registrazione,
            $citta
        ]);

        $cliente_id = $pdo->lastInsertId();

        // ✅ Acconto come rata 0 già pagata (corretto ordine parametri)
        if ($acconto > 0) {
            $pdo->prepare("INSERT INTO rate (cliente_id, numero_rata, importo, data_scadenza, pagata, note)
                VALUES (?, ?, ?, ?, 1, 'Acconto')")
                ->execute([$cliente_id, 0, $acconto, $data_registrazione]);
        }

        // ✅ Gestione rate secondo tipo pagamento
        if ($pagamento === 'finanziamento_interno') {
            $durata = (int)($_POST['durata'] ?? 1);
            $rata = isset($_POST['rata_mensile']) && $_POST['rata_mensile'] !== ''
                ? (float)$_POST['rata_mensile']
                : round($importo_residuo / max($durata, 1), 2);

            $oggi = new DateTime($data_registrazione);
            for ($i = 0; $i < $durata; $i++) {
                $data = (clone $oggi)->modify("+{$i} months")->format('Y-m-d');
                $pdo->prepare("INSERT INTO rate (cliente_id, numero_rata, importo, data_scadenza, pagata, note)
                    VALUES (?, ?, ?, ?, 0, ?)")
                    ->execute([$cliente_id, $i + 1, $rata, $data, "Rata " . ($i + 1)]);
            }

        } elseif ($pagamento === 'rateale_compass') {
            $pdo->prepare("INSERT INTO rate (cliente_id, numero_rata, importo, data_scadenza, pagata, note)
                VALUES (?, ?, ?, ?, 1, 'Pagamento Compass')")
                ->execute([$cliente_id, 1, $importo_residuo, $data_registrazione]);

        } elseif ($pagamento === 'immediato') {
            $pdo->prepare("INSERT INTO rate (cliente_id, numero_rata, importo, data_scadenza, pagata, note)
                VALUES (?, ?, ?, ?, 1, 'Pagamento Immediato')")
                ->execute([$cliente_id, 1, $importo_residuo, $data_registrazione]);
        }

        header("Location: clienti.php?success=1");
        exit;

    } catch (PDOException $e) {
        echo "<pre>Errore DB: " . $e->getMessage() . "</pre>";
    }
} else {
    header("Location: clienti.php");
    exit;
}
?>
