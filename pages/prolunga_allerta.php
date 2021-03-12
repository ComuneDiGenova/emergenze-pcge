<?php 

$subtitle="Prolunga allerta "

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
            <br>
                

<?php

session_start();
//require('../validate_input.php');;


$id_evento=$_GET["e"];
$id_allerta=$_GET["a"];
$time=$_GET["t"];



$id=str_replace("'", "", $id);

$query="SELECT * FROM eventi.v_allerte where id_evento=".$id_evento." AND  id_tipo_allerta=".$id_allerta." AND data_ora_inizio_allerta=".$time.";";
//echo $query;
//exit;
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {

	$timestamp = strtotime($r["data_ora_inizio_allerta"]);
	setlocale(LC_TIME, 'it_IT.UTF8');
	$data_start = strftime('%A %e %B %G', $timestamp);
	$data_start_edit = strftime('%Y-%m-%d', $timestamp);
	$h_start_edit = date('H', $timestamp);
	$m_start_edit = date('i', $timestamp);
	$ora_start = date('H:i', $timestamp);
	$timestamp = strtotime($r["data_ora_fine_allerta"]);
	$data_end = strftime('%A %e %B %G', $timestamp);
	$data_end_edit = strftime('%Y-%m-%d', $timestamp);
	$h_end_edit = date('H', $timestamp);
	$m_end_edit = date('i', $timestamp);
	$ora_end = date('H:i', $timestamp);								
	$color=str_replace("'","",$r["rgb_hex"]);								

	echo "<h3><i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"\"></i> <b>Allerta ".$r["descrizione"]."</b></h3>";
	echo " <h4>Attualmente prevista dalle ".$ora_start." di ".$data_start." alle ore <b>" .$ora_end ." di ".$data_end. " </b></h4>";
}
?>
<h3>Prolunga fino a:</h3>
<form action="eventi/prolunga_allerta2.php?e=<?php echo $id_evento?>&a=<?php echo $id_allerta?>&t=<?php echo $time?>" method="POST">
<div class="form-group">
		<label for="data_fine" >Data fine allerta (AAAA-MM-GG) </label>                 
		<input type="text" class="form-control" name="data_fine" id="js-date"value="<?php echo $data_end_edit; ?>" required>
		<div class="input-group-addon">
			<span class="glyphicon glyphicon-th"></span>
		</div>
	</div> 
	
	<div class="form-group"-->

    <label for="ora_inizio"> Ora fine:</label> <font color="red">*</font>

  <div class="form-row">


 				<div class="form-group col-md-6">
      <select class="form-control"  name="hh_end" required>
      <option name="hh_end" value="<?php echo $h_end_edit; ?>" > <?php echo $h_end_edit; ?> </option>
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
      <option name="mm_end" value="<?php echo $m_end_edit; ?>" ><?php echo $m_end_edit; ?> </option>
        <?php 
          $start_date = 0;
          $end_date   = 59;
          $incremento = 59;
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
     
      



  <button  id="conferma" type="submit" class="btn btn-info">Aggiorna allerta</button>
  <a class="btn btn-info" href="./dettagli_evento.php" > Annulla </a>
</form>


<?php
//$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Rimuovi allerta associata a evento".$id_evento."');";
//$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
//echo "<br>";
//echo $query_log;

//exit;
//header("location: ../dettagli_evento.php");


?>
  </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


<script>

(function ($) {
    'use strict';
    
    $('[type="checkbox"').on('change', function () {
        if ($(this).is(':checked')) {
            $('#conferma').removeAttr('disabled');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
    
}(jQuery));    
    
    

$(document).ready(function() {
    $('#js-date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
   
    
});


             

</script>
    
    

</body>

</html>