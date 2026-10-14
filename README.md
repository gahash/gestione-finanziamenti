# Gestione Finanziamenti

[![Controllo sintassi PHP](https://github.com/gahash/gestione-finanziamenti/actions/workflows/php-lint.yml/badge.svg)](https://github.com/gahash/gestione-finanziamenti/actions/workflows/php-lint.yml)

Applicazione web gestionale in PHP per la gestione di clienti, vendite rateali e finanziamenti: anagrafica clienti, piani di rateizzazione, scadenziario, incassi, uscite, lead commerciali, esportazioni Excel e dashboard riepilogativa.

## Funzionalità

- **Autenticazione** con sessione, ruoli utente (`admin`, `responsabile`, `dipendente`) e cambio password.
- **Dashboard** con indicatori principali e grafico degli incassi degli ultimi sei mesi (Chart.js).
- **Clienti**: inserimento, modifica, eliminazione, ricerca e filtri; gestione di acconto, interessi e modalità di pagamento (immediato, finanziamento interno, finanziaria esterna).
- **Rate e scadenziario**: generazione del piano rate (anche massiva), registrazione dei pagamenti, note, scadenziario dedicato ai pagamenti tramite finanziaria esterna.
- **Notifiche** delle rate in scadenza nei 7 giorni successivi.
- **Incassi e uscite**: registrazione delle spese (una tantum o ricorrenti) e riepilogo economico.
- **Lead**: gestione dei contatti commerciali, esito, data di ricontatto, importazione e conversione del lead in cliente.
- **Esportazioni** in Excel (PhpSpreadsheet) di clienti, pagamenti, incassi, lead e scadenziario.
- Interfaccia **responsive** (tabelle adattive su mobile).

## Tecnologie

- PHP 8.1+ con PDO (MySQL/MariaDB)
- MySQL 5.7+ / MariaDB 10.3+
- [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) tramite Composer
- HTML, CSS, JavaScript vanilla
- Chart.js e Font Awesome via CDN

## Requisiti

- PHP 8.1 o superiore con estensioni `pdo_mysql`, `mbstring`, `zip`, `gd`, `xml`
- Composer
- Server web (Apache/Nginx) oppure il server integrato di PHP per lo sviluppo
- Database MySQL o MariaDB

## Installazione

1. **Clonare il repository**

   ```bash
   git clone <url-del-repository> gestione-finanziamenti
   cd gestione-finanziamenti
   ```

2. **Installare le dipendenze**

   ```bash
   composer install
   ```

3. **Creare il database e importare lo schema**

   ```bash
   mysql -u root -p -e "CREATE DATABASE gestione_finanziamenti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p gestione_finanziamenti < sql/schema.sql
   # Facoltativo: dati di esempio fittizi
   mysql -u root -p gestione_finanziamenti < sql/seed.sql
   ```

4. **Configurare la connessione**

   Le credenziali sono lette da variabili d'ambiente. Copiare il file di esempio e valorizzarlo:

   ```bash
   cp .env.example .env
   ```

   | Variabile    | Descrizione              |
   |--------------|--------------------------|
   | `DB_HOST`    | Host del database        |
   | `DB_NAME`    | Nome del database        |
   | `DB_USER`    | Utente del database      |
   | `DB_PASS`    | Password del database    |
   | `DB_CHARSET` | Charset (default `utf8mb4`) |

   In alternativa è possibile impostare le stesse variabili a livello di server web (es. `SetEnv` in Apache o `fastcgi_param` in Nginx).

5. **Impostare la password dell'utente amministratore**

   Il file `sql/seed.sql` crea l'utente `admin` **senza password valida**. Generare un hash e aggiornarlo:

   ```bash
   php -r "echo password_hash('LA_TUA_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
   ```

   ```sql
   UPDATE utenti SET password = '<hash generato>' WHERE username = 'admin';
   ```

   Se non si usa il seed, inserire manualmente un utente:

   ```sql
   INSERT INTO utenti (username, password, ruolo) VALUES ('admin', '<hash generato>', 'admin');
   ```

6. **Avviare l'applicazione**

   L'applicazione usa percorsi assoluti per CSS e immagini, quindi la document root deve puntare alla cartella del progetto. Per lo sviluppo:

   ```bash
   php -S localhost:8000
   ```

   e aprire `http://localhost:8000`.

## Struttura del progetto

```
.
├── config/
│   └── db.php              # Connessione PDO (credenziali da variabili d'ambiente)
├── css/                    # Fogli di stile delle pagine
├── images/                 # Asset grafici generici
├── includes/               # Autenticazione, header, footer, barra di navigazione
├── js/                     # Script lato client
├── pages/                  # Pagine dell'applicazione ed endpoint (salvataggio, esportazioni, AJAX)
├── sql/
│   ├── schema.sql          # Struttura del database
│   └── seed.sql            # Dati di esempio fittizi
├── .env.example            # Modello di configurazione
├── composer.json
└── index.php               # Reindirizza alla pagina di login
```

## Note di sicurezza

- Nessuna credenziale è salvata nel codice: la configurazione reale risiede in `.env` (escluso dal versionamento tramite `.gitignore`).
- Le password degli utenti sono salvate con `password_hash()`; eventuali hash legacy MD5 vengono migrati automaticamente al primo accesso.
- Le query usano prepared statement PDO.
- In produzione si consiglia di: servire l'applicazione solo in HTTPS, disattivare `display_errors`, limitare l'accesso alle cartelle `config/`, `sql/` e `vendor/`, e aggiungere protezione CSRF ai form.
- Lo sfondo video della pagina di login (`video/login-background.mp4`) non è incluso nel repository; in sua assenza la pagina mostra lo sfondo predefinito.
- `pages/genera_cambiale.php` è un prototipo che richiede la libreria FPDF e un'immagine di sfondo non inclusi.

## Licenza

Tutti i diritti riservati.
