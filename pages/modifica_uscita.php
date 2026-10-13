<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if (!isset($_GET['id'])) {
    echo "ID mancante.";
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM uscite WHERE id = ?");
$stmt->execute([$id]);
$uscita = $stmt->fetch();

if (!$uscita) {
    echo "Uscita non trovata.";
    exit;
}
?>

<div class="modal-content-inner">
  <span class="close" onclick="closeEditModal()">&times;</span>
  <h3>✏️ Modifica Spesa</h3>
  <form id="formModificaUscita">
    <input type="hidden" name="id" value="<?= $uscita['id'] ?>">

    <label>Descrizione: <input type="text" name="descrizione" value="<?= htmlspecialchars($uscita['descrizione']) ?>" required></label>
    <label>Importo: <input type="number" step="0.01" name="importo" value="<?= $uscita['importo'] ?>" required></label>
    <label>Note: <input type="text" name="note" value="<?= htmlspecialchars($uscita['note']) ?>"></label>
    <label>Tipo:
      <select name="tipo">
        <option value="una_tantum" <?= $uscita['tipo'] === 'una_tantum' ? 'selected' : '' ?>>Una Tantum</option>
        <option value="ricorrente" <?= $uscita['tipo'] === 'ricorrente' ? 'selected' : '' ?>>Ricorrente</option>
      </select>
    </label>
    <label>Data: <input type="date" name="data_uscita" value="<?= $uscita['data_uscita'] ?>" required></label>
    <button type="submit" class="btn">Salva Modifiche</button>
  </form>
</div>
