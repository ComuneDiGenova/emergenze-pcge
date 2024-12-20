#!/bin/sh
set -e

# Valori di default
DEFAULT_DUMP_PATH="/share/dumps"
DEFAULT_MODEL_FILE="emergenze.sql"
DEFAULT_DATA_FILE="emergenze-data.sql"


# DEFAULT_FILE_PATH="/share/dumps/emergenze.sql"

DEFAULT_DB_NAME="postgres"

# Variabili configurabili (puoi modificarle)
DB_USER="$POSTGRES_USER"
DB_HOST="localhost"
DB_PORT="5432"

# Assegnazione dei valori di default
DUMP_PATH="$DEFAULT_DUMP_PATH"
MODEL_FILE="$DEFAULT_MODEL_FILE"
DATA_FILE="$DEFAULT_DATA_FILE"
# FILE_PATH="$DEFAULT_FILE_PATH"
DB_NAME="$DEFAULT_DB_NAME"

# Parsing degli argomenti con getopts
while getopts "f:d:" opt; do
    case $opt in
        p) DUMP_PATH="$OPTARG" ;;  # Percorso dei file SQL
        m) MODEL_FILE="$OPTARG" ;; # Nome del file di modello
        d) DATA_FILE="$OPTARG" ;;  # Nome del file di popolamento
        D) DB_NAME="$OPTARG" ;;    # Nome del database
        *) 
            echo "Uso: $0 [-p <path>] [-m <sql_model_file>] [-d <sql_data_file>] [-D <db_name>]"
            exit 1
            ;;
    esac
done

MODEL_FILE_PATH="${DUMP_PATH%/}/${MODEL_FILE}"
DATA_FILE_PATH="${DUMP_PATH%/}/${DATA_FILE}"

# Controllo se il file esiste
if [ -f "$MODEL_FILE_PATH" ]; then
    echo "File $MODEL_FILE trovato. Avvio del restore del database"

    # Esecuzione del restore
    psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -f "$MODEL_FILE_PATH"

    if [ $? -eq 0 ]; then
        echo "Restore del modello completato con successo!"

        # Controllo se il file esiste
        if [ -f "$DATA_FILE_PATH" ]; then
            echo "File $DATA_FILE trovato. Avvio del restore dei dati del database"

            # Esecuzione del restore
            psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -f "$DATA_FILE_PATH"

            if [ $? -eq 0 ]; then
                echo "Restore dei dati completato con successo!"
            else
                echo "Errore durante il restore dei dati del database."
            fi
        else
            echo "File $MODEL_FILE non trovato nel percorso $DUMP_PATH. Interruzione dello script."
            exit 1
        fi

    else
        echo "Errore durante il restore del modello del database."
    fi
else
    echo "File $MODEL_FILE non trovato nel percorso $DUMP_PATH. Interruzione dello script."
    exit 1
fi
