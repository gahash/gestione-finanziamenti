<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $lead_id            = $_POST['lead_id'];
        $nome               = trim($_POST['nome']);
        $citta              = trim($_POST['citta']);
        $email              = trim($_POST['email']);
        $telefono           = trim($_POST['telefono']);
        $indirizzo          = trim($_POST['indirizzo'] ?? '');
        $codice_fiscale     = trim($_POST['codice_fiscale'] ?? '');
        $data_nascita       = $_POST['data_nascita'] ?? null;
        $data_registrazione = $_POST['data_registrazione'];
        $acquisto           = trim($_POST['acquisto'] ?? '');
        $importo            = isset($_POST['importo']) ? (float)$_POST['importo'] : 0;
        $acconto            = isset($_POST['acconto']) ? (float)$_POST['acconto'] : 0;
        $interessi          = isset($_POST['interessi']) ? (float)$_POST['interessi'] : 0;
        $pagamento          = $_POST['pagamento'] ?? '';
        $note               = trim($_POST['note'] ?? '');
        $durata             = isset($_POST['durata']) ? (int)$_POST['durata'] : 12;
        $rata_mensile       = (isset($_POST['rata_mensile']) && $_POST['rata_mensile']!=='')
                              ? (float)$_POST['rata_mensile'] : null;

        if (!$nome || !$data_registrazione) {
            header("Location: clienti.php?error=missing");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO clienti (
            nome,citta,email,telefono,indirizzo,codice_fiscale,
            data_nascita,data_registrazione,acquisto,importo,acconto,pagamento,note
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $nome,$citta,$email,$telefono,$indirizzo,$codice_fiscale,
            $data_nascita,$data_registrazione,$acquisto,$importo,$acconto,$pagamento,$note
        ]);

        $cliente_id = $pdo->lastInsertId();
        $residuo    = $importo - $acconto + $interessi;

        if ($acconto > 0) {
            $stmt = $pdo->prepare("INSERT INTO rate (cliente_id,numero_rata,importo,data_scadenza,pagata,note) VALUES (?,?,?,?,1,'Acconto')");
            $stmt->execute([$cliente_id,0,$acconto,$data_registrazione]);
        }
        if ($pagamento==='finanziamento_interno' && $residuo>0) {
            $rata   = $rata_mensile ?? round($residuo / $durata,2);
            $inizio = new DateTime($data_registrazione);
            for($i=0;$i<$durata;$i++){
                $scad = (clone $inizio)->modify("+{$i} months")->format('Y-m-d');
                $stmt = $pdo->prepare("INSERT INTO rate (cliente_id,numero_rata,importo,data_scadenza,pagata,note) VALUES (?,?,?,?,0,?)");
                $stmt->execute([$cliente_id,$i+1,$rata,$scad,"Rata ".($i+1)]);
            }
        } elseif ($pagamento==='rateale_compass' && $residuo>0) {
            $stmt = $pdo->prepare("INSERT INTO rate (cliente_id,numero_rata,importo,data_scadenza,pagata,note) VALUES (?,?,?,?,1,'Pagamento Compass')");
            $stmt->execute([$cliente_id,1,$residuo,$data_registrazione]);
        }
        // Elimina il lead originale
        $stmt = $pdo->prepare("DELETE FROM lead WHERE id = ?");
        $stmt->execute([$lead_id]);
        header("Location: clienti.php?success=converted");
        exit;

    } catch (PDOException $e) {
        echo "<pre>Errore conversione: " . $e->getMessage() . "</pre>";
        exit;
    }
}
header("Location: clienti.php");
exit;
