<?php 
// ** ============= FUNZIONI GENERALI ============= **//


/**
 * Controlla i permessi dell'utente e reindirizza se necessario.
 *
 * @param int $profilo_sistema Livello del profilo utente
 */
function checkPermissions($profilo_sistema) {
    if ($profilo_sistema > 3) {
        header("Location: ./divieto_accesso.php");
        exit;
    }
}


/**
 * Esegue una query parametrizzata sul database.
 *
 * @param resource $conn Connessione al database
 * @param string $query Query SQL parametrizzata
 * @param array $params Parametri della query
 * @return array|null Risultati della query o null se non ci sono risultati
 */
function executeQuery($conn, $query, $params = []) {
    $result = pg_query_params($conn, $query, $params);

    if (!$result) {
        error_log("Errore nella query: " . pg_last_error($conn));
        return null;
    }

    $rows = [];
    while ($row = pg_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}


// ** ============= DATI GENERICI ============= **//


/**
 * Recupera i dettagli di un evento dato il suo ID.
 *
 * @param resource $conn Connessione al database
 * @param int $id ID dell'evento
 * @return array|null Dati dell'evento o null se non trovato
 */
function getEvento($conn, $id) {
    $query = "SELECT e.id, tt.id as id_evento, tt.descrizione, n.nota, 
                     to_char(e.data_ora_inizio_evento, 'DD/MM/YYYY HH24:MI'::text) AS data_ora_inizio_evento, 
                     to_char(e.data_ora_chiusura, 'DD/MM/YYYY HH24:MI'::text) AS data_ora_chiusura, 
                     to_char(e.data_ora_fine_evento, 'DD/MM/YYYY HH24:MI'::text) AS data_ora_fine_evento
              FROM eventi.t_eventi e
              JOIN eventi.join_tipo_evento t 
                ON t.id_evento = e.id
              LEFT JOIN eventi.t_note_eventi n 
                ON n.id_evento = e.id
              JOIN eventi.tipo_evento tt 
                ON tt.id = t.id_tipo_evento
              WHERE e.id = $1;";
       
    $result = executeQuery($conn, $query, [$id]);

    return $result[0];
}

function getMunicipi($conn, $id) {
    $query = "SELECT  b.nome_munic 
            FROM eventi.join_municipi a,geodb.municipi b  
            WHERE a.id_evento = $1 
            AND a.id_municipio::integer=b.codice_mun::integer;";
       
    $result = executeQuery($conn, $query, [$id]);

    return $result;
}


// ** ============= NUMERO VERDE ============= **//


/**
 * Controlla se il Numero Verde è attivo o no per un determinato evento.
 *
 * @param resource $conn Connessione al database PostgreSQL.
 * @param int $id ID dell'evento per cui controllare lo stato.
 * @return int Il numero di record del Numero Verde trovati (0 se non attivo, >0 se attivo).
 */
function getNumVerdeStatus($conn, $id) {
    $query = "SELECT COUNT(*) as count 
              FROM eventi.t_attivazione_nverde 
              WHERE id_evento = $1 
              AND data_ora_fine <= now();";

    $result = executeQuery($conn, $query, [$id]);

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        return (int) $row['count'];
    }

    return 0; // Nessun record trovato
}

/**
 * Recupera il numero di richieste generiche e segnalazioni per un evento specifico.
 *
 * @param resource $conn Connessione al database PostgreSQL.
 * @param int $id ID dell'evento.
 * @return array Array associativo contenente il numero di richieste generiche e segnalazioni.
 */
function getChiamateRicevute($conn, $id) {
    $results = [
        'richieste_generiche' => 0,
        'segnalazioni' => 0,
    ];

    // Query per le richieste generiche
    $queryRichieste = "SELECT COUNT(r.id) AS count
                       FROM segnalazioni.t_richieste_nverde r
                       WHERE r.id_evento = $1;";
    $result = executeQuery($conn, $queryRichieste, [$id]);
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $results['richieste_generiche'] = (int)$row['count'];
    }

    // Query per le segnalazioni
    $querySegnalazioni = "SELECT COUNT(r.id) AS count
                          FROM segnalazioni.t_segnalazioni r
                          WHERE r.id_evento = $1;";
    $result = executeQuery($conn, $querySegnalazioni, [$id]);
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $results['segnalazioni'] = (int)$row['count'];
    }

    return $results;
}

