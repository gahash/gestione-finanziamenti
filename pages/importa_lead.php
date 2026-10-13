<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if (!file_exists($file)) {
        header("Location: lead.php?error=missing_file");
        exit;
    }

    if (($handle = fopen($file, "r")) !== false) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $row = 0;
        $imported = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($row++ === 0) continue; // salta intestazione

            // Pulisce e assegna valori
            $nome = trim($data[0] ?? '');
            $citta = trim($data[1] ?? '');
            $email = trim($data[2] ?? '');
            $telefono = trim($data[3] ?? '');
            $data_ricontatto = trim($data[4] ?? '');
            $esito = trim($data[5] ?? '');

            // Validazione minima
            if (empty($nome) || empty($data_ricontatto)) continue;

            $stmt = $pdo->prepare("INSERT INTO lead (nome, citta, email, telefono, data_ricontatto, esito)
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $citta, $email, $telefono, $data_ricontatto, $esito]);

            $imported++;
        }

        fclose($handle);
        header("Location: lead.php?import=$imported");
        exit;
    } else {
        header("Location: lead.php?error=csv_open");
        exit;
    }
} else {
    header("Location: lead.php?error=invalid_request");
    exit;
}
