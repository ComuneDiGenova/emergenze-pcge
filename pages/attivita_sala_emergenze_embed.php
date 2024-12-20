<div class="container">
    <!-- Titolo della pagina -->
    <div class="row">
        <div class="col-12">
            <h3>Attività sala emergenze - EVOLUTIVA A-3-T55</h3>
        </div>
    </div>

    <?php
        include './scripts/gestione_turni.php';


        // Coordinatore di Sala
        renderShiftSection([
            'title' => 'Coordinatore di Sala',
            'modal_id' => 'new_coord',
            'db_table' => 'report.t_coordinamento',
            'personnel_query' => "SELECT matricola, cognome, nome, settore, ufficio 
                                FROM varie.v_dipendenti 
                                ORDER BY cognome",
            'emptyMessage' => 'In questo momento non ci sono coordinatori di sala.'
        ], $conn, $profilo_sistema);

        
        // Operatore Monitoraggio Meteo
        renderShiftSection([
            'title' => 'Monitoraggio Meteo',
            'modal_id' => 'new_mm',
            'db_table' => 'report.t_monitoraggio_meteo',
            'personnel_query' => "SELECT cf as matricola, cognome, nome, '' as livello1 
                                FROM users.v_utenti_esterni 
                                WHERE id1=9
                                UNION 
                                SELECT matricola, cognome, nome, settore || ' - '|| ufficio as livello1 
                                FROM varie.v_dipendenti",
            'emptyMessage' => 'In questo momento non ci sono responsabili Monitoraggio Meteo.'
        ], $conn, $profilo_sistema);


        // Operatore Presidi Territoriali
        renderShiftSection([
            'title' => 'Operatore Presidi Territoriali',
            'modal_id' => 'new_pt',
            'db_table' => 'report.t_presidio_territoriale',
            'personnel_query' => "SELECT matricola, cognome, nome, settore, ufficio
                                 FROM varie.v_dipendenti 
                                 ORDER BY cognome",
            'emptyMessage' => 'In questo momento non ci sono operatori Presidi Territoriali.'
        ], $conn, $profilo_sistema);

        
        // Tecnico Protezione Civile
        renderShiftSection([
            'title' => 'Tecnico Protezione Civile',
            'modal_id' => 'new_tPC',
            'db_table' => 'report.t_tecnico_pc',
            'personnel_query' => "SELECT matricola, cognome, nome FROM varie.v_dipendenti ORDER BY cognome",
            'emptyMessage' => 'In questo momento non ci sono tecnici Protezione Civile.'
        ], $conn, $profilo_sistema);

        
        // Operatore Gestione Volontari
        renderShiftSection([
            'title' => 'Operatore Gestione Volontari',
            'modal_id' => 'new_oV',
            'db_table' => 'report.t_operatore_volontari',
            'personnel_query' => "SELECT cf as matricola, cognome, nome FROM users.v_utenti_esterni WHERE id1 IN (1, 8) UNION SELECT matricola, cognome, nome FROM varie.v_dipendenti",
            'emptyMessage' => 'In questo momento non ci sono operatori Gestione Volontari.'
        ], $conn, $profilo_sistema);

        
        // Postazione Presidio Sanitario
        renderShiftSection([
            'title' => 'Postazione Presidio Sanitario',
            'modal_id' => 'new_anpas',
            'db_table' => 'report.t_operatore_anpas',
            'personnel_query' => "SELECT cf as matricola, cognome, nome FROM users.v_utenti_esterni WHERE id1 IN (1, 8) UNION SELECT matricola, cognome, nome FROM varie.v_dipendenti",
            'emptyMessage' => 'In questo momento non ci sono operatori Postazione Presidio Sanitario.'
        ], $conn, $profilo_sistema);

        
        // Operatore Numero Verde
        renderShiftSection([
            'title' => 'Operatore Numero Verde',
            'modal_id' => 'new_oNV',
            'db_table' => 'report.t_operatore_numero_verde',
            'personnel_query' => "SELECT matricola, cognome, nome FROM varie.v_dipendenti ORDER BY cognome",
            'emptyMessage' => 'In questo momento non ci sono operatori Numero Verde.'
        ], $conn, $profilo_sistema);
?>



    


