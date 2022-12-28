<?php 

if (isset($_POST['LightON'])){
    $Comando0 = 'cd /opt/rh/httpd24/root/var/www/html/aggiornamento_DB';
    $Comando ='/home/local/COMGE/egter01/miniconda3/envs/dbupdate/bin/python /opt/rh/httpd24/root/var/www/html/aggiornamento_DB/postgis_update.py 2>&1';
    //echo $Comando;
    //exit;
    $error = array();
    unset($error);
    $error = shell_exec($Comando0);
    echo $error;
    //exit;
    $error = shell_exec($Comando);
    echo $error;
    //$error = shell_exec("ls");
    //echo "<br><br><br> output error:|".$error."|\n";
}

$subtitle="Log aggiornamenti";
$page = $_SERVER['PHP_SELF'];
$sec = "60";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">   
     <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'" content="IE=edge">
    <!--meta http-equiv="X-UA-Compatible" content="IE=edge"-->
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
                    <h1 class="page-header">Log aggiornamenti geodatabase</h1>
                    <?php
                    $check_update=1;
							$myfile = fopen("/opt/rh/httpd24/root/var/www/html/web.txt", "r") or die("Unable to open file!");
							while ($line = fgets($myfile)) {
								// <... Do your work with the line ...>
								$check_update=0;
  								 echo($line);
							}
							fclose($myfile);
							
							if ($check_update==1){
								echo '<i class="fas fa-sync-alt"></i> Aggiornamento in corso.';
							} else{
							
							
							
							
							if ($profilo_sistema == 1){
							?>
                    
                    <hr> 
                    <form method="post">
							<button class="btn btn-primary" name="LightON"> <i class="fas fa-sync-alt"></i> Aggiornamento manuale</button>
                            <div style="color:red">Attenzione! L'aggiornamento manuale comporta la disattivazione temporanea di Altri Servizi</div>
							</form>
							<?php 
							} else {
								echo '<hr><h4><i class="fas fa-minus-circle"></i> L\'utente non è autorizzato a lanciare l\'update manuale</h4>';
							
							}
							
							} ?>
							<hr>
                    <small><i>Pagina aggiornata automaticamente ogni <?php echo $sec;?> secondi</i></small>
                    
                    
                </div>
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
