# MANUALE CONFIGURAZIONE BOT TELEGRAM

Il seguente manuale spiega le impostazioni da rispettare qualora si volessero reinstallare gli ambienti Docker
deputati al funzionamento dei bot telegram del sistema emergenze.
La struttura della cartella prevede un file `docker-compose.yml` deputato alla costruzione da zero dei container docker,
oltre ad un file `template.env` che costituisce la base di partenza da cui creare il file `.env` vero e proprio.

Seguire pertanto i seguenti passaggi:

## 1. CREAZIONE .ENV:
eseguire una copia di `template.env` nella stessa cartella e compilarlo con i token dei bot e i settaggi di connessione appropriati


## 2. POSTGRES:
Aggiornare i permessi postgres, accertandosi che sia abilitato anche l'ip contenuto nel campo `subnet` di `docker-compose.yml`
il percorso del file di configurazione postgres (`pg_hba.conf`) è il seguente:

`/var/lib/pgsql/9.6/data/pg_hba.conf`

Dopodichè riavviare postgres:

`systemctl list-units --type=service --state=running`

il comando mostra tutti i servizi in esecuzione, individuare il corretto nome di postgres e fare un restart.
A questo punto postgres dovrebbe essere riavviato e in grado di accettare chiamate anche dai container che fanno eseguire i bot.

## 3. PERMESSI CARTELLE:
all'interno della cartella `telegram/bots`, il percorso `src` viene creato di default senza i permessi di scrittura, che è pertanto
necessario conferire a mano:

`chmod 777 -R src`

La stessa cosa vale per la cartella `logs` dove vengono salvati i file di log (per il momento solo bot_convocazione_coc.log)

`chmod 777 -R logs`

## 4. AVVIO BOT TELEGRAM:
dalla cartella `/telegram` eseguire il comando:

`docker compose build`

necessario a reperire tutte le ultime modifie effettuate al file `docker-compose.yml`
Dopodichè avviare i container:

`docker compose up -d` che si occuperà di creare i container per entrambi gli ambienti, ed eseguire gli script `bot.py` all'interno
di ognuno di essi. A questo punto i bot saranno entrambi operativi e risponderanno ai comandi.