<?php 
require('./req.php');
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');
require('./check_evento.php');
require('./scripts/reportistica_functions.php');

// Redirect to access restriction page se non ha permessi
if ($profilo_sistema > 3) {
    header("location: ./divieto_accesso.php");
}
$subtitle = "Reportistica";

// Sanifica l'input ID
$id = $_GET['id'];

// Recupera i dati dell'evento
$evento = getEvento($conn, $id);

$data_ora_inizio_evento = $evento['data_ora_inizio_evento'];
$data_ora_chiusura = $evento['data_ora_chiusura'];
$data_ora_fine_evento = $evento['data_ora_fine_evento'];

if (!$evento) {
    echo "<p>Evento non trovato.</p>";
    exit;
}

// Recupera la data e l'ora correnti
$dateTime = getCurrentDateTime();

// Recupera i dati dei municipi
$municipi = getMunicipi($conn, $id);

// Processa i dettagli dell'evento
$eventDetails = processEventDetails(
    $data_ora_inizio_evento,
    $data_ora_chiusura,
    $data_ora_fine_evento
);

$details = $eventDetails['details'];
$views = $eventDetails['views']; // questo array di viste serve per andare a formattare le segnalazioni appropriate v. getSegnalazioni

//DEBUG

// echo print_r($views);
// exit;

$v_incarichi_last_update = $views['v_incarichi_last_update'];
$v_incarichi_interni_last_update = $views['v_incarichi_interni_last_update'];
$v_provvedimenti_cautelari_last_update = $views['v_provvedimenti_cautelari_last_update'];
$v_sopralluoghi_last_update = $views['v_sopralluoghi_last_update'];

$giorno = date("d/m");
$orari = getMonitoraggioOrari();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="simone" >

    <title>Gestione emergenze</title>

    <?php 

    ?>

    <!-- Link to CSS file -->
    <link rel="stylesheet" type="text/css" href="./styles/attivita_sala_emergenze.css">
</head>

