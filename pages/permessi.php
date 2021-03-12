<?php 

$check_cf=0;
$check_matr=0;

$id=pg_escape_string($_GET["id"]);

if(strlen($id)==16) {
	$check_cf=1;
} else {
	$check_matr=1;
}

if($check_cf==1) {
	$subtitle="Gestione permessi utente CF ".str_replace("'", "", $id);
} else {
	$subtitle="Gestione permessi matricola ".str_replace("'", "", $id);
}

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
                    <h1 class="page-header">Gestione permessi utente </h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

            
            <br><br>
            <div class="row">
            
            <?php
			
			
            	$check_profilo=0;
            	$query="SELECT * From varie.v_dipendenti where matricola='".$id."';"; 
               $result = pg_query($conn, $query);
	            while($r = pg_fetch_assoc($result)) {
	            	echo '<div class="col-6 col-sm-6">';
	            	echo '<h4><i class="fa fa-address-book"></i> Anagrafica dipendente</h4>';
	            	echo "<b>Matricola</b>:".$r['matricola']. "<br>";
               	echo "<b>Cognome</b>:".$r['cognome']. "<br>";
               	echo "<b>Nome</b>:".$r['nome']. "<br>";
               	echo '</div>';
               	echo '<div class="col-6 col-sm-6">';
               	echo '<h4><i class="fas fa-project-diagram"></i> Inquadramento</h4>';
	            	echo "<b>Direzione (area)</b>:".$r['direzione_area']. "<br>";
               	echo "<b>Settore</b>:".$r['settore']. "<br>";
               	echo "<b>Ufficio</b>:".$r['ufficio']. "<br>";
               	echo '</div>';
               	$profilo=$r['id_profilo'];
               }
               if ($profilo!=''){
               	$check_profilo=1;
               }
               
            ?>

            </div>
            <hr>
            <?php
			require('./section_permessi.php')
			?>
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    

</body>

</html>
