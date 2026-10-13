// Apri la modale per aggiungere un nuovo lead
function openLeadModal() {
  const modal = document.getElementById('leadModal');
  if (modal) {
    modal.style.display = 'block';
  } else {
    console.error("Modale #leadModal non trovata nel DOM.");
  }
}

// Chiudi la modale
function closeLeadModal() {
  const modal = document.getElementById('leadModal');
  if (modal) {
    modal.style.display = 'none';
  }
}

// Chiudi la modale se clicco fuori dal contenuto
window.onclick = function(event) {
  const modal = document.getElementById('leadModal');
  if (event.target === modal) {
    modal.style.display = "none";
  }
}

// (Opzionale) Apri modale esportazione CSV
function openExportModal() {
  alert("Funzionalità esportazione non ancora implementata.");
}

// (Opzionale) Apri modale importazione CSV
function openImportModal() {
  alert("Funzionalità importazione non ancora implementata.");
}

// (Opzionale) Apri modale per conversione lead (usata se premi 🔁 Converti)
function openConvertModal(lead) {
  alert("Funzionalità di conversione non ancora collegata alla UI.");
  console.log("Dati del lead da convertire:", lead);
}
