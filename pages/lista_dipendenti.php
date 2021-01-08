<?php 

$subtitle="Gestione permessi dipendenti"

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
    

}
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
                    <h1 class="page-header">Elenco dipendenti comunali</h1>
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


        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        
        <table  id="t_dipendenti" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" data-url="./tables/griglia_elenco_dipendenti.php" data-height="900"  data-show-export="true" data-search="true" data-click-to-select="true" data-pagination="true" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th data-field="matricola" data-sortable="false"  data-visible="true">Matr</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="cognome" data-sortable="true"  data-visible="true">Cognome</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="nome" data-sortable="true"  data-visible="true">Nome</th>
	         <th data-field="direzione_area" data-sortable="true"  data-visible="true" >Direzione<br>(Area)</th>
	  	      <th data-field="settore" data-sortable="true"  data-visible="true" >Settore</th>      
	        	<th data-field="ufficio" data-sortable="true"  data-visible="true" >Ufficio</th>
            <th data-field="id_profilo" data-sortable="true"  data-visible="true" >Tipo<br>Profilo</th>
            <th data-field="stato_profilo" data-sortable="true" data-formatter="nameFormatter0" data-visible="true" >Stato<br>profilo</th>
            <!--th data-field="cod" data-sortable="false" data-formatter="nameFormatter0" data-visible="true" >Fragilità</th-->
            <?php
				if ($check_operatore == 1){
ù				?>
            <th data-field="matricola" data-sortable="false" data-formatter="nameFormatter1" data-visible="true" >Edit<br>permessi</th>            
				<?php 
				}
				?>

    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#t_dipendenti');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    })
</script>

<br><br>

<script>


function nameFormatter0(value) {

	if (value=='t'){
        return '<i class="fa fa-play" aria-hidden="true"></i>';
	} else if (value=='f') {
		  return '<i class="fa fa-pause" aria-hidden="true"></i>';
	} else {
		return '';
	}
}


  function nameFormatter1(value) {

        return '<a href="./permessi.php?id='+ value + '" class="btn btn-warning" title="Modifica permessi" role="button"><i class="fa fa-user-lock" aria-hidden="true"></i> </a>';
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
