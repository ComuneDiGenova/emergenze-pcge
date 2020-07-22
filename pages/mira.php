<?php 

$subtitle="Monitoraggio corsi d'acqua";


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
$id=$_GET["id"];



require('./req.php');

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

require('./check_evento.php');

$query="SELECT concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome FROM geodb.punti_monitoraggio_ok p WHERE id=".$id.";";
//echo $query;
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$name_sensore= $r["nome"];
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
		<!--br><br-->
		
             <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Dati mira sul <?php echo $name_sensore ?> (n. <?php echo $id?>) 
					<button class="btn btn-info noprint" onclick="printClass('fixed-table-container')">
					<i class="fa fa-print" aria-hidden="true"></i> Stampa tabella </button> - 
					<a class="btn btn-info" href="mire.php"> Visualizza tutti i punti </a> </h3>
                </div>
				
				<?php
				//echo strtotime("now");
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s');
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s')-3600;
				//echo "<br><br>";
				
				
				// preparo il menù a tendina
				$query2="SELECT id,descrizione,rgb_hex From geodb.tipo_lettura_mire WHERE valido='t';";
				$result2 = pg_query($conn, $query2);
				$select='';
				while($r2 = pg_fetch_assoc($result2)) {
					$select= $select. '<option name="tipo" value="'. $r2["id"].'">'. $r2["descrizione"].'</option>';
				} 
				////echo "Profilo_ok:". $profilo_ok;
				//echo "<br><br>";
				//echo "Profilo sistema:".$profilo_sistema. " ";

				$now = new DateTime();
				$date = $now->modify('-1 hour')->format('Y-m-d H:i:s');
				//echo $date;
				?>
				
				</div>
				<div class="row">
				<div id="toolbar">
				<select class="form-control">
					<option value="">Esporta i dati visualizzati</option>
					<option value="all">Esporta tutto (lento)</option>
					<option value="selected">Esporta solo selezionati</option>
				</select>
				</div>
				<table  id="t_mira" class="table-hover" data-toggle="table" data-url="./tables/griglia_mira.php?id=<?php echo $id;?>" 
				data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf']
				data-search="true" data-click-to-select="true" 
				data-pagination="true" data-page-size=50 data-page-list=[10,25,50,100,200,500]
				data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
		<th data-field="state" data-checkbox="true"></th>
		<th data-field="data_ora" data-sortable="false"  data-visible="true">Ora lettura</th>
		<th data-field="data_ora_reg" data-sortable="false" data-visible="true">Ora registrazione</th>
		<th data-field="data_ora_mod" data-sortable="false" data-visible="true">Ora aggiornamento</th>
		<th data-field="id_lettura" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">Lettura</th>
		<?php
		if ($profilo_sistema>0 and $profilo_sistema<=2){
		?>
		<th data-field="id" data-sortable="false" data-formatter="nameFormatterInsert" data-visible="true">Edit</th>
		<?php
		}
		?>
    </tr>
</thead>
</table>


<script>
function nameFormatterInsert(value, row) {
	return'<form class="form-inline" autocomplete="off" action="./eventi/edit_lettura.php?id=<?php echo $id; ?>" method="POST">\
			<input id="data" name="data" type="hidden" value="'+row.data_ora+'">\
			<select class="form-control" name="tipo" id="tipo" required="">\
			<?php echo $select; ?> </select> \
			<button  id="conferma" type="submit" class="btn btn-primary">Modifica lettura</button>\
			</form>';
}


function nameFormatterLettura(value) {
	if(value==1){
		return '<i class="fas fa-circle" style="color:#00bb2d;"></i></button>';
	} else if (value==2) {
		return '<i class="fas fa-circle" style="color:#ffff00;"></i></button>';
	} else if (value==3) {
		return '<i class="fas fa-circle" style="color:#cb3234;"></i></button>';
	} else {
		return '-';
	}		
}

</script>
	
	
	



				
				
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

            
            <br><br>
            <div class="row">

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
