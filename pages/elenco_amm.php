<?php 




$subtitle="Funzionalità amministratore - Editing tabelle decodifiche";


$getfiltri=pg_escape_string($_GET["f"]);
$filtro_evento_attivo=pg_escape_string($_GET["a"]);
$schema=pg_escape_string($_GET["s"]);
$tabella=pg_escape_string($_GET["t"]);




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

if ($profilo_sistema > 1){
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


  


<?php
if($_GET["s"] != '' and $_GET["t"] != ''){

?>

            
           
            <div class="row">

            <h4><i class="fas fa-edit"></i> Editing tabella 
            <i><?php echo $schema;?>.<?php echo $tabella;?></i> - 
            <a href="#c_t" class="btn btn-primary"><i class="fas fa-table"></i> Cambia tabella da editare</a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new_tipo_modal"><i class="fa fa-plus"></i> Aggiungi record a tabella</button>
            
            </h4> 

	
        <!--div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div-->
        

        <table  id="pc" class="table-hover" data-toggle="table" data-url="./tables/griglia_amm.php?s=<?php echo $schema;?>&t=<?php echo $tabella;?>" data-height="auto" data-show-export="false" data-search="true" data-click-to-select="true" data-pagination="false" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">


        
        
<thead>

 	<tr>
	<th data-field="state" data-checkbox="true"></th>
	<?php 
	$query="select * from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike '".$tabella."';";
	//echo $query;
	$result = pg_query($conn, $query);
	#exit;
	while($r = pg_fetch_assoc($result)) {
		if ($r['data_type']=='boolean'){
	?>
		<th data-field="<?php echo $r['column_name'];?>" data-sortable="true" data-formatter="nameFormatterBoolean" data-visible="true" ><?php echo $r['column_name'];?></th>
		<?php } else { ?>
		<th data-field="<?php echo $r['column_name'];?>" data-sortable="true" data-visible="true" ><?php echo $r['column_name'];?></th>
	<?php
		}
	}


	$query="select * from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike'".$tabella."' and
	ordinal_position= (select min(ordinal_position) 
	from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike'".$tabella."'
	);";
	//echo $query;
	$result = pg_query($conn, $query);
	#exit;
	while($r = pg_fetch_assoc($result)) {
		$column_id=$r['column_name'];
	?>
		<th data-field="<?php echo $r['column_name'];?>" data-sortable="true" data-formatter="nameFormatterEdit" data-visible="true" >Edit</th>

	<?php		
	}
	?>	
	
	
	
    </tr>
</thead>

</table>


<?php
	$query0="SELECT * From ".$schema.".".$tabella." order by ".$column_id.";";
	//echo $query;
	$result0 = pg_query($conn, $query0);
	#exit;
	while($r0 = pg_fetch_assoc($result0)) {
	?>
<!-- Modal -->

<div id="modal_<?php echo $r0[$column_id];?>" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit record </h4>
      </div>
      <div class="modal-body">
      
		<div class="row">
        <form action="amministratore/update_tabella.php?s=<?php echo $schema;?>&t=<?php echo $tabella;?>&id=<?php echo $r0[$column_id]?>" method="POST">


			<?php 
			$query="select * from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike '".$tabella."';";
			//echo $query;
			$result = pg_query($conn, $query);
			#exit;
			while($r = pg_fetch_assoc($result)) {
				if ($r['data_type']!='boolean' and $r['column_name']!=$column_id){
			?>
				<div class="form-group col-lg-12">
                <label for="<?php echo $r['column_name']?>"> <?php echo $r['column_name']?></label> <?php
                if($r['is_nullable']=='NO'){
                	echo '*';
                }
                ?>
                <input type="text" value='<?php echo $r0[$r['column_name']]?>' name="<?php echo $r['column_name']?>" class="form-control" 
                <?php
                if($r['is_nullable']=='NO'){
                	echo 'required';
                }
                ?>
                >
				</div>
				<?php } else if ($r['column_name'] == $column_id) { ?>
				<div class="form-group col-lg-12">
                <label for="<?php echo $r['column_name']?>"> <?php echo $column_id ?></label> (chiave primaria) *
                <input type="text" value='<?php echo $r0[$r['column_name']]?>' name="<?php echo $r['column_name']?>" readonly class="form-control" required>
				</div>
				
				<?php } else { ?>
				<div class="form-group col-lg-12">
                <label for="<?php echo $r['column_name']?>"> <?php echo $r['column_name']?> </label> * <br>
				<?php
                if($r0[$r['column_name']]=='t'){
					echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" checked="" value="t"> Vero </label>';
					echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" value="f"> Falso </label>';
				} else {
					echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" value="t"> Vero </label>';
					echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" checked="" value="f"> Falso </label>';
				}
				echo '</div>';
			}
			}
			?>
			


              

			  
              <div class="form-group col-lg-12">
            <button type="submit" class="btn btn-primary">Aggiorna</button>
			</div>
            </form>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   

<?php
	}
?>



<!-- Modal -->
<div id="new_tipo_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Aggiunta record </h4>
      </div>
      <div class="modal-body">
      
		<div class="row">
        <form action="amministratore/new_tipo_tabella.php?s=<?php echo $schema;?>&t=<?php echo $tabella;?>&id='<?php echo $r0['id']?>'" method="POST">


			<?php 
			$query="select * from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike '".$tabella."';";
			//echo $query;
			$result = pg_query($conn, $query);
			#exit;
			while($r = pg_fetch_assoc($result)) {
				if ($r['data_type']!='boolean' and $r['column_name']!='id'){
			?>
				<div class="form-group col-lg-12">
                <label for="<?php echo $r['column_name']?>"> <?php echo $r['column_name']?></label> *
                <input type="text" name="<?php echo $r['column_name']?>" class="form-control" required>
				</div>
				<?php } else if ($r['column_name'] =='id') { ?>
				<!--div class="form-group col-lg-12">
                <label for="<?php echo $r['column_name']?>"> <?php echo $r[$column_id]?></label> *
                <input type="text" value='<?php echo $r0[$r['column_name']]?>' name="<?php echo $r['column_name']?>" readonly class="form-control" required>
				</div-->
				
				<?php } else { ?>
				<div class="form-group col-lg-12">
                <label for="<?php echo $r['column_name']?>"> <?php echo $r['column_name']?> </label> * <br>
				<?php
                //if($r0[$r['column_name']]=='t'){
					echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" checked="" value="t"> Vero </label>';
					echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" value="f"> Falso </label>';
				//} else {
				//	echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" value="t"> Vero </label>';
				//	echo '<label class="radio-inline"><input type="radio" name="'.$r['column_name'].'" checked="" value="f"> Falso </label>';
				//}
				echo '</div>';
			}
			}
			?>
			


              

			  
              <div class="form-group col-lg-12">
            <button type="submit" class="btn btn-primary">Aggiungi record</button>
			</div>
            </form>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   



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
        
		//return '<a class="btn btn-warning" href=./dettagli_amm.php?s=<?php echo $schema;?>&t=<?php echo $tabella;?>&id='+value+'> <i class="fas fa-edit"></i> </a>';
		return '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_'+value+'"><i class="fa fa-pencil-alt"></i></button>';
    }



  function nameFormatterBoolean(value) {
        //return '<i class="fas fa-'+ value +'"></i>' ;
        
        if (value=='t'){
        		return '<i class="fas fa-check" style="color:#5cb85c"></i>';
        } else if (value=='f') {
        	   return '<i class="fas fa-times" style="color:#ff0000"></i>';
        }
        else {
        		return ' - ';
        }
    }


</script>





            </div>
            <!-- /.row -->
            
            
<?php
} 

