<?php

/**
 * Funzione generica per recuperare i turni da una tabella specifica
 *
 * @param resource $conn Connessione al database
 * @param string $table Nome della tabella principale
 * @param string $joins Stringa opzionale con JOIN SQL personalizzate
 * @param string $conditions Condizioni WHERE aggiuntive
 * @param string $id (Opzionale) ID dell'evento per filtrare
 * @return resource|bool Risultato della query o false in caso di errore
 */
/**
 * Retrieve shifts from the database dynamically
 */
function getTurni($conn, $table, $joins = '', $conditions = '', $id = '') {
    $query = "SELECT 
                r.matricola_cf,
                COALESCE(u.cognome, d.cognome, '') AS cognome,
                COALESCE(u.nome, d.nome, '') AS nome,
                r.data_start, 
                r.data_end, 
                r.warning_turno, 
                r.modificato,
                (r.data_end - r.data_start > '10 hours') AS warning_time
              FROM {$table} r
              LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf = u.cf
              LEFT JOIN varie.dipendenti d ON r.matricola_cf = d.matricola
              {$joins}";

    $conditionsArray = ["r.data_start < NOW()", "r.data_end > NOW()"];
    if (!empty($id)) {
        $conditionsArray[] = "r.data_start < (SELECT COALESCE(data_ora_chiusura, NOW()) FROM eventi.t_eventi WHERE id = $1)";
        $conditionsArray[] = "r.data_end >= (SELECT data_ora_inizio_evento FROM eventi.t_eventi WHERE id = $1)";
    }

    $query .= " WHERE " . implode(" AND ", $conditionsArray);
    $query .= " ORDER BY r.data_start, cognome";

    $result = !empty($id) ? pg_query_params($conn, $query, [$id]) : pg_query($conn, $query);

    if (!$result) {
        error_log("Errore query turni: " . pg_last_error($conn));
        return false;
    }
    return $result;
}


/**
 * Funzione per visualizzare i turni
 *
 * @param resource $result Risultato della query
 * @param string $messaggio_vuoto Messaggio da mostrare se non ci sono risultati
 */
function visualizzaTurni($result, $messaggio_vuoto = "In questo momento non ci sono turni disponibili") {
    $check_reperibile = 0;

    while ($r = pg_fetch_assoc($result)) {
        $check_reperibile = 1;

        echo "- ";
        if (empty($r['cognome'])) {
            echo "TURNO VUOTO - Dalle ";
        } else {
            echo htmlspecialchars($r['cognome']) . " " . htmlspecialchars($r['nome']) . " - Dalle ";
        }

        echo date('H:i', strtotime($r['data_start'])) . " del " . date('d-m-Y', strtotime($r['data_start'])) . " alle ";
        echo date('H:i', strtotime($r['data_end'])) . " del " . date('d-m-Y', strtotime($r['data_end']));

        if ($r['warning_turno'] == 't') {
            echo ' - <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
        }
        if ($r['modificato'] == 't') {
            echo ' - <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
        }
        if ($r['warning_time'] == 't') {
            echo ' - <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
        }
        echo "<br>";
    }

    if ($check_reperibile == 0) {
        echo '- <i class="fas fa-circle" style="color: red;"></i> ' . htmlspecialchars($messaggio_vuoto) . '<br>';
    }
}


function getDipendenti($conn, $tipo = 'tutti', $where = '') {
    $query = "";

    if ($tipo === 'esterni') {
        // Query per utenti esterni
        $query = "SELECT cf as matricola, cognome, nome, livello1 
                  FROM users.v_utenti_esterni 
                  WHERE id1 = 9";
    } elseif ($tipo === 'interni') {
        // Query per dipendenti interni
        $query = "SELECT matricola, cognome, nome, settore || ' - ' || ufficio as livello1 
                  FROM varie.v_dipendenti";
    } elseif ($tipo === 'unione') {
        // Query combinata per esterni e interni con unione
        $query = "SELECT cf as matricola, cognome, nome, livello1 
                  FROM users.v_utenti_esterni 
                  WHERE id1 = 9
                  UNION 
                  SELECT matricola, cognome, nome, settore || ' - ' || ufficio as livello1 
                  FROM varie.v_dipendenti";
    } else {
        // Query base per tutti i dipendenti
        $query = "SELECT matricola, cognome, nome, livello1 FROM varie.v_dipendenti";
    }

    // Aggiunta di condizioni personalizzate
    if (!empty($where)) {
        $query .= " $where";
    }

    $query .= " ORDER BY cognome";

    // Esecuzione della query
    $result = pg_query($conn, $query);

    if (!$result) {
        error_log("Errore query dipendenti: " . pg_last_error($conn));
        return [];
    }

    return pg_fetch_all($result);
}


