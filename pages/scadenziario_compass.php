<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Filtri date
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$where = "WHERE c.pagamento = 'rateale_compass'";
$params = [];

if ($start_date && $end_date) {
    $where .= " AND r.data_scadenza BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date'] = $end_date;
}

// Query dettagli rate
$stmt = $pdo->prepare("SELECT c.nome, c.citta, c.data_registrazione, r.numero_rata, r.importo, r.data_scadenza, r.pagata, r.cliente_id
                       FROM clienti c
                       JOIN rate r ON c.id = r.cliente_id
                       $where
                       ORDER BY c.nome ASC, r.data_scadenza ASC");
$stmt->execute($params);
$scadenze = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query riepilogo ultimo pagamento per ogni cliente Compass
$riepilogoStmt = $pdo->query("SELECT c.nome, MAX(r.data_scadenza) AS ultima_scadenza
                              FROM clienti c
                              JOIN rate r ON c.id = r.cliente_id
                              WHERE c.pagamento = 'rateale_compass'
                              GROUP BY c.id, c.nome
                              ORDER BY c.nome ASC");
$riepiloghi = $riepilogoStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card">
    <div class="top-bar" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; align-items: center;">
      <h2>📆 Scadenziario Compass <?= ($start_date && $end_date) ? "($start_date → $end_date)" : "" ?></h2>
      <form method="get" style="display: flex; gap: 10px; align-items: center;">
        <label>Dal: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"></label>
        <label>Al: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"></label>
        <button type="submit" class="btn">Filtra</button>
      </form>
      <a href="esporta_scadenziario_compass.php" class="btn btn-export" style="background-color:#4CAF50;color:white;padding:8px 12px;border-radius:4px;text-decoration:none;font-weight:bold;">
        📅 Esporta Excel
      </a>
    </div>

    <table class="clienti-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Città</th>
          <th>Numero Rata</th>
          <th>Importo</th>
          <th>Data Scadenza</th>
          <th>Stato</th>
          <th>Data Registrazione</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($scadenze as $s): ?>
        <?php
          $scaduta = !$s['pagata'] && strtotime($s['data_scadenza']) < time();
        ?>
        <tr>
          <td><?= htmlspecialchars($s['nome']) ?></td>
          <td><?= htmlspecialchars($s['citta']) ?></td>
          <td><?= htmlspecialchars($s['numero_rata']) ?></td>
          <td>€<?= number_format($s['importo'], 2, ',', '.') ?></td>
          <td><?= date('d/m/Y', strtotime($s['data_scadenza'])) ?></td>
          <td>
            <?php if ($s['pagata']): ?>
              <span class="stato pagata">✅ Pagata</span>
            <?php elseif ($scaduta): ?>
              <span class="stato non-pagata" style="color:red; font-weight:bold;">⚠️ Scaduta</span>
            <?php else: ?>
              <span class="stato non-pagata">❌ Da pagare</span>
            <?php endif; ?>
          </td>
          <td><?= date('d/m/Y', strtotime($s['data_registrazione'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <br><hr><br>

    <h3>🧾 Riepilogo – Ultima rata prevista per ciascun cliente Compass</h3>
    <table class="clienti-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Ultima Rata Compass</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($riepiloghi as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['nome']) ?></td>
          <td><?= date('d/m/Y', strtotime($r['ultima_scadenza'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>

<?php require_once "../includes/footer.php"; ?>
