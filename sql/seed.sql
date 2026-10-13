-- Gestione Finanziamenti - dati di esempio (TUTTI FITTIZI)
-- Importare dopo sql/schema.sql.
--
-- Utente demo: "admin". La password NON e' inclusa: il campo contiene un
-- segnaposto non valido, quindi il login e' impossibile finche' non si imposta
-- una password. Generare l'hash con:
--   php -r "echo password_hash('LA_TUA_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
-- e poi eseguire:
--   UPDATE utenti SET password = '<hash generato>' WHERE username = 'admin';

INSERT INTO `utenti` (`username`, `password`, `ruolo`) VALUES
('admin', 'DA_IMPOSTARE', 'admin');

INSERT INTO `clienti` (`id`, `nome`, `citta`, `telefono`, `email`, `indirizzo`, `codice_fiscale`, `data_registrazione`, `data_nascita`, `acquisto`, `importo`, `pagamento`, `note`, `acconto`, `interessi`) VALUES
(1, 'Mario Rossi',   'Milano', '000 0000001', 'mario.rossi@example.com',   'Via Esempio 1',  'XXXXXX00X00X000X', '2025-01-10', '1980-05-12', 'Prodotto demo A', 1200.00, 'finanziamento_interno', 'Cliente di esempio', 200.00, 0.00),
(2, 'Laura Bianchi', 'Torino', '000 0000002', 'laura.bianchi@example.com', 'Via Esempio 2',  'XXXXXX00X00X000X', '2025-02-03', '1975-09-21', 'Prodotto demo B', 800.00,  'immediato',             NULL,                 0.00,   0.00),
(3, 'Giorgio Verdi', 'Roma',   '000 0000003', 'giorgio.verdi@example.com', 'Via Esempio 3',  'XXXXXX00X00X000X', '2025-03-15', '1990-01-30', 'Prodotto demo C', 1500.00, 'rateale_compass',       NULL,                 300.00, 0.00);

INSERT INTO `rate` (`cliente_id`, `numero_rata`, `importo`, `data_scadenza`, `pagata`, `data_pagamento`, `note`) VALUES
(1, 1, 250.00, '2025-02-10', 1, '2025-02-09', NULL),
(1, 2, 250.00, '2025-03-10', 1, '2025-03-11', NULL),
(1, 3, 250.00, '2025-04-10', 0, NULL, NULL),
(1, 4, 250.00, '2025-05-10', 0, NULL, NULL),
(2, 1, 800.00, '2025-02-03', 1, '2025-02-03', 'Pagamento immediato');

INSERT INTO `lead` (`nome`, `citta`, `telefono`, `email`, `data_ricontatto`, `esito`) VALUES
('Anna Neri',    'Bologna', '000 0000004', 'anna.neri@example.com',    '2025-04-01', 'ricontattare'),
('Paolo Gialli', 'Napoli',  '000 0000005', 'paolo.gialli@example.com', NULL,         'non interessato');

INSERT INTO `uscite` (`descrizione`, `importo`, `note`, `tipo`, `data_uscita`) VALUES
('Affitto ufficio (demo)', 600.00, NULL, 'ricorrente', '2025-03-01'),
('Materiale di cancelleria (demo)', 45.50, NULL, 'una_tantum', '2025-03-12');
