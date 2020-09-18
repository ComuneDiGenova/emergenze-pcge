<?php 

$subtitle="Grafici idrometri";
$idrometro='';
$idrometro=$_GET[i];
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

        <?php 
            require('./navbar_up.php')
        ?>  
        <?php 
            require('./navbar_left.php')
        ?> 
            

        <div id="page-wrapper">
             <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco idrometri ARPA</h1>
                </div>

				<?php 
				require('./grafici_idrometri_arpa.php');
				?>

            </div>
            <!-- /.row -->

            
            <br><br>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco idrometri Comune</h1>
                </div>

				<?php 
				require('./grafici_idrometri_comune.php');
				?>

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
