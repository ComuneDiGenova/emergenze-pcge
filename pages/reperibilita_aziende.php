<?php 

$subtitle="Pagina reperibilità"

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

                 
	   					
		<div class="row">
			 <div class="col-lg-12">
                    <h1 class="page-header"> <i class="fas fa-user-clock"></i> Reperibilità </h1>
                </div>
		</div>
		<div class="row">
		<div class="col-md-8">
		<h2>Reperibili </h2>
		
		
		<?php
		$query0 = "SELECT id1, descrizione FROM users.uo_1_livello where id1 > 1 and invio_incarichi='t'";
		if ($profilo_sistema == 8){
			$query0 = $query0. " and id1=".$id_livello1."";
		}
		//echo $query0;
		$result0 = pg_query($conn, $query0);
		while($r0 = pg_fetch_assoc($result0)) {
		
			echo "<hr><h3>". $r0['descrizione']."</h3>";
		
		
			//reperibili in corso
			echo '<h4><i class="fas fa-hourglass-half fa-2x"></i> Reperibili attuali</h4>';
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_end, u.livello1, u.telefono1 from users.t_reperibili r ";
			$query = $query. "JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
			$query = $query. "where data_start < now() and data_end > now() ";
			$query = $query. " and id1=".$r0["id1"]."";
			$query = $query. " order by livello1, cognome;";
			
			//echo $query;
			
			$check_reperibile=0;
			$result = pg_query($conn, $query);
			echo "<ul>";
			while($r = pg_fetch_assoc($result)) { 
				$check_reperibile=1;
				echo "<li>";
				echo  $r['livello1']. " - ".$r['cognome']." ";
				echo  $r['nome']. " - Fino alle ".$r['data_end'];
				echo  "- <i class=\"fas fa-phone\"></i> ".$r['telefono1'];
				
				echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '<li> <i class="fas fa-circle fa-2x" style="color: red;"></i> In questo momento non ci sono reperibili</li>';
			}
			
			echo "</ul>";
			
			
			
			//reperibili fututi 
			echo '<h4><i class="fas fa-hourglass-start fa-2x"></i> Reperibili futuri</h4>';
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, u.livello1, u.telefono1 from users.t_reperibili r ";
			$query = $query. "JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
			$query = $query. "where data_start > now()";
			$query = $query. " and id1=".$r0["id1"]."";
			$query = $query. " order by livello1, data_start, cognome;";
			
			//echo $query;
			
			$check_reperibile=0;
			$result = pg_query($conn, $query);
			echo "<ul>";
			while($r = pg_fetch_assoc($result)) { 
				$check_reperibile=1;
				echo "<li>";
				echo  $r['cognome']." ";
				echo  $r['nome']. " - Dalle ".$r['data_start']." alle ".$r['data_end'];
				echo  " - <i class=\"fas fa-phone\"></i> ".$r['telefono1'];
				echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '<li> <i class="fas fa-circle fa-2x" style="color: yellow;"></i> In questo momento non ci sono reperibili</li>';
			}
			
			echo "</ul>";
		}
		
		?>


							
		</div>
		
		
		<div class="col-lg-4">
		<div style="text-align: center;">
		<?php
		if ($profilo_sistema == 8){
		?>	
		<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_reperibilita">
		<i class="fas fa-plus"></i> Aggiungi reperibilità personale <?php echo $livello1?> </button>
		<?php
		} //else if($profilo_sistema <3) {
		?>
		<!--button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_reperibilita">
		<i class="fas fa-plus"></i> Aggiungi reperibilità COC esterni </button-->
		<?php
		//}
		?>
		</div>
			
							

		
							

		</div> <!-- Chiudo la colonna larga 6 -->
					




