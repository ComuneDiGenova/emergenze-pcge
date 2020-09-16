<?php 

$subtitle="Lista eventi e reportistica";

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione emergenze</title>
<?php 
require('./req.php');

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

require('./check_evento.php');

    
    //Controllo se autorizzato a modificare permessi
$check_operatore=0;
if ($profilo_sistema == 1){
	$check_operatore=1;
}

if ($profilo_sistema > 6){
	header("location: ./divieto_accesso.php");
}

?>

</head>

<body>

    <div id="wrapper">

        <?php 
            require('./navbar_up.php')
        ?>  
        <?php 
            require('./navbar_left.php')
        ?> 
            

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Lista eventi registrati a sistema</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <br>
            <?php
				if ($check_operatore == 0){
					echo '<h4><i class="fas fa-minus-circle"></i> L\'utente non è autorizzato a modificare i permessi utenti</h4><hr> ';
				}
				?>
            <br>
            <div class="row">



        
        <table  id="t_eventi" class="table-hover" data-toggle="table" data-url="./tables/griglia_elenco_eventi.php" data-height="900"  data-show-export="false" data-search="true" data-click-to-select="true" data-pagination="true" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
			 <!--th class="col-md-2" data-field="id" data-sortable="true"  data-visible="true" >Id</th-->
			<?php
				if ($check_operatore <= 3){
ù				?>
            <th class="col-md-2" data-field="id" data-sortable="true" data-formatter="nameFormatter1" data-visible="true" >Report</th>
				<?php 
				}
				?>
			<th class="col-md-2" data-field="id" data-sortable="true"  data-visible="true" >Id</th>
	         <th class="col-md-2" data-field="descrizione" data-sortable="true"  data-visible="true" >Tipologia</th>
	         <th class="col-md-3" data-field="nota" data-sortable="true"  data-visible="true" >Nota</th>
	  	      <th class="col-md-2" data-field="data_ora_inizio_evento" data-sortable="true"  data-visible="true" >Inizio</th>      
	        	<th class="col-md-2" data-field="data_ora_fine_evento" data-sortable="true"  data-visible="true" >Fine</th>
            <th class="col-md-1" data-field="valido" data-sortable="true" data-formatter="nameFormatter0" data-visible="true" >Stato</th>
            <!--th data-field="cod" data-sortable="false" data-formatter="nameFormatter0" data-visible="true" >Fragilità</th-->

    </tr>
</thead>

</table>


<script>


function nameFormatter0(value) {

	if (value=='t'){
        return '<i class="fa fa-play" aria-hidden="true" title="In corso"></i>';
	} else if (value=='f') {
		  return '<i class="fa fa-stop" aria-hidden="true" title="Chiuso"></i>';
	} else {
		return '<i class="fa fa-hourglass-half" aria-hidden="true" title="In chiusura"></i>';
	}
}


  function nameFormatter1(value) {

        return '<a href="./reportistica.php?id=\''+ value + '\'" class="btn btn-info" title=Report 8 h (riepilogo segnalazioni in corso di evento)" role="button">\
		<i class="fa fa-file-invoice" aria-hidden="true"></i> 8h </a>\
		<a href="./reportistica_personale.php?id=\''+ value + '\'" class="btn btn-info" title=Report esteso (dettagli squadre e personale impiegato)" role="button">\
		<i class="fa fa-file-invoice" aria-hidden="true"></i> Esteso </a>';
    }


</script>





            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    

</body>

</html>
