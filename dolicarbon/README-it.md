# DoliCarbon per [Dolibarr ERP & CRM](https://www.dolibarr.org)

**DoliCarbon** è un modulo Dolibarr per costruire e gestire **inventari di gas a effetto serra (GES)** nell’istanza: bilanci per esercizio, righe di attività con fattori di emissione, azioni di riduzione, inquadramento metodologico, cruscotti, report e collegamenti opzionali ai flussi di acquisto Dolibarr.

Altri moduli esterni sono disponibili su [Dolistore](https://www.dolistore.com).

## Funzionalità

### Inventari (bilans)

- Creare un inventario per periodo di rendicontazione; ogni record riceve un riferimento automatico **`CARBON-{anno}-{sequenza}`** (vedere la classe `DoliCarbonBilan`).
- Definire gli estremi del periodo (date inizio/fine), collegamento opzionale a un soggetto terzo, **totali** e **obiettivi** in tCO2e, note e stato di workflow (bozza / validato / archiviato).

### Righe di attività (movimenti)

- Registrare le emissioni per **scope** (1, 2 o 3) e **categoria** (tassonomia del modulo), con quantità, unità e collegamento a un **fattore di emissione** ove applicabile.
- Le righe supportano il workflow di validazione, **commenti**, tracciabilità e riferimento agli oggetti sorgente (ad es. fatture fornitore importate).

### Fattori di emissione

- Gestire una libreria di **fattori di emissione** (con campi di versionamento nel modello dati) e attivare o disattivare i fattori secondo lo schema.

### Azioni di riduzione

- Associare **azioni** a un inventario: risparmi stimati, costi, punteggi e stato per seguire il piano di riduzione.

### Inquadramento metodologico (« cadrage »)

- Documentare perimetro, esclusioni, materialità, anni di riferimento e di reporting, e note in un oggetto dedicato usato nella rendicontazione.

### Cruscotto

- Visualizzare indicatori aggregati: totali per scope, serie temporale e principali categorie dai dati d’inventario.

### Report

- Schermata **Report** nell’app integrata: sintesi esecutiva, vista analista, allegato metodologico, testo di **guardrail comunicativo** opzionale, intervallo di **incertezza**, export **CSV** o **JSON** e **istantanea** (snapshot) per un punto nel tempo riproducibile.

### Qualità dei dati e collaborazione

- Schermata **Qualità** per le fasi di workflow, **commenti** e tracciabilità orientata all’audit sulle righe.

### Piano di transizione

- Schermata **Transizione** per confrontare le azioni di riduzione (sintesi tipo costo/beneficio come nell’interfaccia).

### Import da Dolibarr

- Avviare la procedura **Import** per richiamare dati Dolibarr nelle righe d’inventario (vedere `carbon_import.php` e i servizi AJAX collegati).

### Trigger opzionali (amministrazione)

In **Home → Impostazioni → Moduli → DoliCarbon → Configurazione** sono disponibili due opzioni (`DOLICARBON_TRIGGER_NOTIFY` e `DOLICARBON_AUTO_IMPORT_SUPPLIER_INVOICE`):

1. **Messaggi in validazione**  
   Se attivata, la validazione di una **fattura fornitore**, di una **nota spese** o di una **spedizione** può mostrare messaggi Dolibarr verso DoliCarbon (con link all’import per le fatture fornitore).

2. **Riga automatica da fattura fornitore**  
   Se questa opzione e i messaggi sono attivi, la validazione di una **fattura fornitore** può creare automaticamente una riga **Scope 3** nella categoria **`purchases_services`**, usando il **totale imponibile in EUR**, **solo se** esiste un inventario in **bozza** e un **fattore di emissione attivo** coerente (stesso scope e categoria). I duplicati sono evitati tramite hash di importazione.

Se le opzioni sono disattivate, non vengono generati messaggi né righe automatiche.

## Interfaccia utente

- **Applicazione web integrata** (menu **DoliCarbon** → `custom/dolicarbon/index.php`): dashboard, bilans, cadrage, righe, fattori, azioni, qualità, transizione, import, report. È richiesto almeno il diritto di **lettura** sul modulo.
- Le **schermate PHP classiche** restano disponibili (ad es. `carbon_bilan_list.php`, `carbon_factors.php`, altre `carbon_*.php`).

Il bundle Angular include stringhe di interfaccia in **francese** (`assets/i18n/fr.json`). Le traduzioni del modulo Dolibarr sono in **francese** (`fr_FR`) e **inglese** (`en_US`) in `langs/`.

## Permessi

Quattro diritti: **leggere**, **creare/modificare**, **eliminare** e **validare** (validazione / blocco del workflow dell’inventario). Assegnazione in **Utenti e gruppi**.

## Prerequisiti

- **Dolibarr 17** o versione compatibile successiva come da descrittore del modulo (`need_dolibarr_version`).
- **PHP 8.1** o superiore (`phpmin`).

Nessun altro modulo Dolibarr è dichiarato come dipendenza obbligatoria; le funzioni opzionali presuppongono l’esistenza degli oggetti Dolibarr utilizzati (es. fatture fornitore).

## Installazione

Prerequisito: installazione Dolibarr funzionante. Download da [dolibarr.org](https://www.dolibarr.org). Esistono anche offerte in hosting (vedi sotto).

### Da archivio ZIP

Se il modulo è distribuito come `module_dolicarbon-x.y.z.zip` (ad es. da [Dolistore](https://www.dolistore.com)), usare **Home → Impostazioni → Moduli → Distribuisci/installa modulo esterno** e caricare l’archivio.

### Passi finali

1. Accedere come amministratore.
2. Aprire **Impostazioni → Moduli**, abilitare **DoliCarbon**.
3. Aprire **Impostazioni → Moduli → DoliCarbon → Configurazione** per le due opzioni trigger se necessario.

## Configurazione

1. **Configurazione modulo**: attivare o disattivare messaggi e import automatico come sopra.
2. **Permessi**: assegnare almeno **leggere** per aprire l’app; **scrivere**, **eliminare**, **validare** secondo i profili.

## Dolibarr nel cloud (Ma Gestion Cloud)

È possibile eseguire Dolibarr con i moduli Aplose su **[Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)**: hosting, backup e supporto. Registrazione / prova (tracciamento visitatori Dolistore):

**[Crea un account — Ma Gestion Cloud](https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php?origin=dolistore)**

Contatto: [contact@aplose.fr](mailto:contact@aplose.fr)

Piani commerciali e moduli inclusi dipendono dall’abbonamento; rivolgersi a Ma Gestion Cloud per un’offerta adatta.

## Assistenza

- Email: [contact@aplose.fr](mailto:contact@aplose.fr)
- Editore: [Aplose](https://www.aplose.fr)

## Licenze

### Codice principale

GPLv3 o (a scelta) qualsiasi versione successiva. Vedere il file `COPYING`.

### Documentazione

Questa documentazione è sotto [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).

## Versione

Versione attuale del modulo: **1.0.0** (vedere `core/modules/modDoliCarbon.class.php`).
