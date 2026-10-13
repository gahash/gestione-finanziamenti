<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

error_reporting(E_ALL);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function safe($val) {
  return htmlspecialchars($val ?? '');
}

$search = $_GET['search'] ?? '';
$data_da = $_GET['data_da'] ?? '';
$data_a = $_GET['data_a'] ?? '';
$citta = $_GET['citta'] ?? '';
$pagamento = $_GET['pagamento'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$per_pagina = 10;
$offset = ($pagina - 1) * $per_pagina;

$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(
        nome LIKE :search OR citta LIKE :search OR email LIKE :search OR
        telefono LIKE :search OR note LIKE :search OR acquisto LIKE :search OR
        indirizzo LIKE :search OR codice_fiscale LIKE :search
    )";
    $params[':search'] = "%$search%";
}
if (!empty($data_da)) {
    $conditions[] = "data_registrazione >= :data_da";
    $params[':data_da'] = $data_da;
}
if (!empty($data_a)) {
    $conditions[] = "data_registrazione <= :data_a";
    $params[':data_a'] = $data_a;
}
if (!empty($citta)) {
    $conditions[] = "citta = :citta";
    $params[':citta'] = $citta;
}
if (!empty($pagamento)) {
    $conditions[] = "pagamento = :pagamento";
    $params[':pagamento'] = $pagamento;
}

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM clienti $where");
$count_stmt->execute($params);
$totale_clienti = $count_stmt->fetchColumn();
$totale_pagine = ceil($totale_clienti / $per_pagina);

