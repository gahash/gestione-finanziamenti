<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/topbar.php";
require_once "../config/db.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Filtri ricerca
$search   = $_GET['search']  ?? '';
$data_da  = $_GET['data_da'] ?? '';
$data_a   = $_GET['data_a']  ?? '';

$conditions = [];
$params     = [];

if ($search !== '') {
    $conditions[] = "(nome LIKE :search OR citta LIKE :search OR email LIKE :search OR telefono LIKE :search OR esito LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($data_da !== '' && $data_a !== '') {
    $conditions[] = "data_ricontatto BETWEEN :da AND :a";
    $params[':da'] = $data_da;
    $params[':a']  = $data_a;
}
$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("SELECT * FROM lead $where ORDER BY data_ricontatto ASC");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$lead = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../css/clienti.css">

<div class="clienti-container">
  <?php if (isset($_GET['success']) && $_GET['success']==='converted'): ?>
      <div class="alert success">✅ Lead convertito in cliente con successo.</div>
  <?php elseif (isset($_GET['success'])): ?>
      <div class="alert success">✅ Lead aggiunto correttamente.</div>
  <?php endif; ?>
  <?php if (isset($_GET['import'])): ?>
      <div class="alert success">✅ Lead importati correttamente.</div>
  <?php endif; ?>
  <?php if (isset($_GET['error']) && $_GET['error']==='missing'): ?>
      <div class="alert error">⚠️ Inserire almeno il nome e la data di registrazione.</div>
  <?php elseif (isset($_GET['error']) && $_GET['error']==='db'): ?>
      <div class="alert error">❌ Errore durante il salvataggio del lead.</div>
  <?php endif; ?>

  <div class="card full-width">
    <div class="top-bar">
      <h2>🧲 Potenziali Clienti</h2>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
          <input class="input-search" type="text" name="search" placeholder="🔍 Cerca nome, città, email, telefono, esito…" value="<?= htmlspecialchars($search) ?>">
          <input type="date" name="data_da" value="<?= htmlspecialchars($data_da) ?>">
          <input type="date" name="data_a"  value="<?= htmlspecialchars($data_a)  ?>">
          <button class="btn" type="submit">Filtra</button>
        </form>
        <button class="btn btn-add"    id="btnNewLead">+ Nuovo</button>
        <button class="btn btn-export" id="btnExportLead">⬇️ Esporta CSV</button>
        <button class="btn btn-import" id="btnImportLead">⬆️ Importa</button>
      </div>
    </div>
    <table class="clienti-table">
      <thead>
        <tr>
          <th>Nome</th><th>Città</th><th>Email</th><th>Telefono</th>
          <th>Data Ricontatto</th><th>Esito</th><th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$lead): ?>
            <tr><td colspan="7" style="text-align:center;">Nessun lead trovato.</td></tr>
        <?php else: foreach ($lead as $l): ?>
            <tr>
              <td><?= htmlspecialchars($l['nome'])      ?></td>
              <td><?= htmlspecialchars($l['citta'])     ?></td>
              <td><?= htmlspecialchars($l['email'])     ?></td>
              <td><?= htmlspecialchars($l['telefono'])  ?></td>
              <td><?= htmlspecialchars($l['data_ricontatto']) ?></td>
              <td><?= htmlspecialchars($l['esito'])     ?></td>
              <td>
                <a href="#" class="btn-edit" onclick="openEditLeadModal(<?= $l['id'] ?>)">✏️</a>
                <a href="elimina_lead.php?id=<?= $l['id'] ?>" class="btn-delete" onclick="return confirm('Eliminare questo lead?')">🗑️</a>
                <?php if ($l['esito']==='acquistato'): ?>
                  <button class="btn btn-converti" onclick='openConvertModal(<?= json_encode($l) ?>)'>🔁 Converti</button>
                <?php endif; ?>
              </td>
            </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<div id="editLeadModal" class="modal" style="display:none;">
  <div class="modal-content" id="editLeadContent"></div>
</div>
<?php include_once __DIR__ . "/lead_modals.php"; ?>
<script>
document.addEventListener("DOMContentLoaded",()=>{
  const open=(btn,id)=>{
    const b=document.getElementById(btn);
    const m=document.getElementById(id);
    if(b&&m) b.addEventListener("click",()=>{m.style.display="block";document.body.style.overflow="hidden";});
  };
  open("btnNewLead","leadModal");
  open("btnImportLead","importModal");
  open("btnExportLead","exportModal");
});
function openEditLeadModal(id){
  fetch("modifica_lead.php?id="+id)
    .then(r=>r.text())
    .then(h=>{
      document.getElementById("editLeadContent").innerHTML=h;
      document.getElementById("editLeadModal").style.display="block";
      document.body.style.overflow="hidden";
    });
}
function openConvertModal(lead){
  const m=document.getElementById("convertModal");
  if(!m) return;
  m.style.display="block";
  document.body.style.overflow="hidden";
  const map={id:"convert_lead_id",nome:"convert_nome",email:"convert_email",telefono:"convert_telefono",citta:"convert_citta"};
  for(const k in map){
    const el=document.getElementById(map[k]);
    if(el) el.value=lead[k]||"";
  }
}
function closeModal(id){
  const m=document.getElementById(id);
  if(m){m.style.display="none";document.body.style.overflow="auto";}
}
function closeLeadModal(){closeModal("leadModal");}
function closeImportModal(){closeModal("importModal");}
function closeExportModal(){closeModal("exportModal");}
function closeConvertModal(){closeModal("convertModal");}
function closeEditModal(){closeModal("editLeadModal");}
window.onclick=e=>{
  ["leadModal","importModal","exportModal","convertModal","editLeadModal"].forEach(id=>{
      const m=document.getElementById(id);
      if(m&&e.target===m) closeModal(id);
  });
};
</script>
<?php require_once "../includes/footer.php"; ?>
