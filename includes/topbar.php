<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../config/db.php";

date_default_timezone_set('Europe/Rome');
$oggi = date('Y-m-d');

$stmt = $pdo->prepare("SELECT c.nome, r.importo, r.data_scadenza FROM rate r JOIN clienti c ON r.cliente_id = c.id WHERE r.pagata = 0 AND r.data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY r.data_scadenza ASC");
$stmt->execute();
$notifiche = $stmt->fetchAll();
$notifiche_non_pagate = count($notifiche);
$badge_notifiche = $notifiche_non_pagate > 99 ? '99+' : (string) $notifiche_non_pagate;
$topbar_css_version = @filemtime(__DIR__ . '/../css/topbar.css') ?: time();
?>
<!-- CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  html, body {
    margin: 0 !important;
    padding: 0 !important;
  }

  body > .navbar-modern:first-of-type {
    margin-top: 0 !important;
  }
</style>
<link rel="stylesheet" href="/css/topbar.css?v=<?= urlencode((string) $topbar_css_version) ?>">

<!-- Topbar -->
<nav class="navbar-modern">
  <div class="navbar-left">
    <a class="brand" href="dashboard.php" aria-label="Vai alla dashboard">
      <i class="fas fa-leaf"></i>
      <span>Gestione Finanziamenti</span>
    </a>
    <button class="menu-toggle" onclick="toggleMobileMenu()"><i class="fas fa-bars"></i></button>
  </div>

  <ul class="navbar-links" id="navbarLinks">
    <li><a class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="clienti.php"><i class="fas fa-users"></i> Clienti</a></li>
    <li><a href="pagamenti.php"><i class="fas fa-credit-card"></i> Scadenziario</a></li>
    <li><a href="incassi.php"><i class="fas fa-money-check-alt"></i> Incassi</a></li>
    <li><a class="<?= basename($_SERVER['PHP_SELF']) == 'uscite.php' ? 'active' : '' ?>" href="uscite.php"><i class="fas fa-wallet"></i> Uscite</a></li>
    <li><a href="riepilogo.php"><i class="fas fa-chart-line"></i> Riepilogo</a></li>
    <li><a href="lead.php"><i class="fas fa-bullseye"></i> Lead</a></li>
  </ul>

  <div class="navbar-user">
    <div class="notification-wrapper">
      <div class="notification-bell" onclick="toggleNotifiche()">
        <i class="fas fa-bell"></i>
        <?php if ($notifiche_non_pagate > 0): ?>
          <span class="badge"><?= htmlspecialchars($badge_notifiche, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="user-dropdown" onclick="toggleUserMenu(event)">
      <?php if (isset($_SESSION['user'])): ?>
        <span><i class="fas fa-user-circle"></i>
          <?= htmlspecialchars($_SESSION['user']['username']) ?>
          (<?= htmlspecialchars($_SESSION['user']['ruolo']) ?>)
        </span>
        <div id="userMenu" class="user-menu">
          <a href="cambia_password.php"><i class="fas fa-key"></i> Cambia Password</a>
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      <?php else: ?>
        <span><i class="fas fa-user-circle"></i> Utente</span>
        <div id="userMenu" class="user-menu">
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php if ($notifiche_non_pagate > 0): ?>
<div id="notificheModal" class="notifiche-modal">
  <div class="notifiche-content">
    <span class="close-btn" onclick="closeNotifiche()">&times;</span>
    <h4>Rate da incassare nei prossimi 7 giorni</h4>
    <div class="notifiche-list">
      <ul>
        <?php foreach ($notifiche as $n): ?>
          <li>
            <strong><?= htmlspecialchars($n['nome']) ?></strong>
            - EUR <?= number_format($n['importo'], 2, ',', '.') ?>
            <small>(<?= date('d/m/Y', strtotime($n['data_scadenza'])) ?>)</small>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="notifiche-actions">
      <a href="pagamenti.php" class="btn btn-small">Vai allo scadenziario</a>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- STYLE RESPONSIVO -->
<style>
.menu-toggle {
  display: none;
  font-size: 20px;
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
}
.user-dropdown {
  position: relative;
  cursor: pointer;
}
.user-dropdown span {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0 10px;
  max-width: 100%;
  min-width: 0;
}
.user-menu {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  min-width: 180px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  z-index: 999;
}
.user-menu a {
  display: block;
  padding: 10px 15px;
  color: #333;
  text-decoration: none;
}
.user-menu a:hover {
  background-color: #f0f0f0;
}
@media screen and (max-width: 768px) {
  .navbar-links {
    display: none;
    flex-direction: column;
    background-color: #333;
    position: absolute;
    top: 60px;
    left: 0; right: 0;
    padding: 10px;
    z-index: 999;
  }
  .navbar-links li {
    margin-bottom: 10px;
  }
  .navbar-links li a {
    color: #fff;
  }
  .menu-toggle {
    display: inline-block;
    margin-left: 10px;
  }
}
</style>

<!-- JS INTERAZIONE -->
<script>
function toggleUserMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById("userMenu");
  menu.style.display = (menu.style.display === "block") ? "none" : "block";
  document.addEventListener('click', closeUserMenuOnOutsideClick);
}

