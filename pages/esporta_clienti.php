<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=clienti_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Intestazioni CSV
fputcsv($output, [
    'ID',
    'Nome',
    'Città',
    'Telefono',
    'Email',
    'Indirizzo',
    'Codice Fiscale',
    'Data Nascita',
    'Acquisto',
    'Importo',
    'Acconto',
    'Interessi',
    'Pagamento',
    'Note',
    'Data Registrazione'
]);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT * FROM clienti ORDER BY nome ASC");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['nome'],
            $row['citta'],
            $row['telefono'],
            $row['email'],
            $row['indirizzo'],
            $row['codice_fiscale'],
            $row['data_nascita'],
            $row['acquisto'],
            $row['importo'],
            $row['acconto'],
            $row['interessi'],
            $row['pagamento'],
            $row['note'],
            $row['data_registrazione']
        ]);
    }

} catch (PDOException $e) {
    fputcsv($output, ['Errore durante l\'esportazione: ' . $e->getMessage()]);
}

fclose($output);
exit;
?>
