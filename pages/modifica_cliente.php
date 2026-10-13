<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID mancante.";
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM clienti WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    echo "Cliente non trovato.";
    exit;
}
?>

<div class="modal-content-inner">
  <span class="close" onclick="document.getElementById('editClienteModal').style.display='none'; document.body.style.overflow='auto';">&times;</span>
  <h2>✏️ Modifica Cliente</h2>
  <form method="post" action="salva_modifica_cliente.php">
    <input type="hidden" name="id" value="<?= $cliente['id'] ?>">

    <label>Nome: <input type="text" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required></label>
    <label>Città: <input type="text" name="citta" value="<?= htmlspecialchars($cliente['citta'] ?? '') ?>"></label>
    <label>Telefono: <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>"></label>
    <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>"></label>
    <label>Indirizzo: <input type="text" name="indirizzo" value="<?= htmlspecialchars($cliente['indirizzo']) ?>"></label>
    <label>Codice Fiscale: <input type="text" name="codice_fiscale" value="<?= htmlspecialchars($cliente['codice_fiscale']) ?>"></label>
    <label>Data di Nascita: <input type="date" name="data_nascita" value="<?= $cliente['data_nascita'] ?>"></label>
    <label>Cosa ha acquistato: <input type="text" name="acquisto" value="<?= htmlspecialchars($cliente['acquisto']) ?>"></label>
    <label>Importo Totale: <input type="number" name="importo" step="0.01" value="<?= $cliente['importo'] ?>"></label>
    <label>Acconto: <input type="number" name="acconto" step="0.01" value="<?= $cliente['acconto'] ?>"></label>
    <label>Interessi: <input type="number" name="interessi" step="0.01" value="<?= $cliente['interessi'] ?>"></label>

    <label>Tipo di Pagamento:
      <select name="pagamento" required>
        <option value="">-- Seleziona --</option>
        <option value="immediato" <?= $cliente['pagamento'] === 'immediato' ? 'selected' : '' ?>>Contanti / Carta / Bonifico</option>
        <option value="rateale_compass" <?= $cliente['pagamento'] === 'rateale_compass' ? 'selected' : '' ?>>Compass</option>
        <option value="finanziamento_interno" <?= $cliente['pagamento'] === 'finanziamento_interno' ? 'selected' : '' ?>>Finanziamento Interno</option>
      </select>
    </label>

    <label>Note: <textarea name="note"><?= htmlspecialchars($cliente['note']) ?></textarea></label>
    <label>Data registrazione: <input type="date" name="data_registrazione" value="<?= $cliente['data_registrazione'] ?>"></label>
    <button type="submit" class="btn">Salva Modifiche</button>
  </form>
</div>
