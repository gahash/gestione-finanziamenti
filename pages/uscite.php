<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Eliminazione uscita
if (isset($_GET['elimina'])) {
    $id = (int)$_GET['elimina'];
    $pdo->prepare("DELETE FROM uscite WHERE id = ?")->execute([$id]);
    echo "<script>location.href='uscite.php?deleted=1';</script>";
    exit;
}

// Inserimento nuova uscita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descrizione'])) {
    $stmt = $pdo->prepare("INSERT INTO uscite (descrizione, importo, note, tipo, data_uscita)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['descrizione'],
        $_POST['importo'],
        $_POST['note'],
        $_POST['tipo'],
        $_POST['data_uscita']
    ]);
    echo "<script>location.href='uscite.php?success=1';</script>";
    exit;
}

// Filtri
$filter_descrizione = $_GET['filtro_descrizione'] ?? '';
$filter_da = $_GET['filtro_da'] ?? '';
$filter_a = $_GET['filtro_a'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($filter_descrizione) {
    $where .= " AND descrizione LIKE :descrizione";
    $params[':descrizione'] = "%$filter_descrizione%";
}
if ($filter_da && $filter_a) {
    $where .= " AND data_uscita BETWEEN :da AND :a";
    $params[':da'] = $filter_da;
    $params[':a'] = $filter_a;
}

// Paginazione
$per_page = isset($_GET['per_page']) && (int)$_GET['per_page'] > 0 ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM uscite $where");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));

$stmt = $pdo->prepare("SELECT * FROM uscite $where ORDER BY data_uscita DESC LIMIT :offset, :per_page");
foreach ($params as $k => &$v) {
    $stmt->bindParam($k, $v);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$uscite = $stmt->fetchAll();
$totale_spese = array_sum(array_column($uscite, 'importo'));
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <div class="card">
    <div class="top-bar" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
      <h2>💸 Uscite</h2>
      <form method="get" style="display: flex; gap: 10px;">
        <input type="text" name="filtro_descrizione" placeholder="🔍 Cerca per voce" value="<?= htmlspecialchars($filter_descrizione) ?>" class="input-search">
        <label>Dal: <input type="date" name="filtro_da" value="<?= $filter_da ?>"></label>
        <label>Al: <input type="date" name="filtro_a" value="<?= $filter_a ?>"></label>
        <button type="submit" class="btn">Filtra</button>
      </form>
    </div>

    <div style="margin: 20px 0;">
      <button class="btn btn-add" onclick="openModal()">➕ Aggiungi Spesa</button>
    </div>

    <table class="clienti-table">
      <thead>
        <tr>
          <th>Data</th>
          <th>Descrizione</th>
          <th>Importo</th>
          <th>Tipo</th>
          <th>Note</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($uscite as $u): ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($u['data_uscita'])) ?></td>
          <td><?= htmlspecialchars($u['descrizione']) ?></td>
          <td>€<?= number_format($u['importo'], 2, ',', '.') ?></td>
          <td><?= $u['tipo'] === 'ricorrente' ? '♻️ Ricorrente' : '📌 Una Tantum' ?></td>
          <td><?= htmlspecialchars($u['note']) ?></td>
          <td>
            <a href="#" class="btn-edit" onclick="openEditModal(<?= $u['id'] ?>)">✏️</a>
            <a href="?elimina=<?= $u['id'] ?>" class="btn-delete" onclick="return confirm('Eliminare questa uscita?')">🗑️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="font-weight:bold;">
          <td colspan="2">Totale spese</td>
          <td>€<?= number_format($totale_spese, 2, ',', '.') ?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>

    <!-- Paginazione -->
    <div style="margin-top: 20px; text-align: center;">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="btn <?= $i == $page ? 'btn-active' : '' ?>" href="?page=<?= $i ?>&per_page=<?= $per_page ?>"> <?= $i ?> </a>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- Modale aggiunta -->
<div id="uscitaModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>➕ Nuova Spesa</h3>
    <form method="post">
      <label>Descrizione: <input type="text" name="descrizione" required></label>
      <label>Importo: <input type="number" step="0.01" name="importo" required></label>
      <label>Note: <input type="text" name="note"></label>
      <label>Tipo:
        <select name="tipo" required>
          <option value="una_tantum">Una Tantum</option>
          <option value="ricorrente">Ricorrente</option>
        </select>
      </label>
      <label>Data: <input type="date" name="data_uscita" value="<?= date('Y-m-d') ?>" required></label>
      <button type="submit" class="btn">Salva</button>
    </form>
  </div>
</div>

<!-- Modale modifica -->
<div id="editModal" class="modal">
  <div class="modal-content" id="editModalContent"></div>
</div>

<style>
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.4);
}
.modal-content {
  background-color: #fff;
  margin: 5% auto;
  padding: 20px;
  border-radius: 6px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
.modal .close {
  float: right;
  font-size: 24px;
  cursor: pointer;
}
</style>

<script>
function openModal() {
  document.getElementById("uscitaModal").style.display = "block";
  document.body.style.overflow = "hidden";
}
function closeModal() {
  document.getElementById("uscitaModal").style.display = "none";
  document.body.style.overflow = "";
}
window.addEventListener('click', function(e) {
  const modal = document.getElementById("uscitaModal");
  if (e.target === modal) closeModal();
});

function openEditModal(id) {
  fetch('modifica_uscita.php?id=' + id)
    .then(res => res.text())
    .then(html => {
      document.getElementById("editModalContent").innerHTML = html;
      document.getElementById("editModal").style.display = "block";
      document.body.style.overflow = "hidden";

      document.getElementById("formModificaUscita").addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('salva_modifica_uscita.php', {
          method: 'POST',
          body: formData
        }).then(() => location.reload());
      });
    });
}
function closeEditModal() {
  document.getElementById("editModal").style.display = "none";
  document.body.style.overflow = "";
}
</script>

<?php require_once "../includes/footer.php"; ?>
