<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if (!isset($_GET['id'])) {
  echo "<div style='padding:20px;'>ID mancante.</div>";
  exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM lead WHERE id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
  echo "<div style='padding:20px;'>Lead non trovato.</div>";
  exit;
}
?>

<div class="modal-content-inner">
  <span class="close" onclick="closeEditModal()">&times;</span>
  <h2>✏️ Modifica Lead</h2>
  <form method="post" action="salva_modifica_lead.php" class="form-layout">
    <input type="hidden" name="id" value="<?= $lead['id'] ?>">

    <label>Nome:</label>
    <input type="text" name="nome" value="<?= htmlspecialchars($lead['nome']) ?>" required>

    <label>Città:</label>
    <input type="text" name="citta" value="<?= htmlspecialchars($lead['citta']) ?>">

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($lead['email']) ?>">

    <label>Telefono:</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($lead['telefono']) ?>">

    <label>Data Ricontatto:</label>
    <input type="date" name="data_ricontatto" value="<?= $lead['data_ricontatto'] ?>">

    <label>Esito:</label>
    <select name="esito">
      <option value="">-- Seleziona --</option>
      <option value="ricontattare" <?= $lead['esito'] === 'ricontattare' ? 'selected' : '' ?>>Ricontattare</option>
      <option value="non interessato" <?= $lead['esito'] === 'non interessato' ? 'selected' : '' ?>>Non interessato</option>
      <option value="acquistato" <?= $lead['esito'] === 'acquistato' ? 'selected' : '' ?>>Ha acquistato</option>
    </select>

    <div style="margin-top: 20px; text-align: center;">
      <button type="submit" class="btn">💾 Salva Modifiche</button>
    </div>
  </form>
</div>