?>            



<hr>

<section id="c_t">
            <div class="row">

            <h4> <i class="fas fa-pencil-ruler"></i> Scelta tabella da editare</h4> 

<form action="amministratore/scelta_tabella.php" method="POST">

             <div class="form-group col-md-12">
             <label for="id_civico">Seleziona tabella da editare:</label> <font color="red">*</font>
					<select class="form-control" name="table" id="table-list" class="demoInputBox" required="">
					<option  id="table" name="table" value="">Seleziona la tabella</option>
					<?php
					
					$query2="select * from information_schema.tables where table_name ilike 'tipo%' OR 
					table_name ilike 'uo_1_livello' OR table_name ilike 'uo_2_livello' OR table_name ilike 'soglie%' OR table_name ilike 'tipo_funzione_coc'
					order by table_schema,table_name ";
					$result2 = pg_query($conn, $query2);
					 
					while($r2 = pg_fetch_assoc($result2)) { 
						$valore=  $r2['cf']. ";".$r2['nome'];            
					?>
								
							<option id="table" name="table" value="<?php echo $r2['table_schema'];?>.<?php echo $r2['table_name'];?>" ><?php echo $r2['table_schema'];?>.<?php echo $r2['table_name'];?></option>
					 <?php } ?>
				</select>
				<small> L'elenco delle tabelle da editare e definito dall'applicativo. In caso di problemi contatta il fornitore</a>. </small>
             </div>
             
             
             
      
             
             


				</div> 


				
				
            <!--div class="row"-->

            <div class="row">

					   


            <button  type="submit" class="btn btn-primary">Edita tabella</button>
            </div>
            <!-- /.row -->
            

            </form> 


</section>

            
            
    </div>
    <!-- /#wrapper -->



<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    
</body>



</html>
