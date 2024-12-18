<?php
function checkTurniSovrapposti($conn, $cf, $data_inizio, $data_fine) {
    $tabelle_turni = [
        't_coordinamento',
        't_monitoraggio_meteo',
        't_operatore_anpas',
        't_operatore_nverde',
        't_operatore_volontari',
        't_presidio_territoriale',
        't_tecnico_pc'
    ];

    $check_turni = false;

    foreach ($tabelle_turni as $table) {
        $query = "SELECT matricola_cf
                  FROM report.$table
                  WHERE matricola_cf = $1
                  AND (
                      (data_start < $3 AND data_start > $2) OR
                      (data_end < $3 AND data_end > $2) OR
                      (data_start <= $2 AND data_end >= $3)
                  );";

        echo "<b>Debug Query:</b> $query<br>";
        $result = pg_query_params($conn, $query, [$cf, $data_inizio, $data_fine]);

        if (!$result) {
            echo "Errore query: " . pg_last_error($conn) . "<br>";
            continue;
        }

        if (pg_num_rows($result) > 0) {
            echo "<b>Turno sovrapposto trovato nella tabella:</b> $table<br>";

            $check_turni = true;

            // Aggiorna warning_turno
            $updateQuery = "UPDATE report.$table
                            SET warning_turno = 't'
                            WHERE matricola_cf = $1
                            AND (
                                (data_start < $3 AND data_start > $2) OR
                                (data_end < $3 AND data_end > $2) OR
                                (data_start <= $2 AND data_end >= $3)
                            );";

            $updateResult = pg_query_params($conn, $updateQuery, [$cf, $data_inizio, $data_fine]);

            if (!$updateResult) {
                echo "Errore aggiornamento: " . pg_last_error($conn) . "<br>";
            } else {
                echo "<b>Warning_turno aggiornato correttamente nella tabella:</b> $table<br>";
            }
        } else {
            echo "<b>Nessuna sovrapposizione trovata nella tabella:</b> $table<br>";
        }
    }

    return $check_turni;
}

?>
