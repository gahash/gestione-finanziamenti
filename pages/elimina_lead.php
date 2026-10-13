<?php
// elimina_lead.php
require_once "../includes/auth.php";
require_once "../config/db.php";

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM lead WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: lead.php?success=1");
exit;
?>
