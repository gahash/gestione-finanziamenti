<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if (!isset($_GET['id'])) {
    echo "<p>❌ ID rata mancante.</p>";
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT r.*, c.nome FROM rate r JOIN clienti c ON r.cliente_id = c.id WHERE r.id = ?");
$stmt->execute([$id]);
$rata = $stmt->fetch();

if (!$rata) {
    echo "<p>❌ Rata non trovata.</p>";
    exit;
}
?>

<h2>✏️ Modifica Rata</h2>
<form method="post" action="salva_rata.php">
  <input type="hidden" name="id" value="<?= $rata['id'] ?>">

  <label>Cliente:
    <input type="text" value="<?= htmlspecialchars($rata['nome']) ?>" disabled>
  </label>

  <label>Numero Rata:
    <input type="number" name="numero_rata" value="<?= $rata['numero_rata'] ?>" required>
  </label>

  <label>Importo:
    <input type="number" step="0.01" name="importo" value="<?= $rata['importo'] ?>" required>
  </label>

  <label>Data Scadenza:
    <input type="date" name="data_scadenza" value="<?= $rata['data_scadenza'] ?>" required>
  </label>

  <label>Stato Pagamento:
    <select name="pagata">
      <option value="0" <?= $rata['pagata'] == 0 ? 'selected' : '' ?>>❌ Da pagare</option>
      <option value="1" <?= $rata['pagata'] == 1 ? 'selected' : '' ?>>✅ Pagata</option>
    </select>
  </label>

  <label>Note:
    <textarea name="note"><?= htmlspecialchars($rata['note']) ?></textarea>
  </label>

  <button type="submit" class="btn">Salva Modifiche</button>
</form>
