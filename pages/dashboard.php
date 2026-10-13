<?php
error_reporting(E_ALL);

require_once "../includes/auth.php";
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Totali
$tot_clienti = $pdo->query("SELECT COUNT(*) FROM clienti")->fetchColumn();
$tot_lead = $pdo->query("SELECT COUNT(*) FROM lead")->fetchColumn();
$incassato = $pdo->query("SELECT SUM(importo) FROM rate WHERE pagata = 1")->fetchColumn() ?: 0;
$da_incassare = $pdo->query("SELECT SUM(importo) FROM rate WHERE pagata = 0")->fetchColumn() ?: 0;

// Statistiche ultimi 6 mesi
$mesi = [];
$clienti_per_mese = [];
$lead_per_mese = [];
$incassato_per_mese = [];
$da_incassare_per_mese = [];

for ($i = 5; $i >= 0; $i--) {
    $mese = date('Y-m', strtotime("-$i months"));
    $mesi[] = date('M Y', strtotime($mese));

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clienti WHERE DATE_FORMAT(data_registrazione, '%Y-%m') = ?");
    $stmt->execute([$mese]);
    $clienti_per_mese[] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lead WHERE DATE_FORMAT(data_ricontatto, '%Y-%m') = ?");
    $stmt->execute([$mese]);
    $lead_per_mese[] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT SUM(importo) FROM rate WHERE pagata = 1 AND DATE_FORMAT(data_pagamento, '%Y-%m') = ?");
    $stmt->execute([$mese]);
    $incassato_per_mese[] = (float) ($stmt->fetchColumn() ?: 0);

    $stmt = $pdo->prepare("SELECT SUM(importo) FROM rate WHERE pagata = 0 AND DATE_FORMAT(data_scadenza, '%Y-%m') = ?");
    $stmt->execute([$mese]);
    $da_incassare_per_mese[] = (float) ($stmt->fetchColumn() ?: 0);
}

// Gestione filtro storico pagamenti
$filtro = $_GET['filtro'] ?? 'mese';
$data_da = $_GET['data_da'] ?? '';
$data_a = $_GET['data_a'] ?? '';

if (!empty($data_da) && !empty($data_a)) {
    $query = "SELECT c.nome, r.importo, r.data_scadenza, r.pagata
              FROM rate r
              JOIN clienti c ON r.cliente_id = c.id
              WHERE r.data_scadenza BETWEEN :data_da AND :data_a
              ORDER BY r.data_scadenza DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':data_da', $data_da);
    $stmt->bindValue(':data_a', $data_a);
    $stmt->execute();
    $rate_filtrate = $stmt->fetchAll();
} else {
    if ($filtro === 'giorno') {
        $query = "SELECT c.nome, r.importo, r.data_scadenza, r.pagata
                  FROM rate r
                  JOIN clienti c ON r.cliente_id = c.id
                  WHERE r.data_scadenza = CURDATE()
                  ORDER BY r.data_scadenza DESC";
    } else {
        $query = "SELECT c.nome, r.importo, r.data_scadenza, r.pagata
                  FROM rate r
                  JOIN clienti c ON r.cliente_id = c.id
                  WHERE MONTH(r.data_scadenza) = MONTH(CURDATE()) AND YEAR(r.data_scadenza) = YEAR(CURDATE())
                  ORDER BY r.data_scadenza DESC";
    }
    $rate_filtrate = $pdo->query($query)->fetchAll();
}

// Calcolo spese e incasso netto
if (!empty($data_da) && !empty($data_a)) {
    $stmt = $pdo->prepare("SELECT SUM(importo) FROM uscite WHERE data_uscita BETWEEN :da AND :a");
    $stmt->execute([':da' => $data_da, ':a' => $data_a]);
} elseif ($filtro === 'giorno') {
    $stmt = $pdo->query("SELECT SUM(importo) FROM uscite WHERE data_uscita = CURDATE()");
} else {
    $stmt = $pdo->query("SELECT SUM(importo) FROM uscite WHERE MONTH(data_uscita) = MONTH(CURDATE()) AND YEAR(data_uscita) = YEAR(CURDATE())");
}
$spese = $stmt->fetchColumn() ?: 0;
$incasso_netto = $incassato - $spese;
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Gestione Finanziamenti</title>
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include "../includes/topbar.php"; ?>

