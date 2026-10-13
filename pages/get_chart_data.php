<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$labels = [];
$values = [];

// Calcola ultimi 6 mesi (es: Apr 2025, Mar 2025, ...)
for ($i = 5; $i >= 0; $i--) {
    $mese = date('Y-m', strtotime("-$i months"));
    $mese_label = date('M Y', strtotime($mese)); // Apr 2025, ecc.

    $stmt = $pdo->prepare("SELECT SUM(importo) FROM rate WHERE pagata = 1 AND DATE_FORMAT(data_pagamento, '%Y-%m') = ?");
    $stmt->execute([$mese]);
    $totale = $stmt->fetchColumn() ?: 0;

    $labels[] = $mese_label;
    $values[] = round($totale, 2);
}

// Output JSON per Chart.js
header('Content-Type: application/json');
echo json_encode([
    "labels" => $labels,
    "values" => $values
]);
