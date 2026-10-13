<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../config/db.php";

// Totale clienti
$tot_clienti = $pdo->query("SELECT COUNT(*) FROM clienti")->fetchColumn();

// Totale importi pagati
$tot_pagato = $pdo->query("SELECT SUM(importo) FROM rate WHERE pagata = 1")->fetchColumn() ?: 0;

// Totale importi da pagare
$tot_da_pagare = $pdo->query("SELECT SUM(importo) FROM rate WHERE pagata = 0")->fetchColumn() ?: 0;

// Importo totale incassato (uguale a rate pagate per ora)
$tot_incassato = $tot_pagato;

// Ultimi clienti registrati
$ultimi_clienti = $pdo->query("SELECT nome, data_registrazione FROM clienti ORDER BY data_registrazione DESC LIMIT 5")->fetchAll();
?>

<link rel="stylesheet" href="../css/clienti.css">
<?php require_once "../includes/topbar.php"; ?>

<div class="clienti-container">
  <div class="card">
    <div class="top-bar">
      <h2>📊 Riepilogo Generale</h2>
    </div>

    <div class="dashboard-grid">
      <div class="card stat">
        <h3>👥 Totale Clienti</h3>
        <p><?= $tot_clienti ?></p>
      </div>
      <div class="card stat">
        <h3>✅ Rate Pagate</h3>
        <p>€<?= number_format($tot_pagato, 2, ',', '.') ?></p>
      </div>
      <div class="card stat">
        <h3>❌ Da Incassare</h3>
        <p>€<?= number_format($tot_da_pagare, 2, ',', '.') ?></p>
      </div>
      <div class="card stat">
        <h3>💰 Totale Incassato</h3>
        <p>€<?= number_format($tot_incassato, 2, ',', '.') ?></p>
      </div>
    </div>

    <div class="card" style="margin-top: 30px;">
      <h3>📅 Ultimi Clienti Registrati</h3>
      <table class="clienti-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Data Registrazione</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimi_clienti as $cliente): ?>
          <tr>
            <td data-label="Nome"><?= htmlspecialchars($cliente['nome']) ?></td>
            <td data-label="Data"><?= $cliente['data_registrazione'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>