function getVolontari($conn, $condizione = '') {
    $query = "SELECT cf, cognome, nome, livello1 
              FROM users.v_utenti_esterni 
              WHERE (id1 = 1 OR id1 = 8)
              UNION 
              SELECT matricola as cf, cognome, nome, concat(settore, ' - ', ufficio) as livello1 
              FROM varie.v_dipendenti";
    
    // Condizione aggiuntiva opzionale
    if (!empty($condizione)) {
        $query .= " $condizione";
    }

    $query .= " ORDER BY cognome";

    $result = pg_query($conn, $query);

    if (!$result) {
        error_log("Errore query volontari: " . pg_last_error($conn));
        return [];
    }

    return pg_fetch_all($result);
}



function renderDateTimeFields($prefix, $labelDate, $labelTime, $required = true) {
    $requiredAttr = $required ? 'required' : '';
    ob_start();
    ?>
    <div class="form-group">
        <label for="<?= $prefix ?>_data"><?= htmlspecialchars($labelDate) ?></label>
        <input type="date" class="form-control" name="<?= $prefix ?>_data" id="<?= $prefix ?>_data" <?= $requiredAttr ?>>
    </div>

    <div class="form-group">
        <label for="<?= $prefix ?>_ora"><?= htmlspecialchars($labelTime) ?>:</label> <?php if ($required) echo '<font color="red">*</font>'; ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <select class="form-control" name="<?= $prefix ?>_hh" id="<?= $prefix ?>_hh" <?= $requiredAttr ?>>
                    <option value="">Ora</option>
                    <?php for ($j = 0; $j <= 24; $j++): ?>
                        <option value="<?= str_pad($j, 2, '0', STR_PAD_LEFT) ?>">
                            <?= str_pad($j, 2, '0', STR_PAD_LEFT) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <select class="form-control" name="<?= $prefix ?>_mm" id="<?= $prefix ?>_mm" <?= $requiredAttr ?>>
                    <option value="00">00</option>
                    <?php for ($j = 0; $j <= 59; $j += 15): ?>
                        <option value="<?= str_pad($j, 2, '0', STR_PAD_LEFT) ?>">
                            <?= str_pad($j, 2, '0', STR_PAD_LEFT) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function renderModal($id, $title, $formAction, $employees) {
    ?>
    <div id="<?= $id ?>" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?= $title ?></h4>
                </div>
                <div class="modal-body">
                    <form autocomplete="off" action="<?= $formAction ?>" method="POST">
                        <div class="form-group">
                            <label for="cf">Seleziona dipendente comunale:</label> <font color="red">*</font>
                            <select name="cf" id="cf" class="selectpicker show-tick form-control" data-live-search="true" required>
                                <option value="">Seleziona personale</option>
                                <option value="NO_TURNO"><font color="red">TURNO VUOTO</font></option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= htmlspecialchars($employee['matricola']) ?>">
                                        <?= htmlspecialchars($employee['cognome']) . ' ' . htmlspecialchars($employee['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php
                            echo renderDateTimeFields('inizio', 'Data inizio (AAAA-MM-GG)', 'Ora inizio');
                            echo renderDateTimeFields('fine', 'Data fine (AAAA-MM-GG)', 'Ora fine');
                        ?>
                        <button id="conferma" type="submit" class="btn btn-primary">Inserisci</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

?>
