<?php
require_once "../includes/auth.php";
require_once "../config/db.php";
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Preleva tutti i pagamenti completati
$stmt = $pdo->prepare("
    SELECT c.nome, c.pagamento, r.numero_rata, r.importo, r.data_scadenza, r.pagata, r.note
    FROM clienti c
    LEFT JOIN rate r ON c.id = r.cliente_id
    WHERE (c.pagamento = 'immediato') OR (r.pagata = 1)
    ORDER BY c.nome ASC, r.data_scadenza ASC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crea il file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Intestazioni
$headers = ['Cliente', 'Tipo Pagamento', 'Numero Rata', 'Importo', 'Data Scadenza', 'Stato', 'Note'];
$sheet->fromArray($headers, NULL, 'A1');

// Inserimento dati
$row = 2;
foreach ($results as $r) {
    $sheet->setCellValue("A$row", $r['nome']);
    $sheet->setCellValue("B$row", $r['pagamento']);
    $sheet->setCellValue("C$row", $r['numero_rata'] ?? '—');
    $sheet->setCellValue("D$row", number_format($r['importo'], 2, '.', ''));
    $sheet->setCellValue("E$row", $r['data_scadenza'] ?? '—');
    $sheet->setCellValue("F$row", $r['pagata'] ? '✅ Pagata' : '—');
    $sheet->setCellValue("G$row", $r['note']);
    $row++;
}

// Imposta intestazioni HTTP per scaricamento
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="incassi_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: max-age=0');

// Salva e invia
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
