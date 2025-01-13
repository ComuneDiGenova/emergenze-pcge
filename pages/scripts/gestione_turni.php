<?php
function renderShiftSection($params, $conn, $profilo_sistema, $id_evento = null) {

    $query_dipendenti = "SELECT matricola, cognome, nome, settore, ufficio 
        FROM varie.v_dipendenti_all 
        ORDER BY cognome;";

    $query_meteo = "SELECT cf as matricola, cognome, nome, '' as livello1 
        FROM users.v_utenti_esterni 
        WHERE id1=9
        UNION 
        SELECT matricola, cognome, nome, settore || ' - '|| ufficio as livello1 
        FROM varie.v_dipendenti_all
        ORDER BY cognome;";

    $query_volontari = "SELECT cf as matricola, cognome, nome, livello1
        FROM users.v_utenti_esterni 
        WHERE id1=1 or id1=8
        UNION 
        SELECT matricola AS cf, cognome, nome, settore || ' - '|| ufficio as livello1
        FROM varie.v_dipendenti_all
        ORDER BY cognome;";

    // Scomposizione dei parametri
    $title = $params['title'];
    $modalId = $params['modal_id'];
    $dbTable = $params['db_table'];
    $query_type = $params['query_type'];
    $emptyMessage = $params['emptyMessage'] ?? "Nessun record trovato."; // Messaggio di default

    // renderizzo la query in base a query_type
    if ($query_type == 'dipendenti') {
        $personnelQuery = $query_dipendenti;
    } else if ($query_type == 'meteo') {
        $personnelQuery = $query_meteo;
    } else if ($query_type == 'volontari') {
        $personnelQuery = $query_volontari;
    }
    

    // Titolo e pulsante Aggiungi
    echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 shift-container">';
    echo "<hr><h4>$title";

    if ($profilo_sistema <= 3) {
        echo <<<HTML
        <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#{$modalId}">
            <i class="fas fa-plus"></i> Aggiungi
        </button>
        </h4>
HTML;
    } else {
        echo "</h4>";
    }

    // Modal per l'aggiunta del personale
    if ($profilo_sistema <= 3) {
        $result = pg_query($conn, $personnelQuery);
        $personnelList = pg_fetch_all($result);

        // Opzioni per ore e minuti
        $hours = range(0, 24);
        $minutes = range(0, 59, 15);

        echo <<<HTML
        <div id="{$modalId}" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Inserire {$title}</h4>
                    </div>
                    <div class="modal-body">
                        <form action="./report/nuovo_turno.php" method="POST" class="shift-form">
                            <!-- Campo hidden per passare title e db_table -->
                            <input type="hidden" name="title" value="{$title}">
                            <input type="hidden" name="db_table" value="{$dbTable}">
                            <div class="form-group">
                                <label for="cf">Seleziona personale:</label>
                                
                                <select name="cf" class="selectpicker show-tick form-control" data-live-search="true" required="">
                                    <option value="">Seleziona personale</option>
                                    <option value="NO_TURNO">TURNO VUOTO</option>
HTML;
foreach ($personnelList as $person) {
    // Mappa dei campi personalizzati per ogni modal_id
    $fieldMapping = [
        'new_coord' => ['settore', 'ufficio'],               // Coordinatore di Sala
        'new_mm' => ['livello1'],                            // Monitoraggio Meteo
        'new_pt' => ['settore', 'ufficio'],                 // Operatore Presidi Territoriali
        'new_tPC' => ['settore', 'ufficio'],                // Tecnico Protezione Civile
        'new_oV' => ['livello1'],                           // Operatore Gestione Volontari
        'new_anpas' => ['livello1'],                        // Postazione Presidio Sanitario
        'new_oNV' => ['settore', 'ufficio'],                // Operatore Numero Verde
    ];

    $extraInfo = '';

    if (isset($fieldMapping[$modalId])) {
        $fields = $fieldMapping[$modalId];
        $extraFields = [];
        foreach ($fields as $field) {
            if (!empty($person[$field])) {
                $extraFields[] = htmlspecialchars($person[$field]);
            }
        }
        if (!empty($extraFields)) {
            $extraInfo = ' (' . implode(' - ', $extraFields) . ')';
        }
    }

    echo '<option value="' . htmlspecialchars($person['matricola']) . '">'
        . htmlspecialchars($person['cognome']) . ' ' . htmlspecialchars($person['nome'])
        . $extraInfo . '</option>';
}

        echo <<<HTML
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_inizio">Data inizio (AAAA-MM-GG)</label>
                                <input type="text" class="form-control datepicker" name="data_inizio" id="js-date-{$modalId}" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label>Ora inizio:</label>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <select class="form-control" name="hh_start" required>
                                            <option value="">Ora</option>
HTML;
        foreach ($hours as $hour) {
            $formattedHour = str_pad($hour, 2, "0", STR_PAD_LEFT);
            echo "<option value='{$formattedHour}'>{$formattedHour}</option>";
        }

        echo <<<HTML
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <select class="form-control" name="mm_start" required>
                                            <option value="00">Minuti</option>
HTML;
        foreach ($minutes as $minute) {
            $formattedMinute = str_pad($minute, 2, "0", STR_PAD_LEFT);
            echo "<option value='{$formattedMinute}'>{$formattedMinute}</option>";
        }

        echo <<<HTML
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Data fine (AAAA-MM-GG):</label>
                                <input type="text" class="form-control datepicker" name="data_fine" id="js-date2" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label>Ora fine:</label>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <select class="form-control" name="hh_end" required>
                                            <option value="">Ora</option>
HTML;
        foreach ($hours as $hour) {
            $formattedHour = str_pad($hour, 2, "0", STR_PAD_LEFT);
            echo "<option value='{$formattedHour}'>{$formattedHour}</option>";
        }

        echo <<<HTML
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <select class="form-control" name="mm_end" required>
                                            <option value="00">Minuti</option>
HTML;
        foreach ($minutes as $minute) {
            $formattedMinute = str_pad($minute, 2, "0", STR_PAD_LEFT);
            echo "<option value='{$formattedMinute}'>{$formattedMinute}</option>";
        }

        echo <<<HTML
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Pulsante per aggiungere menu a scelta multipla -->
                            <div class="form-group">
                                <button type="button" id="add-event-checkbox" class="btn btn-secondary">Seleziona Eventi:</button>
                                <div id="event-checkboxes" class="form-control mt-2" style="height: auto; overflow-y: auto;">
HTML;
$eventQuery = "SELECT te.id,
                    te.id || ' - ' || tne.nota as descrizione
            FROM eventi.t_eventi te 
            JOIN eventi.t_note_eventi tne 
                ON te.id = tne.id_evento
            WHERE te.valido = true AND te.data_ora_fine_evento IS NULL;";
$eventResult = pg_query($conn, $eventQuery);
$eventList = pg_fetch_all($eventResult);

foreach ($eventList as $event) {
    echo '<div class="form-check">';
    echo '<input class="form-check-input event-checkbox" type="checkbox" name="id_event_list[]" value="' . htmlspecialchars($event['id']) . '" id="event-' . htmlspecialchars($event['id']) . '">';
    echo '<label class="form-check-label" for="event-' . htmlspecialchars($event['id']) . '">';
    echo '&nbsp;&nbsp;';
    echo htmlspecialchars($event['descrizione']);
    echo '</label>';
    echo '</div>';
}
                                echo <<<HTML
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">Inserisci</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
HTML;
    }

    // Mostra turni
    if ($query_type == 'dipendenti' or $query_type == 'meteo') {
        $query = "SELECT u.cognome, u.nome, r.data_start, r.data_end, 
                    r.warning_turno, EXTRACT(EPOCH FROM (r.data_end - r.data_start)) / 3600 AS duration
                    FROM {$dbTable} r 
                    LEFT JOIN varie.v_dipendenti_all u ON r.matricola_cf = u.matricola";
    } else if ($query_type == 'volontari') {
        $query = "SELECT r.matricola_cf,
            case 
                when u.cognome is not null then u.cognome
                when d.cognome is not null then d.cognome 
            end as cognome,
            case 
                when u.nome is not null then u.nome
                when d.nome is not null then d.nome 
            end  as nome,
            r.data_start, r.data_end, r.warning_turno, r.modificato,
            EXTRACT(EPOCH FROM (r.data_end - r.data_start)) / 3600 AS duration
            from {$dbTable} r
            LEFT JOIN users.v_utenti_esterni u 
                ON r.matricola_cf=u.cf
            LEFT JOIN varie.v_dipendenti_all d 
                ON r.matricola_cf=d.matricola";
    } 


    // Filtra per id_evento solo se è definito
    $id_evento = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;

    if ($id_evento !== null) {
        $query .= "WHERE data_start < (SELECT coalesce(data_ora_chiusura, now()) FROM eventi.t_eventi where id = ".$id_evento.")
            AND data_end >= (select data_ora_inizio_evento FROM eventi.t_eventi where id = ".$id_evento.")
            ANDr.id_evento::jsonb @> '[\"$id_evento\"]'::jsonb";
    } else {
        $query .= "WHERE r.data_start < now() AND r.data_end > now()";
    }

    // aggiungo ORDER BY alla query per completarla
    $query .= " ORDER BY r.data_start;";

    // eseguo la query e la renderizzo
    $result = pg_query($conn, $query);
    $shifts = pg_fetch_all($result);

    echo "<div>";
    if ($shifts) {
        foreach ($shifts as $row) {
            echo "- {$row['cognome']} {$row['nome']} dalle ";
            echo date('H:i', strtotime($row['data_start'])). " del " .date('d/m/Y', strtotime($row['data_start']))." alle ";
            echo date('H:i', strtotime($row['data_end'])). " del " .date('d/m/Y', strtotime($row['data_end']));

            // Mostra warning
            if ($row['warning_turno'] == 't') {
                echo ' <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione"></i>';
            }
            if ($row['duration'] > 10) {
                echo ' <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno supera le 10 ore"></i>';
            }

            echo "<br>";
        }
    } else {
        // emptyMessage se non trova nessuno in turno
        echo "- <i class='fas fa-circle' style='color: red;'></i> {$emptyMessage}<br>";
    }
    echo "</div></div>";
}
?>