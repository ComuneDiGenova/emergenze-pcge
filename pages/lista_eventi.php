<?php 
    $subtitle = "Lista eventi e reportistica";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto">
    <title>Gestione emergenze</title>

    <?php 
        require('./req.php');
        require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');
        require('./check_evento.php');

        // Controllo se autorizzato a modificare permessi
        $check_operatore = ($profilo_sistema == 1) ? 1 : 0;

        // Verifica il profilo per l'accesso
        if ($profilo_sistema > 6) {
            header("location: ./divieto_accesso.php");
        }
    ?>

</head>

<body>

    <div id="wrapper">
        <div id="navbar1">
            <?php require('navbar_up.php'); ?>
        </div>  

        <?php require('./navbar_left.php'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Lista eventi registrati a sistema</h1>
                </div>
            </div>
            
            <br>
            <?php
                if ($check_operatore == 0) {
                    echo '<h4><i class="fas fa-minus-circle"></i> L\'utente non è autorizzato a modificare i permessi utenti</h4><hr>';
                }
            ?>
            <br>

            <div class="row">        
                <table id="t_eventi" class="table-hover" data-toggle="table" 
                    data-url="./tables/griglia_elenco_eventi.php" 
                    data-height="900"  
                    data-show-export="false" 
                    data-search="true" 
                    data-click-to-select="true" 
                    data-pagination="true" 
                    data-sidePagination="true" 
                    data-show-refresh="true" 
                    data-show-toggle="false" 
                    data-show-columns="true" 
                    data-toolbar="#toolbar">

                    <thead>
                        <tr>
                            <?php if ($check_operatore <= 3): ?>
                                <th class="col-md-2" data-field="id" data-sortable="true" data-formatter="nameFormatter1" data-visible="true">Report</th>
                            <?php endif; ?>
                            
                            <th class="col-md-2" data-field="id" data-sortable="true" data-visible="true">Id</th>
                            <th class="col-md-2" data-field="descrizione" data-sortable="true" data-visible="true">Tipologia</th>
                            <th class="col-md-3" data-field="nota" data-sortable="true" data-visible="true">Nota</th>
                            <th class="col-md-2" data-field="data_ora_inizio_evento" data-sortable="true" data-visible="true">Inizio</th>      
                            <th class="col-md-2" data-field="data_ora_fine_evento" data-sortable="true" data-visible="true">Fine</th>
                            <th class="col-md-1" data-field="valido" data-sortable="true" data-formatter="nameFormatter0" data-visible="true">Stato</th>
                        </tr>
                    </thead>
                </table>

            </div> <!-- row -->
        </div> <!-- page-wrapper -->
    </div> <!-- wrapper -->

    <script src="./scripts/lista_eventi.js"></script>

    <?php 
        require('./footer.php');
        require('./req_bottom.php');
    ?> 

</body>
</html>
