<?php 

$subtitle="Elenco comunicazioni riservate";

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
if(isset($_GET["r"])){
	$resp=$_GET["r"];
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

if ($profilo_sistema > 2){
	header("location: ./divieto_accesso.php");
}

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
        
		
		


		<div class="collapse" id="collapsecriticita">
          <div class="card card-body">
         		  
          <form id="filtro_cr" action="./tables/decodifica_filtro0.php?r=<?php echo $resp;?>&a=<?php echo $filtro_evento_attivo?>&from=<?php echo $filtro_from?>&to=<?php echo $filtro_to?>&m=<?php echo $filtro_municipio?>" method="post">
            <input type="hidden" name="pagina" id="hiddenField" value="<?php echo $pagina; ?>" />
			
			<?php
            $query='SELECT * FROM segnalazioni.tipo_criticita where valido=\'t\' ORDER BY descrizione;';
            $result = pg_query($conn, $query);
	         #echo $result;
	         //exit;
	         //$rows = array();
            //echo '<div class="form-check form-check-inline">';
            echo '<div class="row">';
	         while($r = pg_fetch_assoc($result)) {
					echo '<div class="form-check col-md-3">';
	            echo '  <input class="form-check-input" type="checkbox" id="filtro_cr" name="filter'.$r['id'].'"  value=1" >';
	            echo '  <label class="form-check-label" for="inlineCheckbox1">'.$r['descrizione'].'</label>';
	            echo "</div>";
	            
            }
            echo "</div>";

        ?>
        <!--hr-->
		
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
		
		<div class="collapse" id="collapsemunicipio">
          <div class="card card-body">
         		  
          <form id="filtro_mun" action="./tables/decodifica_filtro1.php?r=<?php echo $resp;?>&a=<?php echo $filtro_evento_attivo?>&from=<?php echo $filtro_from?>&to=<?php echo $filtro_to?>&f=<?php echo $getfiltri?>" method="post">
            <input type="hidden" name="pagina" id="hiddenField" value="<?php echo $pagina; ?>" />
			<?php
            $query='SELECT * FROM geodb.municipi ORDER BY codice_mun;';
            $result = pg_query($conn, $query);
	         #echo $result;
	         //exit;
	         //$rows = array();
            //echo '<div class="form-check form-check-inline">';
            echo '<div class="row">';
	         while($r = pg_fetch_assoc($result)) {
					echo '<div class="form-check col-md-3">';
	            echo '  <input class="form-check-input" type="checkbox" id="filtro_mun" name="filter'.$r['codice_mun'].'"  value=1" >';
	            echo '  <label class="form-check-label" for="inlineCheckbox1">'.$r['codice_mun'].' - '.$r['nome_munic'].'</label>';
	            echo "</div>";
	            
            }
            echo "</div>";

        ?>
        <!--hr-->
		
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

      	
        <table  id="segnalazioni" class="table-hover" data-toggle="table" data-filter-control="true" 
  data-show-search-clear-button="true" 
		data-url="./tables/griglia_comunicazioni_riservate.php?r=<?php echo $resp;?>&f=<?php echo $getfiltri;?>&from=<?php echo $filtro_from; ?>&to=<?php echo $filtro_to;?>&m=<?php echo $filtro_municipio;?>" 
		data-show-export="false" data-search="true" data-click-to-select="true" 
        data-pagination="true" data-page-size=50 data-page-list=[10,25,50,100,200,500] 
		data-sidePagination="true" data-show-refresh="true" data-show-toggle="true" 
        data-show-columns="true" data-toolbar="#toolbar">
      	

        
        
<thead>

 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
            <!--th data-field="tipo" data-sortable="true" data-visible="true" data-filter-control="select">Tipo</th-->
            <th data-field="testo" data-sortable="true" data-visible="true" data-filter-control="input">Testo</th> 
            <th data-field="allegato" data-sortable="true" data-formatter="nameFormatterRischio" data-visible="true"data-filter-control="select">Allegato</th>
            <th data-field="data_ora_stato" data-sortable="true"   data-visible="true" data-filter-control="input">Data</th>
            <th data-field="id" data-sortable="true" data-formatter="nameFormatterDettagli" data-visible="true">Dettagli</th>
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
	return' <a class="btn btn-info" target="_new" href="./dettagli_'+row.tipo+'.php?id='+value+'"><i class="fas fa-comments" title="Visualizza dettagli comunicazioni '+row.tipo+' in nuova scheda"></i></a>';
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
    	var $table = $('#segnalazioni');
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
