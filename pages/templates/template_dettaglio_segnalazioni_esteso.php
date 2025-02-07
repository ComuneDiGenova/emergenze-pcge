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
    <br>
    
    <?php if ($r['conteggio_incarichi'] > 0): ?>
        <h4><?= $r['conteggio_incarichi'] ?> incarico/i assegnato/i</h4>
    <?php else: ?>
        <p>Nessun incarico assegnato</p>
    <?php endif; ?>

    <?php if ($r['conteggio_sopralluoghi'] > 0): ?>
        <h4><?= $r['conteggio_sopralluoghi'] ?> sopralluogo/i assegnato/i</h4>
    <?php else: ?>
        <p>Nessun sopralluogo assegnato</p>
    <?php endif; ?>

    <hr>
</div>
