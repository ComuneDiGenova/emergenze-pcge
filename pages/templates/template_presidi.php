
<!-- REPORT PRESIDI MOBILI-->
<div class="row">              
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h3>Elenco presidi mobili</h3>

        <?php
        // Definizione della query
        $query_presidi = "SELECT i.id,
                to_char(i.data_ora_invio, 'DD/MM/YYYY'::text) AS data_invio,
                to_char(i.data_ora_invio, 'HH24:MI'::text) AS ora_invio,
                i.descrizione,
                to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
                to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
                to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
                i.note_ente,
                st.id_stato_sopralluogo,
                t.descrizione AS descrizione_stato
            FROM segnalazioni.t_sopralluoghi_mobili i
            JOIN segnalazioni.stato_sopralluoghi_mobili st 
                ON st.id_sopralluogo = i.id
            JOIN segnalazioni.tipo_stato_sopralluoghi t 
                ON t.id = st.id_stato_sopralluogo
            JOIN segnalazioni.join_sopralluoghi_mobili_squadra ii 
                ON ii.id_sopralluogo = i.id
            JOIN users.v_squadre_all u 
                ON u.id::text = ii.id_squadra::text
            JOIN eventi.t_eventi e 
                ON e.id = i.id_evento
            WHERE i.id_evento = $1
            AND st.data_ora_stato = (
                SELECT max(data_ora_stato) 
                FROM segnalazioni.stato_sopralluoghi_mobili 
                WHERE id_sopralluogo = i.id
            ) 
            GROUP BY i.id, data_invio, ora_invio, i.descrizione, time_preview, time_start, time_stop, note_ente, descrizione_stato, id_stato_sopralluogo";

        $parameters = [$id];
        $result_presidi = pg_query_params($conn, $query_presidi, $parameters);
        ?>

        <ul>
            <?php while ($r_p = pg_fetch_assoc($result_presidi)): ?>
                <?php
                // Determinare l'icona, titolo e colore in base allo stato
                $icon = '';
                $title = '';
                $color = '';

                switch ($r_p['id_stato_sopralluogo']) {
                    case 1:
                        $icon = 'fas fa-exclamation';
                        $title = 'Presidio inviato, ma non ancora preso in carico';
                        $color = '#ff0000';
                        break;
                    case 2:
                        $icon = 'fas fa-play';
                        $title = 'Presidio in lavorazione';
                        $color = '#f2d921';
                        break;
                    case 3:
                        $icon = 'fas fa-check';
                        $title = 'Presidio chiuso';
                        $color = '#5cb85c';
                        break;
                    case 4:
                        $icon = 'fas fa-exclamation';
                        $title = 'Presidio rifiutato';
                        $color = '#ff0000';
                        break;
                }

                // Determinare la query per lo storico delle squadre
                if ($r_p['id_stato_sopralluogo'] == 4) {
                    $query_s = "SELECT a.id, a.data_ora_invio as data_ora, a.data_ora_invio as data_ora_cambio, 
                            max(s.data_ora_stato) as time_stop, a.id_squadra::integer, b.nome
                        FROM segnalazioni.t_sopralluoghi_mobili a
                        JOIN users.t_squadre b ON a.id_squadra::integer = b.id::integer  
                        JOIN segnalazioni.stato_sopralluoghi_mobili s ON s.id_sopralluogo = a.id
                        WHERE a.id = {$r_p['id']}
                        GROUP BY a.id, a.data_ora_invio, a.id_squadra, b.nome";
                } else {
                    $query_s = "SELECT a.id_sopralluogo, a.data_ora, a.data_ora_cambio, c.time_stop, a.id_squadra, b.nome 
                        FROM segnalazioni.join_sopralluoghi_mobili_squadra a
                        JOIN users.t_squadre b ON a.id_squadra = b.id 
                        JOIN segnalazioni.t_sopralluoghi_mobili c ON c.id = a.id_sopralluogo
                        WHERE id_sopralluogo = {$r_p['id']} 
                        ORDER BY data_ora";
                }
                ?>
                <li>
                    <h4>
                        <i class="<?= $icon ?>" title="<?= $title ?>" style="color:<?= $color ?>"></i>
                        Presidio <?= htmlspecialchars($r_p['descrizione_stato']) ?> assegnato il <?= htmlspecialchars($r_p['data_invio']) ?>
                        alle <?= htmlspecialchars($r_p['ora_invio']) ?> - Descrizione Presidio: <?= htmlspecialchars($r_p['descrizione']) ?>
                        <?php if (!empty($r_p['note_ente'])): ?>
                            - Note chiusura: <?= htmlspecialchars($r_p['note_ente']) ?>
                        <?php endif; ?>
                    </h4>
                    
                    <ul>
                        <?php require('./templates/query_storico_squadre_incarichi.php'); ?>
                    </ul>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>  
</div>

<hr>

