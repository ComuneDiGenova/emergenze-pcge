<?php 

$subtitle="Correzione turni sala emergenze";

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

$cf=$_GET["m"];
$id=$cf;



$table=$_GET["t"];
if ($table=='t_coordinamento'){
	$descr='Coordinamento sala emergenze';
} else if ($table=='t_monitoraggio_meteo'){
	$descr='Monitoraggio meteo';
} else if ($table=='t_tecnico_pc'){
	$descr='Tecnico protezione civile';
} else if ($table=='t_presidio_territoriale'){
	$descr='Operatore presidi territoriali meteo';
} else if ($table=='t_operatore_nverde'){
	$descr='Operatore n verde';
} else if ($table=='t_operatore_volontari'){
	$descr='Operatore gestione volontari';
} else if ($table=='t_operatore_anpas'){
	$descr='Operatore presidio sanitario';
}




$data_start=$_GET["s"];

$data_end=$_GET["e"];


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
                 
                    <h1 class="page-header"> <i class="fa fa-user"></i> Correzione turno
                    
                    <?php
                    $check_profilo=0;
					//query per recuperare i dati da correggere in maniera leggibile
                    $query="SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, r.modificato, r.modifica
					FROM report.".$table." r 
					JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola 
					WHERE r.matricola_cf='".$cf."' 
					and r.data_start='".$data_start."' 
					and r.data_end='".$data_end."'
					UNION 
					SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, r.modificato, r.modifica
					FROM report.".$table." r 
					JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf
					WHERE r.matricola_cf='".$cf."' 
					and r.data_start='".$data_start."' 
					and r.data_end='".$data_end."'
					;"; 
                    //echo $query;
                    $result = pg_query($conn, $query);
	                //$rows = array();
	                //echo $result;
	                while($r = pg_fetch_assoc($result)) {
                    		//$rows[] = $r;
                    		echo $r['cognome']. " ".$r['nome'];
                    		$profilo=$r['id_profilo'];
    		        ?>
                    
                    </h1>
                </div>


            
            <br><br>
			
			
            <div class="row">
              <div class="col-lg-12 col-md-auto">

            <h4> <i class="fa fa-address-book" ></i> Informazioni turno <?php echo $descr; ?>
			
            </h4>
            <b>Cognome e nome</b>: <?php echo $r['cognome']. " ".$r['nome']  ?>  <br>
            <b>Matricola / Codice fiscale</b>: <?php echo $r['matricola_cf'] ?>  <br>
            <b>Data inizio turno</b>: <?php echo $r['data_start'] ?>  <br>   
            <b>Data fine turno:</b> <?php echo $r['data_end'] ?> <br>
            <b><?php echo $descr; ?></b>

			<?php
			if ($r['modificato']=='t'){
				echo '<br><br><i class="fas fa-exclamation-circle faa-ring animated" style="color:red"></i> '.$r['modifica'];
			}
			?>
			<br><br>
			
			<?php
			if ($profilo_sistema==1){
			?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_ana"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			} else {
				echo '<br><i>Solo gli amministratori possono modificare il turno.</i><br><br>';
			}
			?>

  



<!-- Modal -->
<div id="modal_ana" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit turno <?php echo $descr; ?></h4>
      </div>
      <div class="modal-body">
      

        <form action="./report/correggi_turni2.php?t=<?php echo $table; ?>&s1=<?php echo $data_start; ?>&e1=<?php echo $data_end; ?>" method="POST">

              <div class="form-group">
                <label for="cognome"> Cognome</label> *
                <input type="text" value='<?php echo $r['cognome']?>' name="cognome" class="form-control" readonly required>
              </div>
              <div class="form-group">
                <label for="nome"> Nome</label> *
                <input type="text" value='<?php echo $r['nome']?>' name="nome" class="form-control" readonly required>
              </div>
              <div class="form-group">
                <label for="cf"> CF</label> *
                <input type="text" pattern=".{16,16}" maxlenght="16" value='<?php echo $r['matricola_cf']?>' name="cf" class="form-control" readonly required>
              </div>           
              <div class="form-group">
                <label for="data_start">Data inizio turno</label>
                <input type="text" name="data_start" value='<?php echo $r['data_start']?>' class="form-control" required>
              </div>            
              <div class="form-group">
                <label for="data_end">Data fine turno</label>
                <input type="text" name="data_end" value='<?php echo $r['data_end']?>' class="form-control" required>
              </div>    
              
            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>

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



<hr>
			</div>
            
            
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->
    

     </div>


<?php 

require('./footer.php');

require('./req_bottom.php');


?>

<script>

(function ($) {
    'use strict';
    
    $('[type="checkbox"').on('change', function () {
        if ($(this).is(':checked')) {
            $('#catName').removeAttr('disabled');
            $('#provincia-list').removeAttr('disabled');
            $('#provincia-list').selectpicker('refresh');
            $('#comune-list').removeAttr('disabled');
            $('#btn_comune').removeAttr('disabled');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
    
}(jQuery));    

</script>  
    

</body>

</html>
