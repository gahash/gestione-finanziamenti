<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=lead_export.csv");

$output = fopen("php://output", "w");
fputcsv($output, ['Nome', 'Città', 'Email', 'Telefono', 'Data Ricontatto', 'Esito']);

$stmt = $pdo->query("SELECT nome, citta, email, telefono, data_ricontatto, esito FROM lead ORDER BY data_ricontatto ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
exit;