<!-- Modal reperibilità-->
<div id="new_reperibilita" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Inserire reperibilità <?php echo $livello1?></h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="reperibilita/nuova_rep.php" method="POST">
		
		<?php
		if (substr($cod_profilo_squadra,0,2)=='uo' OR (int)substr($cod_profilo_squadra,-1,1)>1){
					
			$query2="SELECT * FROM users.v_utenti_esterni v 
			WHERE id1=".(int)substr($cod_profilo_squadra,-1)."
			ORDER BY cognome";
		}
		$result2 = pg_query($conn, $query2);
		//echo $query2;
		?>
		
			 <div class="form-group  ">
				  <label for="cf">Seleziona la persona della tua azienda reperibile:</label> <font color="red">*</font>
								<select name="cf" id="cf" class="selectpicker show-tick form-control" data-live-search="true" required="">
								<option  id="cf" name="cf" value="">Seleziona personale</option>
				<?php    
				while($r2 = pg_fetch_assoc($result2)) { 
					$valore=  $r2['cf']. ";".$r2['nome'];            
				?>
							
						<option id="cf" name="cf" value="<?php echo $r2['cf'];?>" ><?php echo $r2['cognome'].' '.$r2['nome'].' ('.$r2['livello1'].')';?></option>
				 <?php } ?>

				 </select>            
				 
				 </div>
			
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio reperibilità (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date" required>
						<!--div class="input-group-addon" id="js-date" >
							<span class="glyphicon glyphicon-th"></span>
						</div-->
					</div> 
					
					<div class="form-group"-->

                <label for="ora_inizio"> Ora inizio:</label> <font color="red">*</font>

              <div class="form-row">
   
   
    				<div class="form-group col-md-6">
                  <select class="form-control"  name="hh_start" required>
                  <option name="hh_start" value="" > Ora </option>
                    <?php 
                      $start_date = 0;
                      $end_date   = 24;
                      for( $j=$start_date; $j<=$end_date; $j++ ) {
                      	if($j<10) {
                        	echo '<option value="0'.$j.'">0'.$j.'</option>';
                        } else {
                        	echo '<option value="'.$j.'">'.$j.'</option>';
                        }
                      }
                    ?>
                  </select>
                  </div>	
                  
      				<div class="form-group col-md-6">
                  <select class="form-control"  name="mm_start" required>
                  <option name="mm_start" value="00" > 00 </option>
                    <?php 
                      $start_date = 0;
                      $end_date   = 59;
                      $incremento = 15; 
                      for( $j=$start_date; $j<=$end_date; $j+=$incremento) {
                      	if($j<10) {
                        	echo '<option value="0'.$j.'">0'.$j.'</option>';
                        } else {
                        	echo '<option value="'.$j.'">'.$j.'</option>';
                        }
                      }
                    ?>
                  </select>
                  </div>                
                  
                </div>  
                </div>
					
					
					<div class="form-group">
						<label for="data_fine" >Data fine reperibilità (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_fine" id="js-date2" required>
						<!--div class="input-group-addon">
							<span class="glyphicon glyphicon-th"></span>
						</div-->
					</div> 
					
					<div class="form-group"-->

                <label for="ora_inizio"> Ora fine:</label> <font color="red">*</font>

              <div class="form-row">
   
   
    				<div class="form-group col-md-6">
                  <select class="form-control"  name="hh_end" required>
                  <option name="hh_end" value="" > Ora </option>
                    <?php 
                      $start_date = 0;
                      $end_date   = 24;
                      for( $j=$start_date; $j<=$end_date; $j++ ) {
                      	if($j<10) {
                        	echo '<option value="0'.$j.'">0'.$j.'</option>';
                        } else {
                        	echo '<option value="'.$j.'">'.$j.'</option>';
                        }
                      }
                    ?>
                  </select>
                  </div>	
                  
      				<div class="form-group col-md-6">
                  <select class="form-control"  name="mm_end" required>
                  <option name="mm_end" value="00" > 00 </option>
                    <?php 
                      $start_date = 59;
                      $end_date   = 59;
                      $incremento = 15;
                      for( $j=$start_date; $j<=$end_date; $j+=$incremento ) {
                      	if($j<10) {
                        	echo '<option value="0'.$j.'">0'.$j.'</option>';
                        } else {
                        	echo '<option value="'.$j.'">'.$j.'</option>';
                        }
                      }
                    ?>
                  </select>
                  </div>                
                  
                </div>  
                </div>
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci reperibilità</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   



  

<?php
echo "</div>";


?>


                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

            <!-- /.row -->
    
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>



   
<script type="text/javascript" >

   
   (function ($) {
    'use strict';

<?php
if ($check_evento==1){
$len=count($eventi_attivi);	               
for ($i=0;$i<$len;$i++){
?>   
 
    $('[type="checkbox"][id="cat"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#conferma').removeAttr('disabled');
            return true;
        }
        
    });
    
    $('[type="checkbox"][id="cat"]').on('change', function () {
        if (!$(this).is(':checked')) {
            $('#conferma').attr('disabled', true);
            return true;
        }
        
    });    

    
  
$(document).ready(function() {
    $('#js-date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date2').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
      $('#js-date3').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date4').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    
});

<?php }} ?>

}(jQuery));  
     
 </script>   

</body>

</html>
