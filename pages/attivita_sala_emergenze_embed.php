<?php
    require_once './scripts/gestione_turni.php';
?>

<div class="container">
    <!-- Titolo della pagina -->
    <div class="row">
        <div class="col-12">
            <h3>Attività sala emergenze</h3>
        </div>
    </div>

    <?php
    // Combined configuration for all roles (grouped in rows of two)
    $roles = [
        [
            'title' => 'Coordinatore di Sala',
            'modalId' => 'new_coord',
            'action' => 'report/nuovo_coord.php',
            'data' => getDipendenti($conn, 'interni'),
            'table' => 'report.t_coordinamento',
            'joins' => "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf = u.matricola",
            'emptyMessage' => 'In questo momento non ci sono coordinatori',
        ],
        [
            'title' => 'Operatore Monitoraggio Meteo',
            'modalId' => 'new_mm',
            'action' => 'report/nuovo_mm.php',
            'data' => getDipendenti($conn, 'unione'),
            'table' => 'report.t_monitoraggio_meteo',
            'joins' => "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf = u.cf",
            'emptyMessage' => 'In questo momento non ci sono responsabili Monitoraggio Meteo',
        ],
        [
            'title' => 'Operatore Presidi Territoriali',
            'modalId' => 'new_pt',
            'action' => 'report/nuovo_pt.php',
            'data' => getDipendenti($conn),
            'table' => 'report.t_presidio_territoriale',
            'joins' => "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf = u.matricola",
            'emptyMessage' => 'In questo momento non ci sono responsabili Presidi Territoriali',
        ],
        [
            'title' => 'Tecnico Protezione Civile',
            'modalId' => 'new_tPC',
            'action' => 'report/nuovo_tPC.php',
            'data' => getDipendenti($conn),
            'table' => 'report.t_tecnico_pc',
            'joins' => "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf = u.matricola",
            'emptyMessage' => 'In questo momento non ci sono tecnici di Protezione Civile',
        ],
        [
            'title' => 'Operatore Gestione Volontari',
            'modalId' => 'new_oV',
            'action' => 'report/nuovo_oV.php',
            'data' => getVolontari($conn),
            'table' => 'report.t_operatore_volontari',
            'joins' => '',
            'emptyMessage' => 'In questo momento non ci sono operatori Gestione Volontari',
        ],
        [
            'title' => 'Postazione presidio sanitario',
            'modalId' => 'new_anpas',
            'action' => 'report/nuovo_anpas.php',
            'data' => getVolontari($conn),
            'table' => 'report.t_operatore_anpas',
            'joins' => '',
            'emptyMessage' => 'In questo momento non ci sono operatori Postazione presidio sanitario',
        ],
    ];
    ?>

    <!-- Iterate and Render Rows Dynamically -->
    <?php foreach (array_chunk($roles, 2) as $row): ?>
<div class="row">
    <?php foreach ($row as $role): ?>
    <div class="col-sm-6">
        <hr>
        <h4>
            <?= htmlspecialchars($role['title']) ?>
            <?php if ($profilo_sistema <= 3): ?>
            <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#<?= $role['modalId'] ?>">
                <i class="fas fa-plus"></i> Aggiungi
            </button>
            <?php endif; ?>
        </h4>

        <?php
            renderModal($role['modalId'], "Inserire " . strtolower($role['title']), $role['action'], $role['data']);

            $result = getTurni($conn, $role['table'], $role['joins'], '', $id);

            if ($result && pg_num_rows($result) > 0) {
                visualizzaTurni($result, $role['emptyMessage']);
            } else {
                echo '- <i class="fas fa-circle" style="color: red;"></i> ' . htmlspecialchars($role['emptyMessage']) . '<br>';
            }
        ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
