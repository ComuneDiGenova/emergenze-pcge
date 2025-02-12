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
        <h4><?= $r['conteggio_incarichi'] ?> incarico/i assegnato/i</h4>
        <div class="incarichi">
            <b>Dettaglio incarichi:</b>
            <?php
                $query_incarichi = "SELECT data_ora_invio, descrizione, descrizione_uo, descrizione_stato
                                    FROM segnalazioni.\"$v_incarichi_last_update\" 
                                    WHERE id_lavorazione = $1 
                                    GROUP BY data_ora_invio, descrizione, descrizione_uo, descrizione_stato 
                                    ORDER BY data_ora_invio ASC;";
                
                $result_incarichi = pg_query_params($conn, $query_incarichi, [$r['id_lavorazione']]);
                
                if ($result_incarichi) {
                    while ($r_i = pg_fetch_assoc($result_incarichi)) {
                        echo '<br>' . htmlspecialchars($r_i['data_ora_invio']);
                        echo ' - ' . htmlspecialchars($r_i['descrizione_stato']) . ' - ';
                        echo htmlspecialchars($r_i['descrizione_uo']) . ' (' . htmlspecialchars($r_i['descrizione']) . ')';
                    }
                } else {
                    echo "<p>Errore nel recupero degli incarichi.</p>";
                }
            ?>
        </div>
    <?php else: ?>
        <b>Nessun incarico assegnato - </b>
    <?php endif; ?>

    <!-- Sezione Incarichi Interni -->
    <?php if ($r['conteggio_incarichi_interni'] > 0): ?>
        <br>--<br><b>Incarichi interni:</b>
        <div class="incarichi-interni">
            <?php
                $query_incarichi_interni = "SELECT data_ora_invio, descrizione, descrizione_uo, descrizione_stato
                                            FROM segnalazioni.\"$v_incarichi_interni_last_update\" 
                                            WHERE id_lavorazione = $1 
                                            GROUP BY data_ora_invio, descrizione, descrizione_uo, descrizione_stato  
                                            ORDER BY data_ora_invio ASC;";
                
                $result_incarichi_interni = pg_query_params($conn, $query_incarichi_interni, [$r['id_lavorazione']]);
                
                if ($result_incarichi_interni) {
                    while ($r_i = pg_fetch_assoc($result_incarichi_interni)) {
                        echo '<br>' . htmlspecialchars($r_i['data_ora_invio']);
                        echo ' - ' . htmlspecialchars($r_i['descrizione_stato']) . ' - ';
                        echo htmlspecialchars($r_i['descrizione_uo']) . ' (' . htmlspecialchars($r_i['descrizione']) . ')';
                    }
                } else {
                    echo "<p>Errore nel recupero degli incarichi interni.</p>";
                }
            ?>
        </div>
    <?php else: ?>
        <b>Nessun incarico interno assegnato - </b>
    <?php endif; ?>

    <!-- Sezione Sopralluoghi -->
    <?php if ($r['conteggio_sopralluoghi'] > 0): ?>
        <b><?= htmlspecialchars($r['conteggio_sopralluoghi']) ?> presidi assegnati - </b>
    <?php else: ?>
        <b>Nessun presidio assegnato - </b>
    <?php endif; ?>

    <!-- Sezione Provvedimenti Cautelari -->
    <?php if ($r['conteggio_pc'] > 0): ?>
        <b><?= htmlspecialchars($r['conteggio_pc']) ?> provvedimenti cautelari assegnati</b>
    <?php else: ?>
        <b>Nessun provvedimento cautelare assegnato</b>
    <?php endif; ?>
    
    <hr>
</div>