/**
 * Genera l'HTML per il messaggio di stato e lo storico del Numero Verde.
 *
 * @param resource $conn Connessione al database PostgreSQL.
 * @param int $id ID dell'evento per cui formattare i dati.
 * @return string HTML formattato con lo stato e lo storico del Numero Verde.
 */
function numeroVerdeFormatter($conn, $id) {
    // Controlla lo stato del numero verde
    $status = getNumVerdeStatus($conn, $id);

    // Formatta HTML per rappresentare lo stato attuale
    $html = "<h4>";
    if ($status > 0) {
        $html .= "<i>Numero verde attivo</i>";
    } else {
        $html .= "<i>Numero verde non attivo</i>";
    }
    $html .= "</h4>";

    // Recupera lo storico del numero verde
    $query = "SELECT * 
              FROM eventi.t_attivazione_nverde 
              WHERE id_evento = $1 
              AND data_ora_fine <= now();";

    $result = executeQuery($conn, $query, [$id]);

    // Formatta HTML per rappresentare lo storico numero verde
    if ($result && pg_num_rows($result) > 0) {
        $html .= "<h5>Storico Numero Verde</h5><ul>";
        while ($row = pg_fetch_assoc($result)) {
            $timestampStart = strtotime($row["data_ora_inizio"]);
            setlocale(LC_TIME, 'it_IT.UTF8');
            $dataStart = strftime('%A %e %B %G', $timestampStart);
            $oraStart = date('H:i', $timestampStart);

            $timestampEnd = strtotime($row["data_ora_fine"]);
            $dataEnd = strftime('%A %e %B %G', $timestampEnd);
            $oraEnd = date('H:i', $timestampEnd);

            $color = htmlspecialchars(str_replace("'", "", $row["rgb_hex"]));

            $html .= "<li>";
            $html .= "<i class=\"fas fa-circle fa-1x\" style=\"color:$color\"></i> ";
            $html .= "<b>Numero Verde Attivo</b>: ";
            $html .= "dalle <span style=\"font-weight:bold;\">$oraStart</span> di <span style=\"font-style:italic;\">$dataStart</span> ";
            $html .= "alle ore <span style=\"font-weight:bold;\">$oraEnd</span> di <span style=\"font-style:italic;\">$dataEnd</span>";
            $html .= "</li>";
        }
        $html .= "</ul>";
    }

    return $html;
}

/**
 * Ottiene la data e l'ora correnti formattate con flessibilità.
 *
 * @param string $timezone Il fuso orario (default: 'Europe/Rome')
 * @param string $dateFormat Il formato della data (default: 'd/m/Y')
 * @param string $timeFormat Il formato dell'ora (default: 'H:i')
 * @return array Data e ora formattate.
 */
function getCurrentDateTime($timezone = 'Europe/Rome', $dateFormat = 'd/m/Y', $timeFormat = 'H:i') {
    try {
        // Crea un nuovo oggetto DateTime con il fuso orario specificato
        $dateTime = new DateTime('now', new DateTimeZone($timezone));

        // Formatta la data e l'ora
        $currentDate = $dateTime->format($dateFormat);
        $currentTime = $dateTime->format($timeFormat);

        return [
            'date' => $currentDate,
            'time' => $currentTime,
        ];
    } catch (Exception $e) {
        // Gestione dell'errore
        error_log("Errore nella generazione della data/ora: " . $e->getMessage());
        return [
            'date' => 'N/D',
            'time' => 'N/D',
        ];
    }
}


// ** ============= EVENTI ============= **//


/**
 * Determina i dettagli dell'evento e le viste da usare.
 *
 * @param string|null $inizio_evento Data e ora di inizio dell'evento
 * @param string|null $chiusura_evento Data e ora di inizio fase di chiusura
 * @param string|null $fine_evento Data e ora di chiusura definitiva
 * @return array Dettagli dell'evento e viste
 */
