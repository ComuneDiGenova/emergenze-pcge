<?php
/**
 * Template per la visualizzazione di una singola segnalazione.
 * La variabile $r è un array associativo contenente i dettagli della segnalazione.
 */
?>

<div class="segnalazione">
    <b>Id segnalazioni:</b> <?= htmlspecialchars($r['id_segn']) ?> - 
    <?php if ($r['num'] > 1): ?>
        <b>Num. segnalazioni collegate:</b> <?= htmlspecialchars($r['num']) ?> - 
    <?php endif; ?>

    <b>Stato:</b> 
    <?php if ($r['in_lavorazione'] == 't'): ?>
        <i class="fas fa-play" style="color:#5cb85c"></i> in lavorazione
    <?php elseif ($r['in_lavorazione'] == 'f'): ?>
        <i class="fas fa-stop"></i> chiusa
    <?php else: ?>
        <i class="fas fa-exclamation" style="color:#ff0000"></i> da prendere in carico
    <?php endif; ?>
    <br>

    <?php if ($r['num'] > 1): ?>
        <b>Data e ora prima segnalazione:</b> <?= htmlspecialchars($r['data_ora']) ?><br>
    <?php else: ?>
        <b>Data e ora segnalazione:</b> <?= htmlspecialchars($r['data_ora']) ?><br>
    <?php endif; ?>

    <b>Tipo criticità:</b> <?= htmlspecialchars($r['criticita']) ?><br>
    <b>Descrizione:</b> <?= htmlspecialchars($r['descrizione']) ?><br>
    <b>Municipio:</b> <?= htmlspecialchars($r['nome_munic']) ?><br>
    <b>Indirizzo:</b> <?= htmlspecialchars($r['localizzazione']) ?><br>

    <?php if (!empty($r['descrizione_chiusura'])): ?>
        <b>Note chiusura:</b> <?= htmlspecialchars($r['descrizione_chiusura']) ?><br>
    <?php endif; ?>
    
    <?php if (empty($r['descrizione_chiusura'])): ?>
        <?php if ($r['incarichi'] == 't'): ?>
            <i class="fas fa-circle" title="incarichi in corso" style="color:#f2d921"></i> Lavorazione in corso
        <?php else: ?>
            <i class="fas fa-circle" title="nessun incarico in corso" style="color:#ff0000"></i> Nessuna lavorazione in corso
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Sezione Incarichi -->
    <?php if ($r['conteggio_incarichi'] > 0): ?>
        <h4><?= htmlspecialchars($r['conteggio_incarichi']) ?> incarico/i assegnato/i</h4>
        <ul>
            <?php
                $query_incarichi = "SELECT i.id,
                    to_char(i.data_ora_invio, 'DD/MM/YYYY') AS data_invio,
                    to_char(i.data_ora_invio, 'HH24:MI') AS ora_invio,
                    i.descrizione,
                    u.descrizione AS descrizione_uo,
                    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS') AS time_preview,
                    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS') AS time_start,
                    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS') AS time_stop,
                    i.note_ente,
                    i.note_rifiuto,
                    st.id_stato_incarico,
                    t.descrizione AS descrizione_stato,
                    st.parziale
                FROM segnalazioni.t_incarichi i
                JOIN segnalazioni.join_segnalazioni_incarichi j 
                    ON j.id_incarico = i.id
                JOIN segnalazioni.stato_incarichi st 
                    ON st.id_incarico = i.id
                JOIN segnalazioni.tipo_stato_incarichi t 
                    ON t.id = st.id_stato_incarico
                JOIN varie.v_incarichi_mail u 
                    ON u.cod = i.id_uo::text
                WHERE j.id_segnalazione_in_lavorazione = $1
                    AND st.data_ora_stato = (
                                            SELECT max(data_ora_stato) 
                                            FROM segnalazioni.stato_incarichi 
                                            WHERE id_incarico = i.id
                    )
                ORDER BY data_invio, ora_invio;";

                $result_incarichi = pg_query_params($conn, $query_incarichi, [$r['id_lavorazione']]);

                if ($result_incarichi) {
                    while ($r_i = pg_fetch_assoc($result_incarichi)) {
                        echo "<li>";
                        if ($r_i['id_stato_incarico'] == 1) {
                            echo '<i class="fas fa-exclamation" title="Incarico inviato, ma non ancora preso in carico" style="color:#f2d921"></i>';
                        } elseif ($r_i['id_stato_incarico'] == 2) {
                            echo '<i class="fas fa-play" title="Incarico in lavorazione" style="color:#f2d921"></i>';
                        } elseif ($r_i['id_stato_incarico'] == 3) {
                            echo '<i class="fas fa-check" title="Incarico chiuso" style="color:#5cb85c"></i>';
                        } elseif ($r_i['id_stato_incarico'] == 4) {
                            echo '<i class="fas fa-exclamation" title="Incarico rifiutato" style="color:#ff0000"></i>';
                        }

                        echo " Incarico " . htmlspecialchars($r_i['descrizione_stato']) . " assegnato il " . htmlspecialchars($r_i['data_invio']) . " alle " . htmlspecialchars($r_i['ora_invio']) . " ";
                        echo "a " . htmlspecialchars($r_i['descrizione_uo']) . "<br>";
                        echo "<b>Descrizione incarico:</b> " . htmlspecialchars($r_i['descrizione']) . "<br>";

                        if (!empty($r_i['note_ente'])) {
                            echo '<b>Note chiusura:</b> ' . htmlspecialchars($r_i['note_ente']) . "<br>";
                        }
                        if (!empty($r_i['note_rifiuto'])) {
                            echo '<b>Note rifiuto:</b> ' . htmlspecialchars($r_i['note_rifiuto']) . "<br>";
                        }
                        if ($r_i['parziale'] == 't') {
                            echo '<i class="fas fa-exclamation"></i> Incarico eseguito solo in parte<br>';
                        }
                        echo "</li>";
                    }
                } else {
                    echo "<li>Errore nel recupero degli incarichi.</li>";
                }
            ?>
        </ul>
    <?php else: ?>
        <b>Nessun incarico assegnato - </b>
    <?php endif; ?>

    <!-- Sezione Incarichi Interni -->
    <?php if ($r['conteggio_incarichi_interni'] > 0): ?>
        <h4><?= htmlspecialchars($r['conteggio_incarichi_interni']) ?> incarico/i interno/i assegnato/i</h4>
        <ul>
            <?php
                $query_incarichi_interni = "SELECT i.id,
                    to_char(i.data_ora_invio, 'DD/MM/YYYY') AS data_invio,
                    to_char(i.data_ora_invio, 'HH24:MI') AS ora_invio,
                    i.descrizione,
                    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS') AS time_preview,
                    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS') AS time_start,
                    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS') AS time_stop,
                    i.note_ente,
                    i.note_rifiuto,
                    st.id_stato_incarico,
                    t.descrizione AS descrizione_stato,
                    st.parziale
                FROM segnalazioni.t_incarichi_interni i
                JOIN segnalazioni.join_segnalazioni_incarichi_interni j 
                    ON j.id_incarico = i.id
                JOIN segnalazioni.stato_incarichi_interni st 
                    ON st.id_incarico = i.id
                JOIN segnalazioni.tipo_stato_incarichi t 
                    ON t.id = st.id_stato_incarico
                JOIN users.v_squadre_all u 
                    ON u.id::text = i.id_squadra::text
                JOIN eventi.t_eventi e 
                    ON e.id = $1
                WHERE j.id_segnalazione_in_lavorazione = $2
                AND st.data_ora_stato = (
                                        SELECT max(data_ora_stato) 
                                        FROM segnalazioni.stato_incarichi_interni 
                                        WHERE id_incarico = i.id
                )
                GROUP BY i.id, data_invio, ora_invio, i.descrizione, time_preview, time_start, time_stop, 
                        note_ente, note_rifiuto, descrizione_stato,parziale, id_stato_incarico;";

                echo "<ul>";
                
                $parameters = [$id, $r['id_lavorazione']];
                $result_incarichi_interni = pg_query_params($conn, $query_incarichi_interni, $parameters);

                if ($result_incarichi_interni) {
                    while ($r_ii = pg_fetch_assoc($result_incarichi_interni)) {


                        echo "<li>";
                        if ($r_ii['id_stato_incarico'] == 1) {
                            echo '<i class="fas fa-exclamation" title="Incarico interno inviato, ma non ancora preso in carico" style="color:#ff0000"></i>';
                        } elseif ($r_ii['id_stato_incarico'] == 2) {
                            echo '<i class="fas fa-play" title="Incarico interno in lavorazione" style="color:#f2d921"></i>';
                        } elseif ($r_ii['id_stato_incarico'] == 3) {
                            echo '<i class="fas fa-check" title="Incarico interno chiuso" style="color:#5cb85c"></i>';
                        } elseif ($r_ii['id_stato_incarico'] == 4) {
                            echo '<i class="fas fa-exclamation" title="Incarico interno rifiutato" style="color:#ff0000"></i>';
                        }

                        if ($r_ii['id_stato_incarico'] == 4) {
                            $query_s="SELECT a.id, a.data_ora_invio as data_ora, a.data_ora_invio as data_ora_cambio, max(s.data_ora_stato) as time_stop, a.id_squadra::integer, b.nome
                                FROM segnalazioni.t_incarichi_interni a
                                JOIN users.t_squadre b ON a.id_squadra::integer = b.id::integer  
                                JOIN segnalazioni.stato_incarichi_interni s ON s.id_incarico=a.id
                                WHERE a.id=".$r_ii['id']."
                                GROUP BY a.id, a.data_ora_invio, a.id_squadra, b.nome";
                        } else {
                            $query_s="SELECT a.id_incarico, a.data_ora, a.data_ora_cambio, c.time_stop,a.id_squadra, b.nome 
                                FROM segnalazioni.join_incarichi_interni_squadra a
                                JOIN users.t_squadre b ON a.id_squadra=b.id 
                                JOIN segnalazioni.t_incarichi_interni c ON c.id=a.id_incarico
                                WHERE id_incarico =".$r_ii['id']." 
                                ORDER BY data_ora";
                        }

                        $result_s = pg_query($conn, $query_s);


                        echo " Incarico interno " . htmlspecialchars($r_ii['descrizione_stato']) . " assegnato il " . htmlspecialchars($r_ii['data_invio']) . " alle " . htmlspecialchars($r_ii['ora_invio']) . " ";
                        echo "<br><b>Descrizione incarico:</b> " . htmlspecialchars($r_ii['descrizione']) . "<br>";

                        if (!empty($r_ii['note_ente'])) {
                            echo '<b>Note chiusura:</b> ' . htmlspecialchars($r_ii['note_ente']) . "<br>";
                        }
                        if (!empty($r_ii['note_rifiuto'])) {
                            echo '<b>Note rifiuto:</b> ' . htmlspecialchars($r_ii['note_rifiuto']) . "<br>";
                        }
                        if ($r_ii['parziale'] == 't') {
                            echo '<i class="fas fa-exclamation"></i> Incarico eseguito solo in parte<br>';
                        }

                        echo "<ul>";
                            require('./templates/query_storico_squadre_incarichi.php');
                        echo "</ul>";

                        echo "</li>";
                    }
                } else {
                    echo "<li>Errore nel recupero degli incarichi interni.</li>";
                }
            ?>
        </ul>
    <?php else: ?>
        <b>Nessun incarico interno assegnato - </b>
    <?php endif; ?>

    <!-- Sezione Sopralluoghi -->
    <?php if ($r['conteggio_sopralluoghi'] > 0): ?>
        <h4><?= htmlspecialchars($r['conteggio_sopralluoghi']) ?> presidi assegnati</h4>
        <ul>
            <?php
                $query_sopralluoghi = "SELECT i.id,
                        to_char(i.data_ora_invio, 'DD/MM/YYYY'::text) AS data_invio,
                        to_char(i.data_ora_invio, 'HH24:MI'::text) AS ora_invio,
                        i.descrizione,
                        to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
                        to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
                        to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
                        i.note_ente,
                        st.id_stato_sopralluogo,
                        t.descrizione AS descrizione_stato
                    FROM segnalazioni.t_sopralluoghi i
                        JOIN segnalazioni.join_segnalazioni_sopralluoghi j 
                            ON j.id_sopralluogo = i.id
                        JOIN segnalazioni.stato_sopralluoghi st 
                            ON st.id_sopralluogo = i.id
                        JOIN segnalazioni.tipo_stato_sopralluoghi t 
                            ON t.id = st.id_stato_sopralluogo
                        JOIN segnalazioni.join_sopralluoghi_squadra ii 
                            ON ii.id_sopralluogo = i.id
                        JOIN users.v_squadre_all u 
                            ON u.id::text = ii.id_squadra::text
                        JOIN eventi.t_eventi e 
                            ON e.id = $1
                        WHERE j.id_segnalazione_in_lavorazione = $2
                        AND st.data_ora_stato = (select max(data_ora_stato) from segnalazioni.stato_sopralluoghi where id_sopralluogo =i.id) 
                        GROUP BY i.id, data_invio, ora_invio, i.descrizione, time_preview, time_start, time_stop, 
                                note_ente, descrizione_stato, id_stato_sopralluogo;";

                echo "<ul>";

                $parameters = [$id, $r['id_lavorazione']];
                $result_sopralluoghi = pg_query_params($conn, $query_sopralluoghi, $parameters);

                if ($result_sopralluoghi) {
                    while ($r_sopr = pg_fetch_assoc($result_sopralluoghi)) {

                        if($r_sopr['id_stato_sopralluogo']==1){
                            echo '<i class="fas fa-exclamation" title="Presidio inviato, ma non ancora preso in carico" style="color:#ff0000"></i>';
                        } else if ($r_sopr['id_stato_sopralluogo']==2){
                            echo '<i class="fas fa-play" title="Presidio in lavorazione" style="color:#f2d921"></i>';
                        } else if($r_sopr['id_stato_sopralluogo']==3){
                            echo '<i class="fas fa-check" title="Presidio chiuso" style="color:#5cb85c"></i>';
                        }else if($r_sopr['id_stato_sopralluogo']==4){
                            echo '<i class="fas fa-exclamation" title="Presidio rifiutato" style="color:#ff0000"></i>';
                        }
                        echo " Presidio ".$r_sopr['descrizione_stato']." assegnato il " .$r_sopr['data_invio']. " alle " .$r_sopr['ora_invio']. " ";
                        echo " - Descrizione Presidio: " .$r_sopr['descrizione']." ";
                        if ($r_sopr['note_ente']!=''){
                            echo ' - Note chiusura: '.$r_sopr['note_ente'].' ';
                        }


                        if($r_sopr['id_stato_sopralluogo']==4){
                            $query_s="SELECT a.id, a.data_ora_invio as data_ora, a.data_ora_invio as data_ora_cambio, max(s.data_ora_stato) as time_stop, a.id_squadra::integer, b.nome
                            FROM segnalazioni.t_sopralluoghi_mobili a
                            JOIN users.t_squadre b ON a.id_squadra::integer = b.id::integer  
                            JOIN segnalazioni.stato_sopralluoghi_mobili s ON s.id_sopralluogo=a.id
                            WHERE a.id=".$r_sopr['id']."
                            GROUP BY a.id, a.data_ora_invio, a.id_squadra, b.nome";
                        } else { 
                            $query_s="SELECT a.id_sopralluogo, a.data_ora, a.data_ora_cambio, c.time_stop,a.id_squadra, b.nome 
                            FROM segnalazioni.join_sopralluoghi_mobili_squadra a
                            JOIN users.t_squadre b ON a.id_squadra=b.id 
                            JOIN segnalazioni.t_sopralluoghi_mobili c ON c.id=a.id_sopralluogo
                            WHERE id_sopralluogo =".$r_sopr['id']." 
                            ORDER BY data_ora";
                        }

                        $result_s = pg_query($conn, $query_s);

                        echo "<ul>";
                        require('./templates/query_storico_squadre_incarichi.php');
                        echo "</ul>";

                        echo "</li>";
                    }
                } else {
                    echo "<li>Errore nel recupero dei sopralluoghi.</li>";
                }
            ?>
        </ul>
    <?php else: ?>
        <b>Nessun presidio assegnato - </b>
    <?php endif; ?>


    <!-- Sezione Provvedimenti Cautelari -->
    <?php if ($r['conteggio_pc'] > 0): ?>
        <h4><?= htmlspecialchars($r['conteggio_pc']) ?> provvedimenti cautelari assegnati</h4>
    <?php else: ?>
        <b>Nessun provvedimento cautelare assegnato</b>
    <?php endif; ?>
    
    <hr>
</div>
