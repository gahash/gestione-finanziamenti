<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=Scadenziario_Compass.xlsx");
header("Cache-Control: max-age=0");

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Intestazioni
$sheet->setCellValue('A1', 'Cliente')
      ->setCellValue('B1', 'Importo Totale')
      ->setCellValue('C1', 'Incassato')
      ->setCellValue('D1', 'Da Incassare')
      ->setCellValue('E1', 'Ultima Rata')
      ->setCellValue('F1', 'Data Registrazione');

// Preleva dati solo Compass
$stmt = $pdo->prepare("SELECT c.id, c.nome, c.data_registrazione,
    SUM(r.importo) AS totale,
    SUM(CASE WHEN r.pagata = 1 THEN r.importo ELSE 0 END) AS incassato,
    SUM(CASE WHEN r.pagata = 0 THEN r.importo ELSE 0 END) AS da_incassare,
    MAX(r.data_scadenza) AS ultima_rata
FROM clienti c
JOIN rate r ON r.cliente_id = c.id
WHERE c.pagamento = 'rateale_compass'
GROUP BY c.id, c.nome, c.data_registrazione
ORDER BY c.nome ASC");

$stmt->execute();
$righe = $stmt->fetchAll(PDO::FETCH_ASSOC);

$row = 2;
foreach ($righe as $r) {
    $sheet->setCellValue('A' . $row, $r['nome'])
          ->setCellValue('B' . $row, number_format($r['totale'], 2, ',', '.'))
          ->setCellValue('C' . $row, number_format($r['incassato'], 2, ',', '.'))
          ->setCellValue('D' . $row, number_format($r['da_incassare'], 2, ',', '.'))
          ->setCellValue('E' . $row, date('d/m/Y', strtotime($r['ultima_rata'])))
          ->setCellValue('F' . $row, date('d/m/Y', strtotime($r['data_registrazione'])));
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
