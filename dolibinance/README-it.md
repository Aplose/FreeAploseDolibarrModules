# DOLIBINANCE PER DOLIBARR

![Screenshot dolibinance](img/screenshot_dolibinance.png?raw=true "DoliBinance")

## Funzionalità

Questo modulo collega il tuo account Binance al tuo Dolibarr.
Se non ne hai ancora uno, segui questo link: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280).
Ciò ti permetterà di aggiungere facilmente il pagamento in Bitcoin e accettare qualsiasi altra criptovaluta supportata da Binance.
Quando un cliente desidera pagare con criptovaluta, il modulo utilizza il tasso medio attuale su 24 ore della criptovaluta per il pagamento della fattura, nonché l'indirizzo del tuo portafoglio Binance.
Il cliente inserisce quindi le informazioni sulla transazione che ha effettuato: ID della transazione, indirizzo di invio, importo. Non appena il deposito arriva sul tuo account Binance, la fattura viene contrassegnata come "pagata".
Puoi monitorare il saldo dei tuoi asset di criptovaluta nel tuo portafoglio spot Binance direttamente in Dolibarr, senza dover utilizzare l'applicazione Binance.

## Prossime funzionalità
Le autorizzazioni degli utenti non sono ancora state sviluppate in questa versione e verranno implementate successivamente. Tutte le funzionalità sono accessibili gratuitamente a tutti gli utenti di Dolibarr, tuttavia, alcune azioni importanti richiedono i privilegi di amministratore.
In base alle esigenze contabili riscontrate durante l'uso del modulo, gli elementi corrispondenti saranno creati automaticamente in Dolibarr.
Non esitate a segnalare le vostre osservazioni a [Olivier Andrade Sanchez](mailto:oandrade@aplose.fr?subject=[DoliBinanceRequest]-).

## Configurazione