function closeUserMenuOnOutsideClick(e) {
  const menu = document.getElementById("userMenu");
  const dropdown = document.querySelector(".user-dropdown");
  if (!dropdown.contains(e.target)) {
    menu.style.display = "none";
    document.removeEventListener('click', closeUserMenuOnOutsideClick);
  }
}

function toggleNotifiche() {
  const modal = document.getElementById("notificheModal");
  if (!modal) return;
  modal.classList.toggle("open");
  if (modal.classList.contains("open")) {
    syncNotificheLayout();
  }
  document.body.style.overflow = modal.classList.contains("open") ? "hidden" : "";
}

function closeNotifiche() {
  const modal = document.getElementById("notificheModal");
  if (!modal) return;
  modal.classList.remove("open");
  document.body.style.overflow = "";
}

function toggleMobileMenu() {
  const nav = document.getElementById("navbarLinks");
  nav.style.display = nav.style.display === "flex" ? "none" : "flex";
}

function syncNotificheLayout() {
  const modal = document.getElementById("notificheModal");
  if (!modal || !modal.classList.contains("open")) return;

  const content = modal.querySelector(".notifiche-content");
  const list = modal.querySelector(".notifiche-list");
  const title = modal.querySelector(".notifiche-content h4");
  const actions = modal.querySelector(".notifiche-actions");
  if (!content || !list || !title || !actions) return;

  const mobile = window.innerWidth <= 768;
  const verticalGap = mobile ? 76 : 32;
  const available = Math.max(280, window.innerHeight - verticalGap);
  const contentHeight = Math.min(760, available);

  content.style.maxHeight = contentHeight + "px";
  content.style.height = contentHeight + "px";

  const fixedHeight = title.offsetHeight + actions.offsetHeight + 72;
  const listMaxHeight = Math.max(140, contentHeight - fixedHeight);
  list.style.maxHeight = listMaxHeight + "px";
}

function enhanceResponsiveTables() {
  const tables = document.querySelectorAll("table.clienti-table, table.tabella-risultati");
  tables.forEach(function (table) {
    if (table.parentElement && !table.parentElement.classList.contains("table-responsive")) {
      const wrapper = document.createElement("div");
      wrapper.className = "table-responsive";
      table.parentNode.insertBefore(wrapper, table);
      wrapper.appendChild(table);
    }

    const headers = Array.from(table.querySelectorAll("thead th")).map(function (th) {
      return th.textContent.trim();
    });
    if (!headers.length) return;

    const applyLabels = function (rowSelector) {
      table.querySelectorAll(rowSelector).forEach(function (row) {
        let colIndex = 0;
        Array.from(row.children).forEach(function (cell) {
          if (cell.tagName !== "TD") return;
          const colspan = parseInt(cell.getAttribute("colspan") || "1", 10);
          if (!cell.hasAttribute("data-label") && colspan === 1) {
            const label = headers[colIndex] || headers[headers.length - 1] || "Valore";
            cell.setAttribute("data-label", label);
          }
          colIndex += colspan;
        });
      });
    };

    applyLabels("tbody tr");
    applyLabels("tfoot tr");
  });
}

document.addEventListener("click", function (event) {
  const modal = document.getElementById("notificheModal");
  if (modal && event.target === modal) {
    closeNotifiche();
  }
});

document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeNotifiche();
  }
});

document.addEventListener("DOMContentLoaded", function () {
  enhanceResponsiveTables();
});

window.addEventListener("resize", function () {
  const nav = document.getElementById("navbarLinks");
  if (window.innerWidth > 768 && nav) {
    nav.style.display = "";
  }
  syncNotificheLayout();
});
</script>