$sql = "SELECT * FROM clienti $where ORDER BY nome ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => &$val) {
    $stmt->bindParam($key, $val);
}
$stmt->bindValue(':limit', $per_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$clienti = $stmt->fetchAll();
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card full-width">
    <div class="top-bar">
      <h2>📋 Anagrafica Clienti</h2>
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
          <input type="text" name="search" placeholder="🔍 Cerca..." value="<?= safe($search) ?>" class="input-search">
          <input type="date" name="data_da" value="<?= safe($data_da) ?>">
          <input type="date" name="data_a" value="<?= safe($data_a) ?>">
          <select name="pagamento">
            <option value="">Pagamento</option>
            <option value="immediato" <?= $pagamento === 'immediato' ? 'selected' : '' ?>>Contanti / Carta / Bonifico</option>
            <option value="rateale_compass" <?= $pagamento === 'rateale_compass' ? 'selected' : '' ?>>Compass</option>
            <option value="finanziamento_interno" <?= $pagamento === 'finanziamento_interno' ? 'selected' : '' ?>>Finanziamento Interno</option>
          </select>
          <button type="submit" class="btn">🔎 Filtra</button>
        </form>
        <button id="btnOpenModal" class="btn btn-add">➕ Nuovo</button>
        <a class="btn btn-export" href="esporta_clienti.php" target="_blank">⬇️ Esporta CSV</a>
      </div>
    </div>

    <table class="clienti-table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Città</th>
          <th>Telefono</th>
          <th>Email</th>
          <th>Pagamento</th>
          <th>Note</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($clienti) === 0): ?>
          <tr><td colspan="7" style="text-align:center;">Nessun cliente trovato.</td></tr>
        <?php else: ?>
          <?php foreach ($clienti as $cliente): ?>
          <tr>
            <td><?= safe($cliente['nome']) ?></td>
            <td><?= safe($cliente['citta']) ?></td>
            <td><?= safe($cliente['telefono']) ?></td>
            <td><?= safe($cliente['email']) ?></td>
            <td><?= safe($cliente['pagamento']) ?></td>
            <td><?= safe($cliente['note']) ?></td>
            <td>
              <a class="btn-edit" href="#" data-id="<?= $cliente['id'] ?>">✏️</a>
              <a class="btn-delete" href="elimina_cliente.php?id=<?= $cliente['id'] ?>" onclick="return confirm('Eliminare questo cliente?')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <div style="margin-top: 15px; text-align: center;">
      <?php for ($i = 1; $i <= $totale_pagine; $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"
           class="btn <?= $i == $pagina ? 'btn-active' : '' ?>"
           style="margin: 0 5px;">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
</div>

<div id="clienteModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeClienteModal()">&times;</span>
    <h2>Nuovo Cliente</h2>
    <form id="formNuovoCliente" method="post" action="salva_clienti.php">
      <label>Nome*</label>
      <input type="text" name="nome" required>
      <label>Telefono</label>
      <input type="text" name="telefono">
      <label>Email</label>
      <input type="email" name="email">
      <label>Indirizzo</label>
      <input type="text" name="indirizzo">
      <label>Città</label>
      <input type="text" name="citta">
      <label>Codice Fiscale</label>
      <input type="text" name="codice_fiscale">
      <label>Data di nascita</label>
      <input type="date" name="data_nascita">
      <label>Cosa ha acquistato</label>
      <input type="text" name="acquisto">
      <label>Importo</label>
      <input type="number" name="importo" id="form_importo" step="0.01">
      <label>Acconto</label>
      <input type="number" name="acconto" id="form_acconto" step="0.01">
      <label>Interessi (opzionale)</label>
      <input type="number" name="interessi" step="0.01">
      <label>Residuo da pagare</label>
      <input type="text" id="form_residuo" readonly style="background:#f6f6f6;color:#336;font-weight:bold;">
      <label>Tipo di pagamento</label>
      <select name="pagamento" id="pagamentoSelect">
        <option value="">--</option>
        <option value="immediato">Contanti / Carta / Bonifico</option>
        <option value="rateale_compass">Compass</option>
        <option value="finanziamento_interno">Finanziamento Interno</option>
      </select>
      <div id="campiFinanziamentoInterno" style="display:none;">
        <label>Durata (mesi)</label>
        <input type="number" name="durata" value="12" min="1">
        <label>Rata mensile (opzionale)</label>
        <input type="number" name="rata_mensile" step="0.01">
      </div>
      <label>Note</label>
      <textarea name="note"></textarea>
      <label>Data registrazione</label>
      <input type="date" name="data_registrazione" value="<?= date('Y-m-d') ?>">
      <button type="submit" class="btn">Salva</button>
    </form>
    <script>
    function calcolaResiduoCliente() {
      const importo = parseFloat(document.getElementById('form_importo').value) || 0;
      const acconto = parseFloat(document.getElementById('form_acconto').value) || 0;
      const residuo = importo - acconto;
      document.getElementById('form_residuo').value = "€ " + residuo.toFixed(2);
    }
    document.getElementById('form_importo').addEventListener('input', calcolaResiduoCliente);
    document.getElementById('form_acconto').addEventListener('input', calcolaResiduoCliente);
    setTimeout(calcolaResiduoCliente, 100);

    document.getElementById('pagamentoSelect').addEventListener('change', function () {
      const extra = document.getElementById('campiFinanziamentoInterno');
      extra.style.display = this.value === 'finanziamento_interno' ? 'block' : 'none';
    });
    </script>
  </div>
</div>

<div id="editClienteModal" class="modal" style="display:none;">
  <div class="modal-content" id="editModalContent"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById("btnOpenModal")?.addEventListener("click", openClienteModal);
  document.querySelectorAll(".btn-edit").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      openEditModal(btn);
    });
  });
});

function openClienteModal() {
  const modal = document.getElementById("clienteModal");
  modal.style.display = "block";
  document.body.style.overflow = "hidden";
}

function closeClienteModal() {
  const modal = document.getElementById("clienteModal");
  modal.style.display = "none";
  document.body.style.overflow = "auto";
}

function openEditModal(el) {
  const id = el.getAttribute("data-id");
  fetch("modifica_cliente.php?id=" + id)
    .then(res => res.text())
    .then(html => {
      document.getElementById("editModalContent").innerHTML = html;
      document.getElementById("editClienteModal").style.display = "block";
      document.body.style.overflow = "hidden";
    });
}
</script>
