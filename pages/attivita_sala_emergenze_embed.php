<div class="container">

    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div id="feedbackMessage" class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>" role="alert">
            <?php echo htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <script src="./scripts/feedback_turni.js"></script>
    <?php endif; ?>


    <!-- Titolo della pagina -->
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h3>Attività sala emergenze</h3>
        </div>
    </div>

    <?php
        include './scripts/gestione_turni.php';

        // Determina se stai chiamando da report oppure no
        $id_evento = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;


        // Coordinatore di Sala
        renderShiftSection([
            'title' => 'Coordinatore di Sala',
            'modal_id' => 'new_coord',
            'db_table' => 'report.t_coordinamento',
            'query_type' => 'dipendenti',
            'emptyMessage' => 'In questo momento non ci sono coordinatori di sala.'
        ], $conn, $profilo_sistema, $id_evento);

        
        // Operatore Monitoraggio Meteo
        renderShiftSection([
            'title' => 'Monitoraggio Meteo',
            'modal_id' => 'new_mm',
            'db_table' => 'report.t_monitoraggio_meteo',
            'query_type' => 'meteo',
            'emptyMessage' => 'In questo momento non ci sono responsabili Monitoraggio Meteo.'
        ], $conn, $profilo_sistema, $id_evento);


        // Operatore Presidi Territoriali
        renderShiftSection([
            'title' => 'Operatore Presidi Territoriali',
            'modal_id' => 'new_pt',
            'db_table' => 'report.t_presidio_territoriale',
            'query_type' => 'dipendenti',
            'emptyMessage' => 'In questo momento non ci sono operatori Presidi Territoriali.'
        ], $conn, $profilo_sistema, $id_evento);

        
        // Tecnico Protezione Civile
        renderShiftSection([
            'title' => 'Tecnico Protezione Civile',
            'modal_id' => 'new_tPC',
            'db_table' => 'report.t_tecnico_pc',
            'query_type' => 'dipendenti',
            'emptyMessage' => 'In questo momento non ci sono tecnici Protezione Civile.'
        ], $conn, $profilo_sistema, $id_evento);

        
        // Operatore Gestione Volontari
        renderShiftSection([
            'title' => 'Operatore Gestione Volontari',
            'modal_id' => 'new_oV',
            'db_table' => 'report.t_operatore_volontari',
            'query_type' => 'volontari',
            'emptyMessage' => 'In questo momento non ci sono operatori Gestione Volontari.'
        ], $conn, $profilo_sistema, $id_evento);

        
        // Postazione Presidio Sanitario
        renderShiftSection([
            'title' => 'Postazione Presidio Sanitario',
            'modal_id' => 'new_anpas',
            'db_table' => 'report.t_operatore_anpas',
            'query_type' => 'volontari',
            'emptyMessage' => 'In questo momento non ci sono operatori Postazione Presidio Sanitario.'
        ], $conn, $profilo_sistema, $id_evento);

        
        // Operatore Numero Verde
        renderShiftSection([
            'title' => 'Operatore Numero Verde',
            'modal_id' => 'new_oNV',
            'db_table' => 'report.t_operatore_nverde',
            'query_type' => 'dipendenti',
            'emptyMessage' => 'In questo momento non ci sono operatori Numero Verde.'
        ], $conn, $profilo_sistema, $id_evento);
?>