<section class="dashboard-container">

  <!-- 🟢 STORICO PAGAMENTI -->
  <div class="card full-width">
    <div class="card-header">
      <h4>Storico Pagamenti</h4>
      <div class="dashboard-filtri">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <a href="?filtro=giorno" class="<?= ($filtro === 'giorno' && !$data_da) ? 'active' : '' ?>">Oggi</a>
          <a href="?filtro=mese" class="<?= ($filtro === 'mese' && !$data_da) ? 'active' : '' ?>">Questo mese</a>
          <input type="date" name="data_da" value="<?= htmlspecialchars($data_da) ?>">
          <input type="date" name="data_a" value="<?= htmlspecialchars($data_a) ?>">
          <button type="submit" class="btn">Filtra Date</button>
        </form>
      </div>
    </div>

    <div class="card-content">
      <table class="tabella-risultati">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Importo</th>
            <th>Data</th>
            <th>Stato</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rate_filtrate)): ?>
            <tr><td colspan="4" class="empty-row">Nessun dato disponibile</td></tr>
          <?php else: ?>
            <?php foreach ($rate_filtrate as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['nome']) ?></td>
                <td>€<?= number_format($r['importo'], 2, ',', '.') ?></td>
                <td><?= date('d/m/Y', strtotime($r['data_scadenza'])) ?></td>
                <td>
                  <span class="stato <?= $r['pagata'] ? 'pagata' : 'non-pagata' ?>">
                    <?= $r['pagata'] ? 'Incassata' : 'Da Incassare' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 🔽 RIEPILOGO CON CARDS -->
  <div class="card-grid" style="margin-top: 40px;">
    <div class="card">
      <h4>Clienti</h4>
      <h3><?= $tot_clienti ?></h3>
      <canvas id="clientiChart"></canvas>
    </div>
    <div class="card">
      <h4>Lead</h4>
      <h3><?= $tot_lead ?></h3>
      <canvas id="leadChart"></canvas>
    </div>
    <div class="card">
      <h4>Incassato</h4>
      <h3>€<?= number_format($incassato, 2, ',', '.') ?></h3>
      <canvas id="incassatoChart"></canvas>
    </div>
    <div class="card">
      <h4>Da Incassare</h4>
      <h3>€<?= number_format($da_incassare, 2, ',', '.') ?></h3>
      <canvas id="daIncassareChart"></canvas>
    </div>
    <div class="card">
      <h4>Spese</h4>
      <h3 style="color:#F44336;">- €<?= number_format($spese, 2, ',', '.') ?></h3>
    </div>
    <div class="card">
      <h4>Incasso Netto</h4>
      <h3 style="color:#4CAF50;">€<?= number_format($incasso_netto, 2, ',', '.') ?></h3>
    </div>
  </div>

</section>

<script>
const mesi = <?= json_encode($mesi) ?>;

new Chart(document.getElementById('clientiChart'), {
  type: 'line',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Clienti',
      data: <?= json_encode($clienti_per_mese) ?>,
      borderColor: '#2196F3',
      backgroundColor: 'rgba(33,150,243,0.2)',
      fill: true,
      tension: 0.4
    }]
  }
});

new Chart(document.getElementById('leadChart'), {
  type: 'bar',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Lead',
      data: <?= json_encode($lead_per_mese) ?>,
      backgroundColor: '#9C27B0'
    }]
  }
});

new Chart(document.getElementById('incassatoChart'), {
  type: 'line',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Incassato',
      data: <?= json_encode($incassato_per_mese) ?>,
      borderColor: '#4CAF50',
      backgroundColor: 'rgba(76,175,80,0.2)',
      fill: true,
      tension: 0.4
    }]
  }
});

new Chart(document.getElementById('daIncassareChart'), {
  type: 'line',
  data: {
    labels: mesi,
    datasets: [{
      label: 'Da Incassare',
      data: <?= json_encode($da_incassare_per_mese) ?>,
      borderColor: '#F44336',
      backgroundColor: 'rgba(244,67,54,0.2)',
      fill: true,
      tension: 0.4
    }]
  }
});
</script>

</body>
</html>
