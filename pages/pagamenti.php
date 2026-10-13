<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../config/db.php";
require_once "../includes/topbar.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Aggiorna stato rata (pagata / non pagata)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rata_id'], $_POST['pagata'])) {
    $stmt = $pdo->prepare("UPDATE rate SET pagata = ? WHERE id = ?");
    $stmt->execute([(int)$_POST['pagata'], (int)$_POST['rata_id']]);
    exit;
}

// Aggiorna nota rata
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nota_rata_id'], $_POST['nota'])) {
    $stmt = $pdo->prepare("UPDATE rate SET note = :nota WHERE id = :id");
    $stmt->execute([
        ':nota' => trim($_POST['nota']),
        ':id' => (int)$_POST['nota_rata_id']
    ]);
    exit;
}

// Filtri
$search = $_GET['search'] ?? '';
$solo_da_pagare = isset($_GET['solo_da_pagare']) && $_GET['solo_da_pagare'] == '1';
$data_da = $_GET['data_da'] ?? '';
$data_a = $_GET['data_a'] ?? '';

$conditions = ["c.pagamento IN ('finanziamento_interno', 'rateale_compass')"];
$params = [];

if ($search) {
    $conditions[] = "c.nome LIKE :search";
    $params[':search'] = "%$search%";
}
if ($solo_da_pagare) {
    $conditions[] = "r.pagata = 0";
}
if ($data_da && $data_a) {
    $conditions[] = "r.data_scadenza BETWEEN :data_da AND :data_a";
    $params[':data_da'] = $data_da;
    $params[':data_a'] = $data_a;
}

$where = 'WHERE ' . implode(' AND ', $conditions);

// Recupera tutte le rate (rimosso il LIMIT!)
$sql = "SELECT r.*, c.nome, c.pagamento, c.id AS cliente_id
        FROM rate r
        JOIN clienti c ON r.cliente_id = c.id
        $where
        ORDER BY c.nome ASC, r.data_scadenza ASC";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$rate = $stmt->fetchAll();

// Organizza per cliente
$clienti_rate = [];
foreach ($rate as $r) {
    $id = $r['cliente_id'];
    if (!isset($clienti_rate[$id])) {
        $clienti_rate[$id] = [
            'nome' => $r['nome'],
            'pagamento' => $r['pagamento'],
            'rate' => [],
            'totale' => 0,
            'incassato' => 0,
            'da_incassare' => 0,
            'ultima_scadenza' => $r['data_scadenza']
        ];
    }
    $clienti_rate[$id]['rate'][] = $r;
    $clienti_rate[$id]['totale'] += $r['importo'];
    if ($r['pagata']) {
        $clienti_rate[$id]['incassato'] += $r['importo'];
    } else {
        $clienti_rate[$id]['da_incassare'] += $r['importo'];
    }
    if ($r['data_scadenza'] > $clienti_rate[$id]['ultima_scadenza']) {
        $clienti_rate[$id]['ultima_scadenza'] = $r['data_scadenza'];
    }
}
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card">
    <div class="top-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
      <h2>💳 Scadenziario Pagamenti</h2>
      <form method="get" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <input type="text" name="search" placeholder="🔍 Cerca cliente..." value="<?= htmlspecialchars($search) ?>" class="input-search">
        <label><input type="checkbox" name="solo_da_pagare" value="1" <?= $solo_da_pagare ? 'checked' : '' ?>> Solo da pagare</label>
        <input type="date" name="data_da" value="<?= htmlspecialchars($data_da) ?>">
        <input type="date" name="data_a" value="<?= htmlspecialchars($data_a) ?>">
        <button type="submit" class="btn">Filtra</button>
      </form>
      <a href="esporta_pagamenti.php" class="btn btn-export">📁 Esporta Excel</a>
    </div>

    <table class="clienti-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Totale</th>
          <th>Incassato</th>
          <th>Da Incassare</th>
          <th>Data Ultima Scadenza</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clienti_rate as $cliente_id => $dati): ?>
        <tr>
          <td><?= htmlspecialchars($dati['nome']) ?></td>
          <td>€<?= number_format($dati['totale'], 2, ',', '.') ?></td>
          <td>€<?= number_format($dati['incassato'], 2, ',', '.') ?></td>
          <td>€<?= number_format($dati['da_incassare'], 2, ',', '.') ?></td>
          <td><?= date('d/m/Y', strtotime($dati['ultima_scadenza'])) ?></td>
          <td><a href="#" onclick="toggleRate(<?= $cliente_id ?>)">📂 Vedi Rate</a></td>
        </tr>
        <?php foreach ($dati['rate'] as $r): ?>
        <tr class="rate-cliente rate-cliente-<?= $cliente_id ?>" style="display:none;">
          <td><?= htmlspecialchars($r['nome']) ?></td>
          <td>Rata <?= $r['numero_rata'] ?></td>
          <td>€<?= number_format($r['importo'], 2, ',', '.') ?></td>
          <td><?= date('d/m/Y', strtotime($r['data_scadenza'])) ?></td>
          <td>
            <select onchange="aggiornaStatoRata(<?= $r['id'] ?>, this.value)">
              <option value="0" <?= !$r['pagata'] ? 'selected' : '' ?>>❌ Da pagare</option>
              <option value="1" <?= $r['pagata'] ? 'selected' : '' ?>>✅ Pagata</option>
            </select>
          </td>
          <td>
            <textarea onchange="aggiornaNotaRata(<?= $r['id'] ?>, this.value)" style="width: 100%; font-size: 0.9em;" rows="2"><?= htmlspecialchars($r['note']) ?></textarea>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleRate(clienteId) {
  document.querySelectorAll('.rate-cliente-' + clienteId).forEach(row => {
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
  });
}

function aggiornaStatoRata(id, value) {
  fetch('pagamenti.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `rata_id=${id}&pagata=${value}`
  }).then(res => {
    if (!res.ok) alert("Errore nel salvataggio");
  });
}

function aggiornaNotaRata(id, nota) {
  fetch('pagamenti.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `nota_rata_id=${id}&nota=${encodeURIComponent(nota)}`
  }).then(res => {
    if (!res.ok) alert("Errore nel salvataggio della nota");
  });
}
</script>

<?php require_once "../includes/footer.php"; ?>
