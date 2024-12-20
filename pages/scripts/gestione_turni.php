<?php
function renderShiftSection($params, $conn, $profilo_sistema) {


    // Scomposizione dei parametri
    $title = $params['title'];
    $modalId = $params['modal_id'];
    // $formAction = $params['form_action'];
    $dbTable = $params['db_table'];
    $personnelQuery = $params['personnel_query'];
    $emptyMessage = $params['emptyMessage'] ?? "Nessun record trovato."; // Messaggio di default

    // Titolo e pulsante Aggiungi
    echo '<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 shift-container">';
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
                        <form action="./report/nuovo_turno.php" method="POST">
                            <!-- Campo hidden per db_table -->
                            <input type="hidden" name="title" value="{$title}">
                            <input type="hidden" name="db_table" value="{$dbTable}">
                            <div class="form-group">
                                <label for="cf">Seleziona personale:</label>
                                <select name="cf" class="form-control" required>
                                    <option value="">Seleziona personale</option>
                                    <option value="NO_TURNO">TURNO VUOTO</option>
HTML;
    foreach ($personnelList as $person) {
        // Formattazione personalizzata in base al `modal_id`
        $extraInfo = '';
        switch ($modalId) {
            case 'new_coord': // Coordinatore di Sala
                if (!empty($person['settore']) && !empty($person['ufficio'])) {
                    $extraInfo = ' (' . htmlspecialchars($person['settore']) . ' - ' . htmlspecialchars($person['ufficio']) . ')';
                }
                break;

            case 'new_mm': // Monitoraggio Meteo
                if (!empty($person['livello1'])) {
                    $extraInfo = ' (' . htmlspecialchars($person['livello1']) . ')';
                }
                break;

            // case 'new_pt': // Operatore Presidi Territoriali
            //     if (!empty($person['settore'])) {
            //         $extraInfo = ' (' . htmlspecialchars($person['settore']) . ')';
            //     }
            //     break;

            default:
                // Formattazione generale
                $extraInfo = '';
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
                                <input type="text" class="form-control datepicker" name="data_inizio" id="js-date-{$modalId}" required>
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
                                <input type="text" class="form-control datepicker" name="data_fine" id="js-date2" required>
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
    $query = "SELECT u.cognome, u.nome, r.data_start, r.data_end, 
              r.warning_turno, EXTRACT(EPOCH FROM (r.data_end - r.data_start)) / 3600 AS duration
              FROM {$dbTable} r 
              LEFT JOIN varie.v_dipendenti u ON r.matricola_cf = u.matricola
              WHERE r.data_start < now() AND r.data_end > now() 
              ORDER BY r.data_start;";

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
