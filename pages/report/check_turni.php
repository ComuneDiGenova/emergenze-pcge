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

        echo "Query: $query<br>";
        echo "Parametri: CF = $cf, Start = $data_inizio, End = $data_fine<br>";

        $result = pg_query_params($conn, $query, [$cf, $data_inizio, $data_fine]);

        if (pg_num_rows($result) > 0) {
            echo "Turno sovrapposto rilevato nella tabella $table<br>";
            $check_turni = true;

            // Aggiornamento flag warning_turno
            $updateQuery = "UPDATE report.$table
                            SET warning_turno = 't'
                            WHERE matricola_cf = $1
                            AND (
                                (data_start < $3 AND data_start > $2) OR
                                (data_end < $3 AND data_end > $2) OR
                                (data_start <= $2 AND data_end >= $3)
                            );";

            pg_query_params($conn, $updateQuery, [$cf, $data_inizio, $data_fine]);
        }
    }

    // return $check_turni;
}
?>
