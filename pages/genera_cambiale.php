<?php
// Prototipo: richiede la libreria FPDF in ../fpdf/ e l'immagine ../img/cambiale.jpg (non inclusi). Dati di esempio fittizi.
require_once "../includes/auth.php";
require_once('../vendor/autoload.php'); // se usi composer per FPDF
require('../fpdf/fpdf.php'); // se invece usi la classica

$pdf = new FPDF('L', 'mm', [210, 99]); // orientamento e dimensioni
$pdf->AddPage();

// Sfondo immagine
$pdf->Image('../img/cambiale.jpg', 0, 0, 210, 99);

// Simuliamo dati
$cliente = "SIG. Mario Rossi";
$indirizzo = "Via Roma n.1";
$localita = "Milano (MI)";
$importo = "60,00";
$data_scadenza = "01/01/2025";

// Scrittura testo
$pdf->SetFont('Arial','',12);
$pdf->SetXY(170, 12); $pdf->Cell(30, 10, $importo, 0, 0, 'C');
$pdf->SetXY(90, 22);  $pdf->Cell(40, 10, $data_scadenza, 0, 0);
$pdf->SetXY(85, 34);  $pdf->Cell(60, 10, "SESSANTA/00", 0, 0, 'C');
$pdf->SetXY(30, 56);  $pdf->Cell(100, 10, $cliente, 0, 0);
$pdf->SetXY(30, 66);  $pdf->Cell(100, 10, $indirizzo, 0, 0);
$pdf->SetXY(30, 76);  $pdf->Cell(60, 10, $localita, 0, 0);

$pdf->Output();