function processEventDetails($inizio_evento, $chiusura_evento, $fine_evento) {
    $details = [];
    $check_chiusura = 0;

    // Aggiungi "Data e ora inizio fase di chiusura" se presente
    if (!empty($chiusura_evento)) {
        $details[] = '<b>Data e ora inizio fase di chiusura</b>: ' . htmlspecialchars($chiusura_evento);
    }

    // Aggiungi "Data e ora chiusura definitiva" se presente
    if (!empty($fine_evento)) {
        $details[] = '<b>Data e ora chiusura definitiva</b>: ' . htmlspecialchars($fine_evento);
    }

    // Stato dell'evento
    if (!empty($chiusura_evento) && empty($fine_evento)) {
        $details[] = '<i class="fas fa-hourglass-end"></i> Evento in chiusura';
    }
    if (!empty($chiusura_evento) && !empty($fine_evento)) {
        $check_chiusura = 1;
        $details[] = '<i class="fas fa-stop"></i> Evento chiuso';
    }

    // Determina le viste da usare
    $views = [];
    if ($check_chiusura === 0) {
        $views = [
            'v_incarichi_last_update' => 'v_incarichi_last_update',
            'v_incarichi_interni_last_update' => 'v_incarichi_interni_last_update',
            'v_sopralluoghi_last_update' => 'v_sopralluoghi_last_update',
            'v_sopralluoghi_mobili_last_update' => 'v_sopralluoghi_mobili_last_update',
            'v_provvedimenti_cautelari_last_update' => 'v_provvedimenti_cautelari_last_update',
        ];
    } else {
        $views = [
            'v_incarichi_last_update' => 'v_incarichi_eventi_chiusi_last_update',
            'v_incarichi_interni_last_update' => 'v_incarichi_interni_eventi_chiusi_last_update',
            'v_sopralluoghi_last_update' => 'v_sopralluoghi_eventi_chiusi_last_update',
            'v_sopralluoghi_mobili_last_update' => 'v_sopralluoghi_mobili_eventi_chiusi_last_update',
            'v_provvedimenti_cautelari_last_update' => 'v_provvedimenti_cautelari_eventi_chiusi_last_update',
        ];
    }

    return [
        'details' => $details,
        'views' => $views,
    ];
}


// ** ============= SEGNALAZIONI ============= **//

/**
 * Restituisce un'icona e un messaggio formattato in base allo stato.
 *
 * @param string $value Valore dello stato ('t', 'f', null).
 * @return string HTML contenente l'icona e il messaggio.
 */
function nameFormatter($value) {
    if ($value === 't') {
        return '<i class="fas fa-play" style="color:#5cb85c"></i> in lavorazione';
    } elseif ($value === 'f') {
        return '<i class="fas fa-stop" style="color:#cb3234"></i> chiusa';
    } else {
        return '<i class="fas fa-exclamation" style="color:#ff0000"></i> da prendere in carico';
    }
}

/**
 * Restituisce un link con pulsante per modificare i dettagli di una segnalazione.
 *
 * @param int $value ID della segnalazione.
 * @return string HTML con il pulsante di modifica.
 */
function nameFormatterEdit($value) {
    return '<a class="btn btn-warning" href="./dettagli_segnalazione.php?id=' . $value . '">
                <i class="fas fa-edit"></i>
            </a>';
}

/**
 * Restituisce un'icona per indicare il rischio in base allo stato.
 *
 * @param string $value Valore dello stato di rischio ('t', 'f', null).
 * @return string HTML contenente l'icona del rischio.
 */
function nameFormatterRischio($value) {
    if ($value === 't') {
        return '<i class="fas fa-exclamation-triangle" style="color:#ff0000"></i>';
    } elseif ($value === 'f') {
        return '<i class="fas fa-check" style="color:#5cb85c"></i>';
    } else {
        return '<i class="fas fa-question" style="color:#505050"></i>';
    }
}

/**
 * Genera il codice HTML per una mappa interattiva in una modale.
 *
 * @param int $value ID della segnalazione.
 * @param array $row Dati della riga contenente latitudine e longitudine.
 * @return string HTML per la mappa interattiva.
 */
