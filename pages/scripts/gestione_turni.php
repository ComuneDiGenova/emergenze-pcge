<?php
function renderShiftSection($params, $conn, $profilo_sistema) {
    // Destructure Parameters
    $title = $params['title'];
    $modalId = $params['modal_id'];
    $formAction = $params['form_action'];
    $dbTable = $params['db_table'];
    $personnelQuery = $params['personnel_query'];
    $emptyMessage = $params['emptyMessage'] ?? "No records found."; // Default message

    // Title and Add Button
    echo '<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">';
    echo "<hr><h4>$title";

    if ($profilo_sistema <= 3) {
        echo <<<HTML
        <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#$modalId">
            <i class="fas fa-plus"></i> Aggiungi
        </button>
        </h4>
HTML;
    } else {
        echo "</h4>";
    }

    // Modal for Adding Personnel
    if ($profilo_sistema <= 3) {
        $result = pg_query($conn, $personnelQuery);
        $personnelList = pg_fetch_all($result);

        // Hour and Minute Select Options
        $hours = range(0, 24);
        $minutes = range(0, 59, 15);

        echo <<<HTML
        <div id="$modalId" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Inserire $title</h4>
                    </div>
                    <div class="modal-body">
                        <form action="$formAction" method="POST">
                            <div class="form-group">
                                <label for="cf">Seleziona personale:</label>
                                <select name="cf" class="form-control" required>
                                    <option value="">Seleziona personale</option>
                                    <option value="NO_TURNO">TURNO VUOTO</option>
HTML;
        foreach ($personnelList as $person) {
            echo '<option value="'.$person['matricola'].'">'.$person['cognome'].' '.$person['nome'].'</option>';
        }

        // Data Inizio
        echo <<<HTML
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Data inizio (AAAA-MM-GG):</label>
                                <input type="date" class="form-control" name="data_inizio" required>
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
            echo "<option value='$formattedHour'>$formattedHour</option>";
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
            echo "<option value='$formattedMinute'>$formattedMinute</option>";
        }

        // Data Fine
        echo <<<HTML
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Data fine (AAAA-MM-GG):</label>
                                <input type="date" class="form-control" name="data_fine" required>
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
            echo "<option value='$formattedHour'>$formattedHour</option>";
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
            echo "<option value='$formattedMinute'>$formattedMinute</option>";
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

    // Display Current and Future Shifts
    $query = "SELECT u.cognome, u.nome, r.data_start, r.data_end, r.warning_turno 
              FROM $dbTable r 
              LEFT JOIN varie.v_dipendenti u ON r.matricola_cf = u.matricola
              WHERE r.data_start < now() AND r.data_end > now() 
              ORDER BY r.data_start;";
    
    $result = pg_query($conn, $query);
    $shifts = pg_fetch_all($result);

    echo "<div>";
    if ($shifts) {
        foreach ($shifts as $row) {
            echo "- {$row['cognome']} {$row['nome']} dalle ";
            echo date('H:i', strtotime($row['data_start']))." alle ".date('H:i', strtotime($row['data_end']));
            if ($row['warning_turno'] == 't') {
                echo ' <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione"></i>';
            }
            echo "<br>";
        }
    } else {
        // Display empty message if no shifts are found
        echo "- <i class='fas fa-circle' style='color: red;'></i> $emptyMessage<br>";
    }
    echo "</div></div>";
}
?>
