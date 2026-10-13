<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

// Recupera tutte le rate
$stmt = $pdo->query("
  SELECT r.id, r.cliente_id, r.numero_rata, r.importo, r.data_scadenza, r.pagata, r.note, c.nome
  FROM rate r
  JOIN clienti c ON r.cliente_id = c.id
  ORDER BY r.data_scadenza ASC
");
$rate = $stmt->fetchAll();
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card full-width">
    <div class="top-bar">
      <h2>📑 Gestione Massiva Rate</h2>
    </div>

    <form method="post" action="salva_massive_rate.php">
      <table class="clienti-table">
        <thead>
          <tr>
            <th>✔</th>
            <th>Cliente</th>
            <th>Rata</th>
            <th>Importo</th>
            <th>Scadenza</th>
            <th>Stato</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rate as $r): ?>
            <tr>
              <td>
                <input type="checkbox" name="selected_ids[]" value="<?= $r['id'] ?>">
              </td>
              <td><?= htmlspecialchars($r['nome']) ?></td>
              <td><?= $r['numero_rata'] ?></td>
              <td>€<?= number_format($r['importo'], 2, ',', '.') ?></td>
              <td><?= date('d/m/Y', strtotime($r['data_scadenza'])) ?></td>
              <td>
                <select name="stato[<?= $r['id'] ?>]">
                  <option value="0" <?= $r['pagata'] == 0 ? 'selected' : '' ?>>❌ Da pagare</option>
                  <option value="1" <?= $r['pagata'] == 1 ? 'selected' : '' ?>>✅ Pagata</option>
                </select>
              </td>
              <td>
                <input type="text" name="note[<?= $r['id'] ?>]" value="<?= htmlspecialchars($r['note']) ?>">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <br>
      <button type="submit" class="btn">💾 Salva Selezionati</button>
    </form>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>
