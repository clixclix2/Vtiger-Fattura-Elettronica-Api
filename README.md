# Vtiger-Fattura-Elettronica-Api
Modulo Vtiger per la creazione e la trasmissione delle Fatture Elettroniche all'Agenzia delle Entrate (SDI) 
tramite il servizio Fattura-Elettronica-Api (https://fattura-elettronica-api.it/)

È stato testato con Vtiger 7.3. Tuttavia, dovrebbe funzionare anche con versioni precedenti e successive.

FUNZIONAMENTO:
- In ogni documento di fattura sarà presente il pulsante "Invia Fattura Elettronica"
- Cliccando, il sistema si interfaccia con Fattura-Elettronica-Api per creare la fattura elettronica e trasmetterla al destinatario tramite l'agenzia delle entrate
- Su Vtiger rimane salvato l'identificativo della trasmissione (Identificativo SDI), lo stato della trasmissione (Inviato, Consegnato, Non consegnato, Errore) e l'eventuale messaggio di errore.
- In caso di errore, il sistema consente di correggere i dati errati ed effettuare un nuovo invio
- In caso di successo, è possibile scaricare una copia della fattura elettronica (xml)
- In fase di installazione del modulo, viene installato su Vtiger anche un cron task (Impostazioni >> CRM Settings >> Automazione >> Scheduler) che ogni 15 minuti interroga Fattura-Elettronica-Api
per verificare lo stato di consegna delle fatture trasmesse.

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

# Legenda stati di trasmissione

La trasmissione di un documento con Fattura-Elettronica-Api può avere i seguenti stati:
- **Inviato** - è stato effettuato l'invio e si attende l'esito
- **Errore** - è stato rilevato un errore nei dati trasmessi: verificare la natura dell'errore, correggere e riprovare la trasmissione
- **Consegnato** - il documento è stato correttamente consegnato al destinatario
- **Non Consegnato** - l'agenzia delle entrate non è riuscita a recapitare il documento al destinatario 
  (succede tipicamente se il destinatario è un privato), tuttavia l'azienda che ha emesso il documento ha ottemperato correttamente al suo obbligo di trasmettere il documento all'agenzia delle entrate
