<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
    foreach ($_POST['selected_ids'] as $id) {
        $id = (int)$id;
        $stato = isset($_POST['stato'][$id]) ? (int)$_POST['stato'][$id] : 0;
        $note = isset($_POST['note'][$id]) ? trim($_POST['note'][$id]) : '';

        $stmt = $pdo->prepare("UPDATE rate SET pagata = ?, note = ? WHERE id = ?");
        $stmt->execute([$stato, $note, $id]);
    }
}

header("Location: rate_massive.php?aggiornato=1");
exit;
?>
