<?php
require_once "../includes/auth.php";
require_once "../config/db.php";
require '../vendor/autoload.php'; // Assicurati che il path sia corretto

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Filtro opzionale mese e anno
$mese = isset($_GET['mese']) ? (int)$_GET['mese'] : null;
$anno = isset($_GET['anno']) ? (int)$_GET['anno'] : null;

$query = "
  SELECT c.nome, r.numero_rata, r.importo, r.data_scadenza, r.pagata, r.note, c.pagamento
  FROM rate r
  JOIN clienti c ON r.cliente_id = c.id
  WHERE c.pagamento IN ('finanziamento_interno', 'rateale_compass')
";

$params = [];
if ($mese && $anno) {
    $query .= " AND MONTH(r.data_scadenza) = ? AND YEAR(r.data_scadenza) = ?";
    $params = [$mese, $anno];
}

$query .= " ORDER BY c.nome ASC, r.data_scadenza ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crea il foglio Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Intestazioni
$headers = ['Cliente', 'Numero Rata', 'Importo', 'Data Scadenza', 'Stato', 'Note', 'Tipo Pagamento'];
$sheet->fromArray($headers, NULL, 'A1');

// Popola i dati
$row = 2;
foreach ($results as $r) {
    $sheet->setCellValue("A$row", $r['nome']);
    $sheet->setCellValue("B$row", $r['numero_rata']);
    $sheet->setCellValue("C$row", number_format($r['importo'], 2, '.', ''));
    $sheet->setCellValue("D$row", $r['data_scadenza']);
    $sheet->setCellValue("E$row", $r['pagata'] ? 'Pagata' : 'Da pagare');
    $sheet->setCellValue("F$row", $r['note']);
    $sheet->setCellValue("G$row", $r['pagamento'] === 'finanziamento_interno' ? 'Finanziamento Interno' : 'Compass');
    $row++;
}

// Nome file dinamico
$filename = "scadenziario_pagamenti";
if ($mese && $anno) {
    $filename .= "_{$anno}_{$mese}";
}
$filename .= ".xlsx";

// Header per download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Scrivi ed esegui
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