function nameFormatterMappa1($value, $row) {
    return '
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#myMap' . $value . '">
            <i class="fas fa-map-marked-alt"></i>
        </button>
        <div class="modal fade" id="myMap' . $value . '" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Anteprima segnalazione ' . $value . '</h4>
                    </div>
                    <div class="modal-body">
                        <iframe class="embed-responsive-item" style="width:100%; height:600px;" 
                                src="./mappa_leaflet.php#17/' . htmlspecialchars($row['lat']) . '/' . htmlspecialchars($row['lon']) . '">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    ';
}


/**
 * Recupera e formatta i dettagli delle segnalazioni per un evento.
 *
 * @param resource $conn Connessione al database.
 * @param int $id ID dell'evento.
 * @param string $v_incarichi_last_update Nome della vista incarichi.
 * @param string $v_incarichi_interni_last_update Nome della vista incarichi interni.
 * @param string $v_sopralluoghi_last_update Nome della vista sopralluoghi.
 * @param string $v_provvedimenti_cautelari_last_update Nome della vista provvedimenti cautelari.
 * @return string HTML formattato con i dettagli delle segnalazioni.
 */
function fetchDettaglioSegnalazioni($conn, $id, $v_incarichi_last_update, $v_incarichi_interni_last_update, $v_provvedimenti_cautelari_last_update, $v_sopralluoghi_last_update) {
    $query = "SELECT 
            min(s.data_ora) as data_ora,
            count(s.id) AS num,
            string_agg(s.id::text, ', '::text) AS id_segn,
            string_agg(s.descrizione::text, ', '::text) AS descrizione,
            array_to_string(array_agg(DISTINCT c.descrizione::text), ', '::text) AS criticita,
            array_to_string(array_agg(DISTINCT m.nome_munic::text), ', '::text) AS nome_munic,
            string_agg(
                CASE
                    WHEN s.id_civico IS NULL 
                        THEN ( SELECT concat('~ ', civici.desvia, ' ', civici.testo) AS concat
                            FROM geodb.civici
                            WHERE civici.geom && st_expand(st_transform(s.geom, 3003), 250::double precision)
                            ORDER BY st_distance(civici.geom, st_transform(s.geom, 3003))
                            LIMIT 1
                        )
                    ELSE (g.desvia::text || ' '::text) || g.testo::text
                END, ', '::text
            ) AS localizzazione,
            jl.id_segnalazione_in_lavorazione AS id_lavorazione,
            l.in_lavorazione,
            l.descrizione_chiusura,
            l.id_profilo,
            CASE
                WHEN ((SELECT count(i.id) 
                        FROM segnalazioni.\"$v_incarichi_last_update\" i
                        WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_incarico < 3)) > 0 OR 
                        ((SELECT count(i.id) 
                        FROM segnalazioni.\"$v_incarichi_interni_last_update\" i
                        WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_incarico < 3)) > 0 OR 
                        ((SELECT count(i.id) 
                        FROM segnalazioni.\"$v_provvedimenti_cautelari_last_update\" i
                        WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_provvedimenti_cautelari < 3)) > 0 OR 
                        ((SELECT count(i.id) 
                        FROM segnalazioni.\"$v_sopralluoghi_last_update\" i
                        WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_sopralluogo < 3)) > 0 
                THEN 't'
                ELSE 'f'
            END AS incarichi,
            (
                SELECT count(i.id) AS sum
                FROM segnalazioni.t_incarichi i
                JOIN segnalazioni.join_segnalazioni_incarichi j 
                    ON j.id_incarico= i.id
                WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione
            ) AS conteggio_incarichi,
            (
                SELECT count(i.id) AS sum
                FROM segnalazioni.t_incarichi_interni i
                JOIN segnalazioni.join_segnalazioni_incarichi_interni j 
                    ON j.id_incarico= i.id
                WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione
            ) AS conteggio_incarichi_interni,
            (
                SELECT count(i.id) AS sum
                FROM segnalazioni.t_sopralluoghi i
                JOIN segnalazioni.join_segnalazioni_sopralluoghi j 
                    ON j.id_sopralluogo= i.id
                WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione
            ) AS conteggio_sopralluoghi,
            (
                SELECT count(i.id) AS sum
                FROM segnalazioni.t_provvedimenti_cautelari i
                JOIN segnalazioni.join_segnalazioni_provvedimenti_cautelari j ON j.id_provvedimento = i.id
                WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione
            ) AS conteggio_pc,
            max(s.geom::text) AS geom
        FROM segnalazioni.t_segnalazioni s
        JOIN segnalazioni.tipo_criticita c 
            ON c.id = s.id_criticita
        JOIN eventi.t_eventi e 
            ON e.id = s.id_evento
        LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl 
            ON jl.id_segnalazione = s.id
        LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l 
            ON jl.id_segnalazione_in_lavorazione = l.id
        LEFT JOIN geodb.municipi m 
            ON s.id_municipio = m.id::integer
        LEFT JOIN geodb.civici g 
            ON g.id = s.id_civico
        WHERE s.id_evento = $1 AND jl.id_segnalazione_in_lavorazione > 0
        GROUP BY jl.id_segnalazione_in_lavorazione, l.in_lavorazione, l.id_profilo, s.id_evento, e.fine_sospensione, l.descrizione_chiusura
        ORDER BY data_ora ASC;";

    $result = pg_query_params($conn, $query, [$id]);

    if (!$result) {
        return [];
    }

    return pg_fetch_all($result);
}


