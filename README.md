# Vtiger-Fattura-Elettronica-Api
Modulo Vtiger per la creazione e la trasmissione delle Fatture Elettroniche all'Agenzia delle Entrate (SDI) 
tramite il servizio Fattura-Elettronica-Api (https://fattura-elettronica-api.it/)

È stato testato con Vtiger 7.3. Tuttavia, dovrebbe funzionare anche con versioni precedenti e successive.

NOTE IMPORTANTI:
- Dopo l'installazione, inserire **username** e **password** Fattura-Elettronica-Api nel file **config.inc.php** (modules/FatturaElettronicaApi/resources/config.inc.php)
- Per effettuare invii di test al servizio FatturaElettronicaApi, impostare $config['send_test'] = true;
- In fase di installazione, vengono creati, tra gli altri, i campi Partita IVA e Codice Fiscale sui moduli Account (tabella vtiger_account) e Contact (vtiger_contactdetails),
  se si dispone già di tali campi e si vuole utilizzarli, seguire le istruzioni su config.inc.php e poi cancellare i campi dalla tabella vtiger_field

Il modulo viene rilasciato sotto licenza GPLv3, senza alcuna garanzia formale per l'utilizzatore.

Il presente modulo è stato derivato da: https://github.com/TommasoBilotta/vtiger-fattura-elettronica

# Installazione

- Scaricare il file FatturaElettronicaApi.zip
- Accedere su Impostazioni >> CRM Settings >> Module Management >> Moduli | cliccare [Import Module from Zip] 
e caricare il file zip.

# Assistenza

Per assistenza: https://fattura-elettronica-api.it/contatti/
