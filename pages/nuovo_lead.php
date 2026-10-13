<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $nome = trim($_POST['nome']);
        $citta = trim($_POST['citta']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono']);
        $data_ricontatto = $_POST['data_ricontatto'] ?? null;
        $esito = $_POST['esito'] ?? '';

        if (!$nome) {
            header("Location: lead.php?error=missing");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO lead (nome, citta, email, telefono, data_ricontatto, esito)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $citta, $email, $telefono, $data_ricontatto, $esito]);
        header("Location: lead.php?success=1");
        exit;

    } catch (PDOException $e) {
        header("Location: lead.php?error=db");
        exit;
    }
} else {
    header("Location: lead.php");
    exit;
}