<body>
    <div id="wrapper">
        <div id="navbar1">
            <?php
                require('navbar_up.php');
            ?>
        </div> 

        <?php 
            require('./navbar_left.php')
        ?> 

        <div id="page-wrapper">
            <div class="row">
                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                    <h3>Evento n. <?= htmlspecialchars($id); ?> - Tipo: <?= htmlspecialchars($evento['descrizione']); ?> -
                        <?php if ($profilo_sistema > 0 && $profilo_sistema <= 3): ?>
                            <button class="btn btn-info noprint" onclick="printDiv('page-wrapper')">
                                <i class="fa fa-print" aria-hidden="true"></i> Stampa report 
                            </button>
                        <?php endif; ?>
                    </h3>
                </div>

                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <h3> Data: <?= htmlspecialchars($dateTime['date']); ?><br>
                        Ora: <?= htmlspecialchars($dateTime['time']); ?>
                    </h3>
                </div>
            </div>
            
            <hr>

            <div class="row">
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <img src="../img/pc_ge_sm.png" alt="">
                </div>
                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                        <?php if ($evento['nota']): ?>
                            <h2><?= htmlspecialchars($evento['nota']); ?></h2>
                        <?php endif; ?>
                    <b>Municipi interessati</b>: 
                        <?php if (!empty($municipi)): ?>
                            <?php foreach ($municipi as $municipio): ?>
                                <?= htmlspecialchars($municipio['nome_munic']); ?>,
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span>Nessun municipio disponibile</span>
                        <?php endif; ?>
                    <br>
                        <b>Data e ora inizio</b>: <?= htmlspecialchars($data_ora_inizio_evento); ?>
                        <!-- altri dettagli evento -->
                        <?php foreach ($details as $detail): ?>
                            <p><?= $detail; ?></p>
                        <?php endforeach; ?>
                </div>
                <hr>
            </div>

            <hr>

            <div class="row">
                    <?php require('./allerte_embed.php'); ?>
            </div>
            
            <div class="row">
                <?php require('./monitoraggio_meteo_embed.php'); ?>
            </div>
            
            <hr>

            <div class="row">
                <?php require('./comunicazioni_embed.php'); ?>
            </div>
                        
            <div class="row">
                <?php require('./attivita_sala_emergenze_embed.php'); ?>
            </div>
            
            <hr>

            <div class="row">
			    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h3>Comunicazioni e informazioni alla popolazione</h3>
			    </div>

                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <?php echo numeroVerdeFormatter($conn, $id); ?>
                </div>

                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <h4>Numero chiamate ricevute</h4>
                    <?php
                        echo "<b>Richieste generiche:</b> " . htmlspecialchars($chiamate['richieste_generiche']) . "<br>";
                        echo "<b>Segnalazioni:</b> " . htmlspecialchars($chiamate['segnalazioni']) . "<br>";
                    ?>
                </div>
                
            </div>

            <hr>

            <!-- REPORT SEGNALAZIONI -->
            <div class="row">
               
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h3>Elenco segnalazioni </h3>
			    </div>
            </div>
            <div class="row">            
            
                <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">			
                    <h4>Riepilogo</h4>
                </div>



                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
                    <svg width="400" height="300"></svg>
                    <?php
                    require('./grafico_criticita.php');
                    ?>
                </div>


                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">			
                    <table  id="segnalazioni_count" class="table table-condensed" 
                    style="word-break:break-all; word-wrap:break-word;" data-toggle="table" 
                    data-url="./tables/griglia_segnalazioni_conteggi.php?id=<?php echo $id?>" 
                    data-show-export="false" data-search="false" data-click-to-select="false" 
                    data-pagination="false" data-sidePagination="false" data-show-refresh="false" 
                    data-show-toggle="false" data-show-columns="false" data-toolbar="#toolbar">

                    <thead>

                    <tr>
                    <th data-field="criticita" data-sortable="false" data-visible="true" >Tipologia</th>
                    <th data-field="pervenute" data-sortable="true" data-visible="true">Pervenute</th>
                    <th data-field="risolte" data-sortable="true" data-visible="true">Risolte</th>
                    </tr>
                    </thead>
                    <tbody>
                        <!-- Le righe verranno caricate dinamicamente -->
                    </tbody>
                    </table>
                </div>             
            </div>
            
            <hr>

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">			 
                    <h4>Dettaglio segnalazioni in elaborazione o chiuse</h4>
                    <?php
                        echo getDettaglioSegnalazioni($conn, $id, $v_incarichi_last_update, $v_incarichi_interni_last_update, $v_provvedimenti_cautelari_last_update, $v_sopralluoghi_last_update);
                    ?>
                </div>                                            
            </div>

            <hr>

            <!-- REPORT PROVVEDIMENTI CAUTELARI -->
            <div class="row">              
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h3>
                        Elenco provvedimenti cautelari:
                    </h3>
                    <table  id="pc_count" class="table table-condensed" 
                    style="word-break:break-all; word-wrap:break-word;" data-toggle="table" 
                    data-url="./tables/griglia_pc_report.php?id=<?php echo $id?>" 
                    data-show-export="false" data-search="false" data-click-to-select="false" 
                    data-pagination="false" data-sidePagination="false" data-show-refresh="true" 
                    data-show-toggle="false" data-show-columns="false" data-toolbar="#toolbar">

                        <thead>
                            <tr>
                                <th data-field="tipo_provvedimento" data-sortable="false" data-visible="true" >Tipologia</th>
                                <th data-field="descrizione_stato" data-sortable="true" data-visible="true">Stato</th>
                                <th data-field="count" data-sortable="true" data-visible="true">Totale</th>
                            </tr>
                        </thead>
                    </table>
                        <?php
                            echo getElencoProvvedimentiCautelari($conn, $id);
                        ?>
                </div>
            </div>
            
            <!-- REPORT MIRE -->
            <?php 
            if ($id_evento == 3 || $id_evento == 1): ?>
            <div class="row">              
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h3>Monitoraggio (Letture Mire e Idrometri nelle 8 ore precedenti)</h3>

                    <h4>Letture Mire e Rivi</h4>

                    <h5><a name="mire-tab1">Tab. 1</a>: acquisizioni dalle <?php echo $orari[16] ?> alle <?php echo $orari[9] ?> 
                        (<a title="Vai alle acquisizioni più recenti" href="#mire-tab2"><i class="fas fa-angle-down"></i></a>)
                    </h5>
                    <table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_mire_report.php" 
                        data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json','xml','csv','txt','sql','excel','doc','pdf'] 
                        data-search="true" data-click-to-select="true" data-show-print="true"  
                        data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
                        data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
                        data-filter-control="true" data-toolbar="#toolbar">
            
                        <thead>
                            <tr>
                                <th class="noprint" data-field="state" data-checkbox="true"></th>    
                                <th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Rio</th>
                                <th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
                                <?php for ($i = 16; $i >= 9; $i--): ?>
                                    <th data-field="<?php echo $i; ?>" data-sortable="false" data-formatter="nameFormatterLettura" 
                                        data-visible="true"><?php echo $giorno . '<br>' . $orari[$i]; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                    </table>

                    <h5><a name="mire-tab2">Tab. 2</a>: acquisizioni dalle <?php echo $orari[8] ?> alle <?php echo $orari[0] ?> 
                        (<a title="Vai alle acquisizioni meno recenti" href="#mire-tab1"><i class="fas fa-angle-up"></i></a>)
                    </h5>
                    <table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_mire_report.php" 
                        data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json','xml','csv','txt','sql','excel','doc','pdf'] 
                        data-search="true" data-click-to-select="true" data-show-print="true"  
                        data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
                        data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
                        data-filter-control="true" data-toolbar="#toolbar">
                
                        <thead>
                            <tr>
                                <th class="noprint" data-field="state" data-checkbox="true"></th>    
                                <th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Rio</th>
                                <th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
                                <?php for ($i = 9; $i >= 0; $i--): ?>
                                    <th data-field="<?php echo $i; ?>" data-sortable="false" data-formatter="nameFormatterLettura" 
                                        data-visible="true"><?php echo $giorno . '<br>' . $orari[$i]; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                    </table>

                    <br></br>

                    <h4>Valori Idrometri ARPAL </h4>
                    <h5><a name="mire-tab3">Tab. 1</a>: acquisizioni dalle <?php echo $orari[16] ?> alle <?php echo $orari[9] ?> 
                        (<a href="#mire-tab4" title="Vai alle acquisizioni più recenti"><i class="fas fa-angle-down"></i></a>)
                    </h5>
                    <table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_idro_arpal_report.php" 
                    data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
                    data-search="true" data-click-to-select="true" data-show-print="true"  
                    data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
                    data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
                    data-filter-control="true" data-toolbar="#toolbar">
            
                    <thead>

                        <tr>
                            <th class="noprint" data-field="state" data-checkbox="true"></th>    
                            <th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Idrometro</th>
                                <?php for ($i = 16; $i >= 9; $i--): ?>
                                    <th data-field="<?php echo $i; ?>" data-sortable="false" data-formatter="nameFormatterLettura" 
                                        data-visible="true"><?php echo $giorno . '<br>' . $orari[$i]; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                    </table>

                    <h5><a name="mire-tab4">Tab. 2</a>: acquisizioni dalle <?php echo $orari[8] ?> alle <?php echo $orari[0] ?> 
                        (<a title="Vai alle acquisizioni meno recenti" href="#mire-tab3"><i class="fas fa-angle-up"></i></a>)
                    </h5>
                    <table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_idro_arpal_report.php" 
                        data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json','xml','csv','txt','sql','excel','doc','pdf'] 
                        data-search="true" data-click-to-select="true" data-show-print="true"  
                        data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
                        data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
                        data-filter-control="true" data-toolbar="#toolbar">
                
                        <thead>
                            <tr>
                                <th class="noprint" data-field="state" data-checkbox="true"></th>    
                                <th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Rio</th>
                                <th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
                                <?php for ($i = 9; $i >= 0; $i--): ?>
                                    <th data-field="<?php echo $i; ?>" data-sortable="false" data-formatter="nameFormatterLettura" 
                                        data-visible="true"><?php echo $giorno . '<br>' . $orari[$i]; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                    </table>

                    <br></br>

                    <h4>Valori Idrometri COMUNE </h4>
                    <h5><a name="mire-tab5">Tab. 1</a>: acquisizioni dalle <?php echo $orari[16] ?> alle <?php echo $orari[9] ?> 
                        (<a title="Vai alle acquisizioni più recenti" href="#mire-tab6"><i class="fas fa-angle-down"></i></a>)
                    </h5>				
                    <table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_idro_com_report.php" 
                    data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
                    data-search="true" data-click-to-select="true" data-show-print="true"  
                    data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
                    data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
                    data-filter-control="true" data-toolbar="#toolbar">
            
                    <thead>

                        <tr>
                            <th class="noprint" data-field="state" data-checkbox="true"></th>    
                            <th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Idrometro</th>
                            <?php for ($i = 16; $i >= 9; $i--): ?>
                                    <th data-field="<?php echo $i; ?>" data-sortable="false" data-formatter="nameFormatterLettura" 
                                        data-visible="true"><?php echo $giorno . '<br>' . $orari[$i]; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                    </table>

                    <h5><a name="mire-tab6">Tab. 2</a>: acquisizioni dalle <?php echo $orari[8] ?> alle <?php echo $orari[0] ?> 
                        (<a title="Vai alle acquisizioni meno recenti" href="#mire-tab5"><i class="fas fa-angle-up"></i></a>)
                    </h5>				
                        <table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_idro_com_report.php" 
                        data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
                        data-search="true" data-click-to-select="true" data-show-print="true"  
                        data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
                        data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
                        data-filter-control="true" data-toolbar="#toolbar">
                
                        <thead>

                            <tr>
                                <th class="noprint" data-field="state" data-checkbox="true"></th>    
                                <th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Idrometro</th>
                                <?php for ($i = 9; $i >= 0; $i--): ?>
                                    <th data-field="<?php echo $i; ?>" data-sortable="false" data-formatter="nameFormatterLettura" 
                                        data-visible="true"><?php echo $giorno . '<br>' . $orari[$i]; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                    </table>

                    <script>
                    function nameFormatterLettura(value,row) {
                        if(row.tipo=='IDROMETRO ARPA' ){
                            <?php
                            $query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_arpa WHERE cod='?>row.id<?php';";
                            $result_soglie = pg_query($conn, $query_soglie);
                            while($r_soglie = pg_fetch_assoc($result_soglie)) {
                                $arancio=$r_soglie['liv_arancione'];
                                $rosso=$r_soglie['liv_rosso'];
                            }
                            ?>
                            if(value < row.arancio ){
                                return '<font style="color:#00bb2d;">'+Math.round(value*1000)/1000+'</font>';
                            } else if (value > row.arancio && value < row.rosso) {
                                return '<font style="color:#FFC020;">'+Math.round(value*1000)/1000+'</font>';
                            } else if (value > row.rosso) {
                                return '<font style="color:#cb3234;">'+Math.round(value*1000)/1000+'</font>';
                            } else {
                                return '-';
                            }
                        } else if(row.tipo=='IDROMETRO COMUNE'){
                            <?php
                            $query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_comune WHERE id='?>row.id<?php';";
                            $result_soglie = pg_query($conn, $query_soglie);
                            while($r_soglie = pg_fetch_assoc($result_soglie)) {
                                $arancio=$r_soglie['liv_arancione'];
                                $rosso=$r_soglie['liv_rosso'];
                            }
                            ?>
                            if(value < row.arancio ){
                                return '<font style="color:#00bb2d;">'+Math.round(value*1000)/1000+'</font>';
                            } else if (value > row.arancio && value < row.rosso) {
                                return '<font style="color:#FFC020;">'+Math.round(value*1000)/1000+'</font>';
                            } else if (value > row.rosso) {
                                return '<font style="color:#cb3234;">'+Math.round(value*1000)/1000+'</font>';
                            } else {
                                return '-';
                            }
                        } else {
                            if(value==1){
                                return '<i class="fas fa-circle" title="Livello basso" style="color:#00bb2d;"></i>';
                            } else if (value==2) {
                                return '<i class="fas fa-circle" title="Livello medio" style="color:#ffff00;"></i>';
                            } else if (value==3) {
                                return '<i class="fas fa-circle" title="Livello alto" style="color:#cb3234;"></i>';
                            } else {
                                return '-';
                            }
                        }		
                    }
                    </script>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <?php
                        $date = date_create(date(), timezone_open('Europe/Berlin'));
                        $data = date_format($date, 'd-m-Y');
                        $ora = date_format($date, 'H:i');
                            echo "<hr><div align='center'>Il presente report è stato ottenuto in maniera automatica utilizzando il Sistema 
                            di Gestione delle Emergenze in data ".$data ." alle ore " .$ora.". 
                            </div>";
                    ?>
                </div>
            </div>

        </div> <!-- page-wrapper -->
    </div> <!-- wrapper -->

    <?php
        require('./footer.php');
        require('./req_bottom.php');
    ?>
    <!-- <script src="./scripts/attivita_sala_emergenze.js"></script> -->
</body>

</html>