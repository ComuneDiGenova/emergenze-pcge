<?php 

$subtitle="Elenco provvedimenti cautelari (eventi attivi o in chiusura)";


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
        

        <table  id="pc" class="table-hover" data-toggle="table" 
        data-url="./tables/griglia_pc.php?from=<?php echo $filtro_from;?>&to=<?php echo $filtro_to;?>" 
        data-height="900" data-show-export="true" data-search="true" data-click-to-select="true" 
        data-pagination="true" data-sidePagination="true" data-show-refresh="true" data-show-toggle="true" 
        data-show-columns="true" data-toolbar="#toolbar">


        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
			<th data-field="id_evento" data-sortable="true" data-visible="true">Id<br>evento</th>
			<th data-field="data_ora_invio" data-sortable="true" data-visible="true">Data<br>assegnazione</th>
            <th data-field="id_stato_provvedimenti_cautelari" data-formatter="presidiFormatter"data-sortable="true" data-visible="true" >Stato</th> 
            <th data-field="tipo_provvedimento" data-sortable="true" data-visible="true">Tipo</th>
			   <th data-field="oggetto" data-sortable="true"  data-visible="true">Localizzazione</th>
            <th data-field="descrizione" data-sortable="true"   data-visible="true">Descrizione</th>
            <!--th data-field="id_evento" data-sortable="true"  data-visible="true">Id<br>evento</th-->
            <!--th data-field="time_preview" data-sortable="true"  data-visible="true">Ora<br>prevista</th>
            <th data-field="time_start" data-sortable="true"  data-visible="true">Ora<br>inizio</th>
            <th data-field="time_stop" data-sortable="true"  data-visible="true">Ora<br>fine</th-->
            <th data-field="note_ente" data-sortable="false" data-visible="true" >Note</th>
            <th data-field="id" data-sortable="false" data-formatter="nameFormatterEdit" data-visible="true" >Dettagli</th>            
				<th data-field="id_segnalazione" data-sortable="false" data-formatter="nameFormatterEdit1" data-visible="true" >Segnalazione</th>
    			<th data-field="rimosso" data-sortable="false" data-formatter="nameFormatterRem" data-visible="true" >Rimozione</th>            
    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#pc');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    })
	
	function presidiFormatter(value,row) {
        if (value==2){
        		return '<i class="fas fa-play" title="'+row.descrizione_stato+'" style="color:#5cb85c"></i>';
        } else if (value==3) {
        	   return '<i class="fas fa-check" title="'+row.descrizione_stato+'" style="color:#5cb85c" ></i>';
        } else if (value==1){
        	   return '<i class="fas fa-exclamation" title="'+row.descrizione_stato+'" style="color:#ff0000"></i>';
        }

    }
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



function nameFormatterRem(value,row) {
        if (value=='t'){
        		return '<i class="fas fa-times" title="Provvedimento rimosso" style="color:red"></i>';
        } else {
        	   return '-';
		}
        }

function nameFormatterRemtemp(value,row) {
        if (value=='t'){
        		return '<i class="fas fa-times" title="Provvedimento rimosso" style="color:red"></i>';
        } else if (row.id_stato_provvedimenti_cautelari==3) {
        	   return' <button type="button" class="btn btn-danger" data-toggle="modal" title="rimozione PC" data-target="#rimuovi'+row.id+'"><i class="fas fa-times"></i></button> \
    <div class="modal fade" id="rimuovi'+row.id+'" role="dialog"> \
    <div class="modal-dialog"> \
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal">&times;</button>\
          <h4 class="modal-title">Conferma rimozione provvedimento cautelare '+row.id+'</h4>\
        </div>\
        <div class="modal-body">\
        Confermi di voler rimuovere il presente Provvedimento Cautelare? <br> Ricorda che per \
		  rimuovere un provvedimento cautelare è necessaria una nuova ordinanza sindacale.<hr>\
			<form autocomplete="off" action="provvedimenti_cautelari/rimuovi.php?id='+row.id+'" method="POST">\
				<button  id="conferma" type="submit" class="btn btn-primary">Rimuovi provvedimento cautelare</button>\
			</form>\
        </div>\
        <!--div class="modal-footer">\
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
        </div-->\
      </div>\
    </div>\
  </div>\
</div>';
		}
        }



 function nameFormatterEdit(value) {
        
		return '<a class="btn btn-warning" href=./dettagli_provvedimento_cautelare.php?id='+value+'> <i class="fas fa-edit"></i> </a>';
 
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
