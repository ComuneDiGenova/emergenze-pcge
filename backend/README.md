# Modulo di interoperabilità

Modulo di interoperabilità sviluppato sulla base di evolutive di integrazione con sistemi di terze parti:

* Verbatel
    Modulo di condivisione incarichi con sistema di gestione interventi della Polizia Locale.

* AlertSystem
    Modulo di gestione chiamate di allerta mediante il servizio di Comunica Italia.

* API Mire e Idrometri
    Esposizone dei dati relativi ai livelli dei corsi d'acqua

## Installazione

### Configurazione

Creare parallelamente al file `settings.py` un file `settings_private.py` con il seguente contenuto
sostituendo i valori corretti ai *placeholder*

```py
# -*- coding: utf-8 -*-

from pydal._compat import urllib_quote

# EMERGENZE_UPLOAD = '/local/absolute/path/to/emergenze_uploads'

DB_PASSWORD = urllib_quote('<password>')
DB_URI = f"postgres://<username>:{DB_PASSWORD}@<hostname or IP>/<db name>"
DB_POOL_SIZE = 10
DB_MIGRATE = False
DB_DECODE_CREDENTIALS = True # Just in case password contains strange characters

# logger settings
LOGGERS = [
    "debug:stdout"
]  # syntax "severity:filename" filename can be stderr or stdout

```

### Compilazione dei moduli

Le istruzioni qui di seguito sono date considerando l'adozione della piattaforma Docker usata per lo sviluppo e la produzione del modulo.

* Compilazione dei container
    ```sh
    docker-compose -f docker-compose-dev.yml build --build-arg UID=$(id -u) --build-arg GID=$(id -g)
    ```
* Lancio dei servizi
    
    ```bash
    docker-compose -f docker-compose-dev.yml up -d
    ```

* Chiusura dei servizi
    ```bash
    docker-compose -f docker-compose-dev.yml down
    ```

## Note sull'uso di Docker

<details>

    <summary>Lanciare i comandi dal container e simili</summary>

Per lanciare un comando da bash nel container

```bash
sudo docker exec <container_id/container_name> echo "I'm inside the container"
```

oppure

```bash
sudo docker exec -it <container_id/container_name> echo "I'm inside the container"
```

Per quanto riguarda listener.py, dopo essere entrati nel container eseguire il set up

```bash
py4web call apps emergenze.listener.setup
```

E mettere il servizio in ascolto

```bash
py4web call apps emergenze.listener.listen
```

A questo punto in maniera speditiva, una volta che il container è già attivo:

```bash
sudo docker exec -d <container_id/container_name> py4web call apps emergenze.listener.listen
```

Controllare comunque il docker-compose.
Per richiamare il listener senza entrare nel container ma in maniera interattiva (va bene accoppiato con pdb)

```bash
sudo docker exec -it 0be784c462d6 py4web call apps emergenze.listener.listen
```

si può testare il listner con una modifica al DB

```sql
INSERT INTO eventi.join_tipo_foc
(id_evento, id_tipo_foc, data_ora_inizio_foc, data_ora_fine_foc)
VALUES(110, 3, NOW(), NOW() + interval '1 hour');
```

</details>

## Attivare e disattivare il servizio
Per tirare su il sistema di container è necessario lanciare dalla shell di comando, posizionandosi nella cartella che contiere il docker-compose.yml di interesse, che segue:
```bash
docker-compose up -d
```
Analogamente per tirare giù il servizio, dalla stessa cartella, eseguire:
```bash
docker-compose down -v
```
Per controllare eventuali log:
```bash
docker-compose logs -f
```
