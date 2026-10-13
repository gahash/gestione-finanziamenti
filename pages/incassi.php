<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Filtro pagamento
$filtro_pagamento = isset($_GET['pagamento']) ? $_GET['pagamento'] : '';

// Filtro ricerca
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query base
$sql = "SELECT c.id, c.nome, c.pagamento, c.data_registrazione,
               SUM(CASE WHEN r.pagata = 1 THEN r.importo ELSE 0 END) as incassato,
               SUM(CASE WHEN r.pagata = 0 THEN r.importo ELSE 0 END) as da_incassare,
               SUM(r.importo) as totale
        FROM clienti c
        LEFT JOIN rate r ON r.cliente_id = c.id
        WHERE (c.pagamento IS NOT NULL)";

// Applica filtro metodo pagamento
if ($filtro_pagamento) {
    $sql .= " AND c.pagamento = :pagamento";
}

// Applica filtro ricerca nome
if ($search) {
    $sql .= " AND c.nome LIKE :search";
}

$sql .= " GROUP BY c.id, c.nome, c.pagamento, c.data_registrazione
          ORDER BY c.nome ASC";

$stmt = $pdo->prepare($sql);

if ($filtro_pagamento) {
    $stmt->bindValue(':pagamento', $filtro_pagamento);
}
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$clienti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card">
    <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
      <h2>💰 Incassi Completati</h2>
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <form method="get" action="" style="display: flex; gap: 10px; align-items: center;">
          <select name="pagamento" class="input-search" onchange="this.form.submit()">
            <option value="">-- Tutti i Metodi --</option>
            <option value="immediato" <?= $filtro_pagamento == 'immediato' ? 'selected' : '' ?>>Contanti / Carta / Bonifico</option>
            <option value="rateale_compass" <?= $filtro_pagamento == 'rateale_compass' ? 'selected' : '' ?>>Compass</option>
            <option value="finanziamento_interno" <?= $filtro_pagamento == 'finanziamento_interno' ? 'selected' : '' ?>>Finanziamento Interno</option>
          </select>
          <input type="text" name="search" placeholder="🔍 Cerca cliente..." value="<?= htmlspecialchars($search) ?>" class="input-search">
          <button type="submit" class="btn">Filtra</button>
        </form>
        <a href="esporta_incassi.php" class="btn btn-export" style="background-color:#4CAF50;color:white;padding:8px 12px;border-radius:4px;text-decoration:none;font-weight:bold;">
          📁 Esporta Excel
        </a>
      </div>
    </div>

    <table class="clienti-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Tipo Pagamento</th>
          <th>Data Registrazione</th>
          <th>Importo Totale</th>
          <th>Incassato</th>
          <th>Da Incassare</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clienti as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['nome']) ?></td>
          <td><?= $c['pagamento'] === 'immediato' ? 'Pagamento Immediato' : ($c['pagamento'] === 'rateale_compass' ? 'Compass' : 'Finanziamento Interno') ?></td>
          <td><?= date('d/m/Y', strtotime($c['data_registrazione'])) ?></td>
          <td>€<?= number_format($c['totale'], 2, ',', '.') ?></td>
          <td>€<?= number_format($c['incassato'], 2, ',', '.') ?></td>
          <td>€<?= number_format($c['da_incassare'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <?php 
        $tot_totale = array_sum(array_column($clienti, 'totale'));
        $tot_incassato = array_sum(array_column($clienti, 'incassato'));
        $tot_da_incassare = array_sum(array_column($clienti, 'da_incassare'));
        ?>
        <tr style="font-weight: bold; background-color: #f0f0f0;">
          <td colspan="3" style="text-align:right;">Totali:</td>
          <td>€<?= number_format($tot_totale, 2, ',', '.') ?></td>
          <td>€<?= number_format($tot_incassato, 2, ',', '.') ?></td>
          <td>€<?= number_format($tot_da_incassare, 2, ',', '.') ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>
