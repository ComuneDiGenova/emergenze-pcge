<?php 

$subtitle="Elenco storico mail inviate ";

if(isset($_GET["f"])){
	$getfiltri=$_GET["f"];
}
if(isset($_GET["a"])){
	$filtro_evento_attivo=$_GET["a"];
}
if(isset($_GET["m"])){
	$filtro_municipio=$_GET["m"];
}
if(isset($_GET["from"])){
	$filtro_from=$_GET["from"];
}
if(isset($_GET["to"])){
	$filtro_to=$_GET["to"];
}
/*if(isset($_GET["r"])){
	$resp=$_GET["r"];
}*/

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
            <?php
            if ($profilo_sistema == 8){
			    //echo $id_livello1."<br>";
                $resp='uo_'.$id_livello1;
                $query0 = "SELECT id1, descrizione FROM users.uo_1_livello where id1 > 1 and invio_incarichi='t'";
                $query0 = $query0. " and id1=$1";
                
                //echo $query0;
                $result0 = pg_prepare($conn,"myquery0", $query0);
                $result0 = pg_execute($conn,"myquery0", array($id_livello1));
                while($r0 = pg_fetch_assoc($result0)) {
                    echo "<h3> Si visualizzano solo le mail inviate da ". $r0['descrizione']."</h3>";
                }
            }

            ?>
<p>
            <!--a class="btn btn-primary" data-toggle="collapse" href="#collapsecriticita" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="fas fa-filter"></i>  Filtra per criticità
          </a>
		  
		  <a class="btn btn-primary" data-toggle="collapse" href="#collapsemunicipio" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="fas fa-home"></i>  Filtra per municipio
          </a-->
		  
		  <a class="btn btn-primary" data-toggle="collapse" href="#collapsedata" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="fas fa-hourglass"></i>  Filtra per data
          </a>
			
        </p>
        
		
		


		
		
		<div class="collapse" id="collapsedata">
          <div class="card card-body">
         		  
          <form id="filtro_data" action="./tables/decodifica_filtro2.php?r=<?php echo $resp;?>&a=<?php echo $filtro_evento_attivo?>&m=<?php echo $filtro_municipio?>&f=<?php echo $getfiltri?>" method="post">
            <input type="hidden" name="pagina" id="hiddenField" value="<?php echo $pagina; ?>" />
			
				<div class="form-check col-md-6">
				<label for="startdate">Da (AAAA/MM/GG HH:MM):</label>
				<input type="text" required="" class="form-control" id="startdate" name="startdate" value=<?php echo str_replace("'", "", $filtro_from)?>>
				<small id="sdateHelp" class="form-text text-muted"> Inserire la data e l'ora (opzionale)</small>
				</div>
				
				
				<div class="form-check col-md-6">
				<label for="todate">A (AAAA/MM/GG HH:MM):</label>
				<input type="text" required="" class="form-control" id="todate" name="todate" value=<?php echo str_replace("'", "", $filtro_to)?>>
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
			<?php
			if ($filtro_from!='' or $filtro_to!=''){
			?>
			<br><br>
			<a class="btn btn-primary" href="<?php echo $pagina; ?>">
            <i class="fas fa-redo-alt"></i> Rimuovi filtro data
          </a>
          <hr>
			<?php			
			} else {
				echo ' <i class="fas fa-list-ul"></i> Dati completi';
			}
			
			?>

			
        <!--div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div-->
        <div style="overflow-x:auto;">

      	
        <table  id="mail" class="table-hover" data-toggle="table" data-filter-control="true" 
  data-show-search-clear-button="true" 
		data-url="./tables/griglia_storico_mail.php?r=<?php echo $resp;?>&f=<?php echo $getfiltri;?>&from=<?php echo $filtro_from; ?>&to=<?php echo $filtro_to;?>&m=<?php echo $filtro_municipio;?>" 
		 data-show-export="false" data-search="true" data-click-to-select="true" data-pagination="true" data-page-size=50 data-page-list=[10,25,50,100,200,500] 
		data-sidePagination="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-toolbar="#toolbar">
      	

        
        
<thead>

 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
            <!--th data-field="tipo" data-sortable="true" data-visible="true" data-filter-control="select">Tipo</th-->
            <th data-field="mittente" data-sortable="true" data-visible="true" data-filter-control="input">Mittente</th> 
            <th data-field="destinatario" data-sortable="true" data-visible="true" data-filter-control="input">Destinatario</th> 
            <th data-field="testo_aggiuntivo" data-sortable="true" data-visible="true" data-filter-control="input">Testo aggiuntivo</th> 
            <th data-field="data_ora" data-sortable="true"   data-visible="true" data-filter-control="input">Data</th>
            <th data-field="id_incarico" data-sortable="true" data-formatter="nameFormatterDettagli" data-visible="true">Incarico</th>
    </tr>
</thead>

</table>
</div>



<br><br>

<script>



  function nameFormatterRischio(value) {
        //return '<i class="fas fa-'+ value +'"></i>' ;
        
        if (value=='y'){
        		return '<i class="fas fa-paperclip" style="color:#000000"></i>';
        } else {
        		return '-';
        }
    }


function nameFormatterDettagli(value, row) {
	//var test_id= row.id;
	return' <a class="btn btn-info" target="_new" href="./dettagli_incarico.php?id='+value+'"><i class="fas fa-comments" title="Visualizza dettagli comunicazioni '+row.tipo+' in nuova scheda"></i></a>';
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

<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    $(function () {
    	var $table = $('#mail');
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    });
	
	
	

$(document).ready(function(){
    $("form[id=filtro_cr]").submit(function(){
		if ($('input[type=checkbox][id=filtro_cr]').filter(':checked').length < 1){
        alert("Seleziona almeno una criticità!");
		return false;
		}
    });
});

$(document).ready(function(){
    $("form[id=filtro_mun]").submit(function(){
		if ($('input[type=checkbox][id=filtro_mun]').filter(':checked').length < 1){
        alert("Seleziona almeno un municipio!");
		return false;
		}
    });
});
</script>

</html>
