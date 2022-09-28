## Configuration

just create a file called `settings_private.py` with the following content replacing
the placeholders with the right values:

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
## Docker

I due file docker-compose-yml e Dockerfile devono stare una cartella sopra al progetto, da modificare la struttura (e questo readme)

```
#
emergenze_verbatel
|     docker-compose.yml
|     Dockerfile
|     requirements.txt
|____ Emergenze-Verbatel
     |       __init__.py
     |_______models
     |_______static

     ....

```
Per accedere all'applicazione cerca da browser http://localhost:8000/emergenze/evento


<details>
<summary>Lanciare i comandi dal container e simili</summary>
<br>
Per lanciare un comando da bash nel container
<pre>
sudo docker exec <container_id/container_name> echo "I'm inside the container"
</pre>
oppure
<pre>
sudo docker exec -it <container_id/container_name> echo "I'm inside the container"
</pre>
Per quanto riguarda listener.py, dopo essere entrati nel container eseguire il set up
<pre>
py4web call apps emergenze.listener.setup
</pre>
E mettere il servizio in ascolto
<pre>
py4web call apps emergenze.listener.listen
</pre>

A questo punto in maniera speditiva, una volta che il container è già attivo:

<pre>
sudo docker exec -d <container_id/container_name> py4web call apps emergenze.listener.listen
</pre>

Controllare comunque il docker-compose.
Per richiamare il listener senza entrare nel container ma in maniera interattiva (va bene accoppiato con pdb)

<pre>
sudo docker exec -it 0be784c462d6 py4web call apps emergenze.listener.listen
</pre>

si può testare il listner con una modifica al DB
<pre>
INSERT INTO eventi.join_tipo_foc
(id_evento, id_tipo_foc, data_ora_inizio_foc, data_ora_fine_foc)
VALUES(110, 3, NOW(), NOW() + interval '1 hour');
</pre>
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

### Problemi

#### Non raggiungibilità portale Gestione Emergenze

Sono segnalati occasionali blocchi degli accessi al portale di Gestione Emergenze
che a valle dell'autenticazione tramite SPID risponde con un *proxy error*.

Promemoria delle cose da verificare in queste occasioni:

* consumo CPU dei servizi
* stato di occupazione del filesystem
* panoramica delle query al db e relativi consumi di risorse
* ... (altre idee?)

**Soluzione**

Per far ripartire il servizio dovrebbe bastare il riavvio del container web:

```sh
cd ~/emergenze_verbatel
sudo docker-compose restart web
```

o in alternativa per riavviare entrambi i container definiti:

```sh
cd ~/emergenze_verbatel
sudo docker-compose down -v
sudo docker-compose up -d
```
