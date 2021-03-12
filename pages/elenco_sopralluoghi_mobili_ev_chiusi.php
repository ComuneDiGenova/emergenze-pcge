<?php 

$subtitle="Elenco presidi mobili (eventi attivi o in chiusura)";


$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];
if(isset($_GET["from"])){
	$filtro_from=$_GET["from"];
}
if(isset($_GET["to"])){
	$filtro_to=$_GET["to"];
}
//echo $filtro_evento_attivo; 


$uri=basename($_SERVER['REQUEST_URI']);
//echo $uri;

$pagina=basename($_SERVER['PHP_SELF']); 


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

require('./tables/filtri_segnalazioni.php');
?>
    
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
            <!--div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco segnalazioni</h1>
                </div>
            </div-->

            
            <br><br>
            <div class="row">

<a class="btn btn-primary" data-toggle="collapse" href="#collapsedata" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="fas fa-hourglass"></i>  Filtra per data
         </a>

		  


<div class="collapse" id="collapsedata">
          <div class="card card-body">
         		  
          <form id="filtro_data" action="./tables/decodifica_filtro_data.php" method="post">
            <input type="hidden" name="pagina" id="hiddenField" value="<?php echo $pagina; ?>"/>
			
				<div class="form-check col-md-6">
				<label for="startdate">Da (AAAA/MM/GG HH:MM):</label>
				<input type="text" class="form-control" id="startdate" name="startdate" value="<?php echo str_replace("'", "", $filtro_from)?>">
				<small id="sdateHelp" class="form-text text-muted"> Inserire la data e l'ora (opzionale)</small>
				</div>
				
				
				<div class="form-check col-md-6">
				<label for="todate">A (AAAA/MM/GG HH:MM):</label>
				<input type="text" class="form-control" id="todate" name="todate" value="<?php echo str_replace("'", "", $filtro_to)?>">
				<small id="tdateHelp" class="form-text text-muted"> Inserire la data e l'ora (opzionale)</small>
				</div>
			
			
			<button id="checkBtn_filtri" type="submit" class="btn btn-primary"> 
			<?php if ($getfiltri=='' or intval($getfiltri)==0) {?>
				Filtra 
			<?php } else {?>
				Aggiorna filtro
			<?php }?>
			</button>
			

        </form>
          </div>
        </div>


        <hr>
        
	
        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        

        <table  id="pres" class="table-hover" data-toggle="table"  data-filter-control="true" 
        data-url="./tables/griglia_sopralluoghi_mobili_eventi_chiusi.php?from=<?php echo $filtro_from;?>&to=<?php echo $filtro_to;?>" 
        data-height="900" data-show-export="true" data-search="true" data-click-to-select="true" 
        data-pagination="true" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" 
        data-show-columns="true" data-toolbar="#toolbar">


        
        
<thead>

 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
            <th data-field="id_stato_sopralluogo" data-sortable="true" data-formatter="presidiFormatter" data-visible="true" >Stato</th> 
            <!--th data-field="tipo_provvedimento" data-sortable="true" data-visible="true">Tipo</th-->
			<th data-field="id_evento" data-sortable="true" data-filter-control="select" data-visible="true" >Evento</th> 
				<th data-field="descrizione_uo" data-sortable="true"  data-visible="true">Ultima squadra</th>
            <th data-field="descrizione" data-sortable="true"  data-filter-control="select" data-visible="true">Descrizione</th>
            <!--th data-field="id_evento" data-sortable="true"  data-visible="true">Id<br>evento</th-->
            <th data-field="data_ora_invio" data-sortable="true" data-filter-control="input" data-visible="true">Data e ora<br>assegnazione</th>
            <!--th data-field="time_start" data-sortable="true"  data-visible="true">Ora<br>inizio</th>
            <th data-field="time_stop" data-sortable="true"  data-visible="true">Ora<br>fine</th>
            <th data-field="note" data-sortable="false" data-visible="true" >Note</th-->
            <th data-field="id" data-sortable="false" data-formatter="presidiFormatterEdit" data-visible="true" >Dettagli</th>            
				<!--th data-field="id_segnalazione" data-sortable="false" data-formatter="nameFormatterEdit1" data-visible="true" >Segnalazione</th-->
    </tr>
</thead>

</table>




<br><br>

<script>


 function presidiFormatter(value) {
        if (value==2){
        		return '<i class="fas fa-play" title="Preso in carico" style="color:#5cb85c"></i>';
        } else if (value==3) {
        	   return '<i class="fas fa-stop" title="Chiuso"></i>';
        } else if (value==1){
        	   return '<i class="fas fa-exclamation" title="Da prendere in carico" style="color:#ff0000"></i>';
        }

    }

 function presidiFormatterEdit(value) {
        
		return '<a class="btn btn-warning" href=./dettagli_sopralluogo_mobile.php?id='+value+'> <i class="fas fa-edit"></i> </a>';
 
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
<script>

    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#pres');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    })
</script>

    
</body>





</html>