### Dizionario
Per offrire ai tuoi clienti un pagamento in criptovaluta, devi aggiungere le informazioni necessarie nel dizionario "Elenco degli indirizzi di pagamento Binance" (Home->Configurazione->Dizionario), ovvero gli indirizzi di ricezione delle criptovalute che desideri accettare come pagamento delle tue fatture.
![Screenshot dictionnary 1](img/doc-010-dictionnary.png?raw=true "Dizionario 1")
In questo dizionario, inserisci le informazioni relative ai tuoi diversi indirizzi di ricezione creati su Binance ([vedi la documentazione Binance per questo](https://www.binance.com/fr/support/faq/comment-d%C3%A9poser-des-cryptos-sur-binance-115003764971)).
Ad esempio, in questo caso, sono stati inseriti tre indirizzi (due per Bitcoin su due diverse reti).
![Screenshot dictionnary 2](img/doc-011-dictionnary.png?raw=true "Dizionario 2")
Come puoi vedere, puoi attivare o disattivare le criptovalute senza cancellare la configurazione.

### Pagina delle impostazioni
L'uso di questo modulo richiede l'esistenza di un account Binance.
Perché Binance? Perché è il più grande exchange di criptovalute al mondo. Tuttavia, fai attenzione, non possiedi le chiavi private e pubbliche del portafoglio corrispondente, quindi ti consiglio di utilizzare questa piattaforma solo per inviare e ricevere pagamenti o per fare trading.
Se desideri proteggere le tue criptovalute, preferisci un portafoglio hardware come una chiave Ledger ([leggi l'articolo a riguardo qui](https://www.cryptocolo.fr/2023/02/08/not-your-keys-not-your-coins/)).
Per creare il tuo account Binance, non esitare a utilizzare il nostro link di riferimento Binance: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280)
Ci sono molti vantaggi nel far parte dei miei affiliati (tra cui l'uso di un bot di trading specifico, contattami per ulteriori informazioni).
#### Configurazione delle chiavi API
Inizialmente, crea le tue chiavi API su Binance seguendo questa documentazione (concedi solo autorizzazioni in lettura): [Crea le tue chiavi API su Binance](https://www.binance.com/fr/support/faq/comment-cr%C3%A9er-des-cl%C3%A9s-api-sur-binance-360002502072)
Successivamente, inserisci le tue chiavi API nella pagina di configurazione del modulo:
![Screenshot setup](img/doc-020-setup.png?raw=true "Impostazioni")
Noterai che il modulo fornisce il prezzo attuale del Bitcoin, la criptovaluta di riferimento.

## Utilizzo

### Fatturazione
Ora le tue fatture standard possono essere pagate in criptovalute!
Puoi utilizzare il link di pagamento fornito da Dolibarr e visualizzato sulla tua fattura:
![Screenshot use 1](img/doc-030-use.png?raw=true "Uso 1")
Questo URL può essere incorporato direttamente nei tuoi modelli di email utilizzando la variabile di sostituzione: \__ONLINE_PAYMENT_TEXT_AND_URL__.

### Pagina di pagamento: passo 1
Seguendo questo link, il tuo cliente accede alla pagina di pagamento online di Dolibarr. Nel primo passo, sceglie la coppia Crypto/Rete che preferisce dalla lista proposta:
![Screenshot use 2](img/doc-040-use.png?raw=true "Uso 2")

### Pagina di pagamento: passo 2
Nel secondo passo, gli viene presentato l'importo corrente da pagare nella criptovaluta scelta. Dovrà inserire le informazioni sulla transazione che ha effettuato o sta per effettuare (l'ID della transazione non è obbligatorio e verrà ottenuto automaticamente da Binance in seguito):
![Screenshot use 3](img/doc-050-use.png?raw=true "Uso 3")

### Pagina di pagamento: passo 3
Nell'ultimo passo, gli viene presentato il riepilogo del suo pagamento.
![Screenshot use 4](img/doc-060-use.png?raw=true "Uso 4")

### Portafoglio Binance
Puoi visualizzare lo stato dei saldi delle tue criptovalute nel tuo portafoglio Spot Binance utilizzando il link "Portafoglio Binance" nel menu a sinistra:
![Screenshot use 5](img/doc-070-use.png?raw=true "Uso 5")

### Cronologia dei depositi nel tuo portafoglio
Puoi visualizzare la cronologia dei depositi effettuati negli ultimi 90 giorni sugli indirizzi di deposito che hai creato su Binance utilizzando il collegamento del menu "Cronologia dei depositi nel tuo portafoglio".
Lo stato "1" indica una transazione confermata (e quindi considerata da DoliBinance):
![Screenshot use 6](img/doc-080-use.png?raw=true "Uso 6")

### Transazioni
I record delle pagine di pagamento effettuati dai tuoi clienti sono elencati qui; uno stato "1" indica una transazione completata, ricevuta su Binance e la relativa fattura contrassegnata come "Pagata":
![Screenshot use 7](img/doc-090-use.png?raw=true "Uso 7")

### Job di convalida delle transazioni (lavori automatizzati)
Le transazioni blockchain possono richiedere diversi minuti per essere confermate dalla rete corrispondente, quindi non è possibile far aspettare il tuo cliente sulla pagina di pagamento.
Abbiamo scelto un approccio di convalida differita delle transazioni, con la verifica regolare dei depositi sugli indirizzi di ricezione.
Il job viene avviato idealmente ogni minuto, se la tua configurazione lo consente (assicurati che la crontab sia attiva sul tuo server):
![Screenshot use 8](img/doc-100-use.png?raw=true "Uso 8")

Il job è modificabile come qualsiasi processo automatizzato in Dolibarr, e puoi modificarlo per eseguirlo solo una volta all'ora, ad esempio. Puoi anche scegliere di avviarlo manualmente:
![Screenshot use 9](img/doc-110-use.png?raw=true "Uso 9")

Una volta che la transazione è stata convalidata dalla rete e il job è stato eseguito, la relativa fattura verrà automaticamente contrassegnata come "Pagata" e verrà aggiunta una nota privata per indicare l'azione di DoliBinance:
![Screenshot use 10](img/doc-120-use.png?raw=true "Uso 10")

## Rimaniamo in contatto!
Questo modulo è una prima versione che evolverà e migliorerà in base ai vostri feedback. Non esitate a condividere le vostre idee e osservazioni con [oandrade@aplose.fr](mailto:oandrade@aplose.fr).

## Traduzioni

Le traduzioni possono essere completate manualmente modificando i file nelle directory *langs*.

## Licenze

### Codice principale

GPLv3.

### Documentazione

Tutti i testi e i readme sono sotto licenza GFDL.
