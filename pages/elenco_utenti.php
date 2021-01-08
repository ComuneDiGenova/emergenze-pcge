<?php 


$subtitle="Elenco utenti";


$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];

//echo $filtro_evento_attivo; 


$uri=basename($_SERVER['REQUEST_URI']);
//echo $uri;

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

if ($profilo_ok==3){
	$subtitle="Elenco utenti (completo)";
} else {
	$subtitle="Elenco utenti (tua Unità Operativa)";
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
            <!--div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco segnalazioni</h1>
                </div>
            </div-->
			<br>
			<?php
            if ($profilo_ok==3){
				$filter = ' ';
			} else if($profilo==8){
				$filter= ' WHERE id_profilo=\''.$profilo.'\' and nome_munic = \''.$livello.'\' ';
			} else {
				$filter= ' WHERE id_profilo=\''.$profilo.'\' ';
			}
						
			$query="SELECT count(matricola_cf) From \"users\".\"v_utenti_sistema\" ".$filter." ;";

			//echo $query;
			$result = pg_query($conn, $query);

			while($r = pg_fetch_assoc($result)) {
				if ($profilo_ok==3){
					echo '<i class="fas fa-users  faa-ring animated"></i> '. $r['count']. ' utenti registrati a sistema';
				} else {
					echo '<i class="fas fa-users faa-ring animated"></i> '. $r['count']. ' utenti della tua unit� operativa abilitati';
				}
				
			}	
						
			?>			
            <br>
            <div class="row">

		<?php //echo $profilo_ok;?>
		<br>
		<?php //echo $livello1;?>
	
        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        

        <table  id="users" class="table-hover" data-toggle="table" data-url="./tables/griglia_utenti_sistema.php?p=<?php echo $profilo_ok;?>&l=<?php echo $livello1;?>" data-height="900" data-show-export="true" data-search="true" data-click-to-select="true" data-pagination="false" data-sidePagination="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-toolbar="#toolbar">


        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th-->
            <th data-field="matricola_cf" data-sortable="true" data-visible="true" >CF o<br>matricola</th> 
            <!--th data-field="tipo_provvedimento" data-sortable="true" data-visible="true">Tipo</th-->
			<th data-field="cognome" data-sortable="true"  data-visible="true">Cognome</th>
            <th data-field="nome" data-sortable="true"   data-visible="true">Nome</th>
            <th data-field="profilo" data-sortable="true"  data-visible="true">Tipo<br>profilo</th>
			<th data-field="valido" data-sortable="true" data-formatter="nameFormatter" data-visible="true">Stato</th>
            <th data-field="matricola_cf" data-sortable="false" data-formatter="nameFormatterEdit" data-visible="true" >Dettagli</th>            
    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#users');
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


 function nameFormatter(value) {
        if (value=='t'){
        		return '<i class="fas fa-play" style="color:#5cb85c"></i>';
        } else if (value=='f') {
        	   return '<i class="fas fa-stop"></i>';
        } else {
        	   return '<i class="fas fa-pause" style="color:#ff0000"></i>';;
        }

    }

 function nameFormatterEdit(value) {
    if (value.length==16){
		return '<a class="btn btn-warning" href="./update_volontario.php?id='+value+'"> <i class="fas fa-edit"></i> </a>';
	} else {
		return '<a class="btn btn-warning" href="./permessi.php?id='+value+'"> <i class="fas fa-edit"></i> </a>';
    }
 }

 function nameFormatterEdit1(value) {
        if (value){
			return '<a class="btn btn-warning" href=./dettagli_segnalazione.php?id='+value+'> <i class="fas fa-search"></i> </a>';
		} else {
			return '-';
		}
    }

  function nameFormatterRischio(value) {
        //return '<i class="fas fa-'+ value +'"></i>' ;
        
        if (value=='t'){
        		return '<i class="fas fa-exclamation-triangle" style="color:#ff0000"></i>';
        } else if (value=='f') {
        	   return '<i class="fas fa-check" style="color:#5cb85c"></i>';
        }
        else {
        		return '<i class="fas fa-question" style="color:#505050"></i>';
        }
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
