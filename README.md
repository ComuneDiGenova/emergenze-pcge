# Instruction manual

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

```bash
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

Per accedere all'applicazione cerca da browser <http://localhost:8000/emergenze/evento>

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