<!-- REPORT PRESIDI FISSI SLEGATI DA SEGNALAZIONI-->
<div class="row">              
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <h3>Elenco presidi fissi slegati da segnalazioni</h3>

    <?php
        $query_presidi_fissi = "SELECT i.id,
                to_char(i.data_ora_invio, 'DD/MM/YYYY'::text) AS data_invio,
                to_char(i.data_ora_invio, 'HH24:MI'::text) AS ora_invio,
                i.descrizione,
                to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
                to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
                to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
                i.note_ente,
                st.id_stato_sopralluogo,
                jj.id_segnalazione_in_lavorazione,
                t.descrizione AS descrizione_stato
            FROM segnalazioni.t_sopralluoghi i
            JOIN segnalazioni.stato_sopralluoghi st 
                ON st.id_sopralluogo = i.id
            JOIN segnalazioni.tipo_stato_sopralluoghi t 
                ON t.id = st.id_stato_sopralluogo
            JOIN segnalazioni.join_sopralluoghi_squadra 
                ii ON ii.id_sopralluogo = i.id
            JOIN users.v_squadre_all u 
                ON u.id::text = ii.id_squadra::text
            JOIN eventi.t_eventi e 
                ON e.id = i.id_evento
            LEFT JOIN segnalazioni.join_segnalazioni_sopralluoghi jj 
                ON jj.id_sopralluogo=i.id
            WHERE i.id_evento = $1 AND id_segnalazione_in_lavorazione IS NULL
                AND st.data_ora_stato = (
                    SELECT max(data_ora_stato) 
                    FROM segnalazioni.stato_sopralluoghi 
                    WHERE id_sopralluogo =i.id
                ) 
            GROUP BY i.id, data_invio, ora_invio, i.descrizione, time_preview, time_start, time_stop, 
                    note_ente, descrizione_stato, id_stato_sopralluogo, id_segnalazione_in_lavorazione;";

        $parameters = [$id];
        $result_presidi_fissi = pg_query_params($conn, $query_presidi_fissi, $parameters);
    ?>
    
    <ul>
    <?php
        while ($r_pf = pg_fetch_assoc($result_presidi_fissi)):
            // Determinare l'icona e il colore in base allo stato del sopralluogo
            $icon = '';
            $title = '';
            $color = '';

            switch ($r_pf['id_stato_sopralluogo']) {
                case 1:
                    $icon = 'fas fa-exclamation';
                    $title = 'Presidio inviato, ma non ancora preso in carico';
                    $color = '#ff0000';
                    break;
                case 2:
                    $icon = 'fas fa-play';
                    $title = 'Presidio in lavorazione';
                    $color = '#f2d921';
                    break;
                case 3:
                    $icon = 'fas fa-check';
                    $title = 'Presidio chiuso';
                    $color = '#5cb85c';
                    break;
                case 4:
                    $icon = 'fas fa-exclamation';
                    $title = 'Presidio rifiutato';
                    $color = '#ff0000';
                    break;
            }

            // Costruire la query in base allo stato del sopralluogo
            if ($r_pf['id_stato_sopralluogo'] == 4) {
                $query_s = "SELECT a.id, a.data_ora_invio as data_ora, a.data_ora_invio as data_ora_cambio, 
                        max(s.data_ora_stato) as time_stop, a.id_squadra::integer, b.nome
                    FROM segnalazioni.t_sopralluoghi a
                    JOIN users.t_squadre b 
                        ON a.id_squadra::integer = b.id::integer  
                    JOIN segnalazioni.stato_sopralluoghi s 
                        ON s.id_sopralluogo = a.id
                    WHERE a.id = {$r_pf['id']}
                    GROUP BY a.id, a.data_ora_invio, a.id_squadra, b.nome";
            } else {
                $query_s = "SELECT a.id_sopralluogo, a.data_ora, a.data_ora_cambio, c.time_stop, a.id_squadra, b.nome 
                    FROM segnalazioni.join_sopralluoghi_squadra a
                    JOIN users.t_squadre b 
                        ON a.id_squadra = b.id 
                    JOIN segnalazioni.t_sopralluoghi c 
                        ON c.id = a.id_sopralluogo
                    WHERE id_sopralluogo = {$r_pf['id']} 
                    ORDER BY data_ora";
            }
    ?>
        <li>
            <h4>
                <i class="<?= $icon ?>" title="<?= $title ?>" style="color:<?= $color ?>"></i>
                Presidio <?= htmlspecialchars($r_pf['descrizione_stato']) ?> assegnato il <?= htmlspecialchars($r_pf['data_invio']) ?> 
                alle <?= htmlspecialchars($r_pf['ora_invio']) ?> - Descrizione Presidio: <?= htmlspecialchars($r_pf['descrizione']) ?>
                <?php if (!empty($r_pf['note_ente'])): ?>
                    - Note chiusura: <?= htmlspecialchars($r_pf['note_ente']) ?>
                <?php endif; ?>
            </h4>
            
            <ul>
                <?php 
                    $result_s = pg_query($conn, $query_s);
                    require('./templates/query_storico_squadre_incarichi.php'); 
                ?>
            </ul>
        </li>
    <?php endwhile; ?>
    </ul>

    <hr>
</div>