function getDettaglioSegnalazioni($conn, $id, $v_incarichi_last_update, $v_incarichi_interni_last_update, 
                                    $v_provvedimenti_cautelari_last_update, $v_sopralluoghi_last_update, $esteso) {
    $dati = fetchDettaglioSegnalazioni($conn, $id, $v_incarichi_last_update, $v_incarichi_interni_last_update, 
                                        $v_provvedimenti_cautelari_last_update, $v_sopralluoghi_last_update);
    
    if (!$dati) {
        return "<p>Errore nella query o nessun dato trovato.</p>";
    }

    $output = '';
    foreach ($dati as $r) {
        ob_start();
        
        if ($esteso) {
            include './templates/template_dettaglio_segnalazioni_esteso.php';
        } else {
            include './templates/template_dettaglio_segnalazioni.php';
        }
        
        $output .= ob_get_clean();
    }

    // AGGIUNTA PRESIDI PER REPORT ESTESO
    if ($esteso) {
        ob_start();
        include './templates/template_presidi.php';
        $output .= ob_get_clean();
    }

    return $output;
}



// ** ============= PROVVEDIMENTI CAUTELARI ============= **//

/**
 * Recupera e formatta il totale dei residenti allontanati
 */
function getElencoProvvedimentiCautelari($conn, $id) {
    $query = "SELECT sum(residenti) AS totale_residenti 
              FROM segnalazioni.v_residenti_allontanati 
              WHERE id_evento = $1";
    $result = pg_query_params($conn, $query, [$id]);

    if (!$result) {
        return "<p>Errore nella query per i residenti allontanati.</p>";
    }

    $output = '';
    while ($r = pg_fetch_assoc($result)) {
        if (!is_null($r['totale_residenti'])) {
            $output .= "<br><br><b>Residenti allontanati in questo momento:</b> {$r['totale_residenti']}<br><br>";
        }
    }

    return $output;
}

// ** ============= MIRE ============= **//

/**
 * Funzioni già esistenti per arrotondare l'ora al quarto d'ora
 */
function roundToQuarterHour($now = null) {
    if ($now === null) {
        $now = time();
    }
    $rounded = floor($now / 900) * 900;
    return $rounded;
}

function getMonitoraggioOrari() {
    $orari = [];
    $currentTime = roundToQuarterHour();
    for ($i = 0; $i <= 16; $i++) {
        $time = $currentTime - ($i * 1800); // Sottrai 30 minuti (1800 secondi) per ogni iterazione
        $orari[] = date("H:i", $time);
    }
    return $orari;
}

?>