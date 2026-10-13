<script>
function calcolaResiduoConverti(){
  const imp = parseFloat(document.getElementById("convert_importo") ?.value)||0;
  const acc = parseFloat(document.getElementById("convert_acconto")?.value)||0;
  const int = parseFloat(document.getElementById("convert_interessi")?.value)||0;
  const dur = parseInt  (document.getElementById("convert_durata")  ?.value)||12;
  const residuo = imp - acc + int;
  const rataSug = dur>0 ? (residuo/dur).toFixed(2) : '0.00';
  const out = document.getElementById("convert_residuo_text");
  const sug = document.getElementById("convert_suggerimento_rata");
  if(out){ out.textContent = `Residuo da pagare: €${residuo.toFixed(2)}`;
           out.style.color = residuo<0 ? 'red':'green'; }
  if(sug) sug.textContent = `Suggerimento rata: €${rataSug}`;
}
['convert_importo','convert_acconto','convert_interessi','convert_durata']
 .forEach(id=>{
   const el=document.getElementById(id);
   if(el) el.addEventListener('input',calcolaResiduoConverti);
});
document.getElementById("convertForm").addEventListener("submit",e=>{
  const imp=parseFloat(document.getElementById("convert_importo") ?.value)||0;
  const acc=parseFloat(document.getElementById("convert_acconto")?.value)||0;
  const int=parseFloat(document.getElementById("convert_interessi")?.value)||0;
  if(imp-acc+int < 0){
    e.preventDefault();
    alert("⚠️ Attenzione: il residuo è negativo. Verifica i dati inseriti.");
  }
});
</script>
<!-- Modale NUOVO LEAD -->
<div id="leadModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeLeadModal()">&times;</span>
    <h2 style="color:#377dff;font-size:2rem;margin-bottom:10px;text-align:center;">+ Nuovo Lead</h2>
    <form method="post" action="nuovo_lead.php">
      <input name="nome"      placeholder="Nome" required>
      <input name="email"     placeholder="Email">
      <input name="telefono"  placeholder="Telefono">
      <input name="citta"     placeholder="Città">
      <input type="date" name="data_ricontatto" placeholder="Data ricontatto">
      <select name="esito" required style="margin-bottom:10px;">
        <option value="" disabled selected>Seleziona esito</option>
        <option value="interessato">Interessato</option>
        <option value="acquistato">Acquistato</option>
        <option value="non interessato">Non interessato</option>
      </select>
      <button class="btn" type="submit" style="width:100%;">Salva</button>
    </form>
  </div>
</div>
<!-- Modale IMPORTA -->
<div id="importModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeImportModal()">&times;</span>
    <h2>⬆️ Importa Lead</h2>
    <form method="post" action="importa_lead.php" enctype="multipart/form-data">
      <input type="file" name="file_csv" accept=".csv" required>
      <button class="btn" type="submit">Importa</button>
    </form>
  </div>
</div>
<!-- Modale ESPORTA -->
<div id="exportModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeExportModal()">&times;</span>
    <h2>⬇️ Esporta Lead</h2>
    <p>Scarica i lead attuali in formato CSV</p>
    <a href="esporta_lead.php" class="btn">Scarica</a>
  </div>
</div>
<!-- Modale CONVERTI LEAD -->
<div id="convertModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeConvertModal()">&times;</span>
    <h2>🔁 Converti Lead in Cliente</h2>
    <form id="convertForm" method="post" action="converti_lead.php">
      <input type="hidden" name="lead_id" id="convert_lead_id">

      <label>Nome</label>
      <input name="nome" id="convert_nome" required>

      <label>Email</label>
      <input type="email" name="email" id="convert_email">

      <label>Telefono</label>
      <input name="telefono" id="convert_telefono">

      <label>Città</label>
      <input name="citta" id="convert_citta">

      <label>Indirizzo</label>
      <input name="indirizzo" id="convert_indirizzo">

      <label>Codice Fiscale</label>
      <input name="codice_fiscale" id="convert_codice_fiscale">

      <label>Data di nascita</label>
      <input type="date" name="data_nascita" id="convert_data_nascita">

      <label>Data registrazione</label>
      <input type="date" name="data_registrazione" value="<?= date('Y-m-d') ?>" required>

      <label>Importo</label>
      <input type="number" step="0.01" name="importo" id="convert_importo">

      <label>Acconto</label>
      <input type="number" step="0.01" name="acconto" id="convert_acconto">

      <label>Interessi</label>
      <input type="number" step="0.01" name="interessi" id="convert_interessi">

      <label>Durata (mesi)</label>
      <input type="number" name="durata" id="convert_durata" value="12">

      <label>Rata Mensile (opzionale)</label>
      <input type="number" step="0.01" name="rata_mensile" id="convert_rata_mensile">

      <div id="convert_residuo_text" style="font-weight:bold;"></div>
      <div id="convert_suggerimento_rata" style="font-style:italic;color:#888;"></div>

      <label>Tipo di pagamento</label>
      <select name="pagamento">
        <option value="">--</option>
        <option value="immediato">Contanti / Carta / Bonifico</option>
        <option value="rateale_compass">Compass</option>
        <option value="finanziamento_interno">Finanziamento Interno</option>
      </select>

      <label>Note</label>
      <textarea name="note"></textarea>

      <button class="btn" type="submit">Converti</button>
    </form>
  </div>
</div>
