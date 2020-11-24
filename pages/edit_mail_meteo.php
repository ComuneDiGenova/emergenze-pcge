<?php 

session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];
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
                    <h1 class="page-header">Modifica  mail</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

            <div class="row">

				<?php
				$check_mail=0; // non ci sono maio diventa 1 se ce ne sono già
				$query="SELECT descrizione, mail FROM users.t_mail_meteo WHERE id=".$id.";";
				$result = pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					
				
				?>

				<form action="report/edit_mail.php?id=<?php echo $id;?>" method="POST">
							
							
				<div class="form-group">
					<label for="aggiornamento">Descrizione</label> <font color="red">*</font>
					<input type="text" class="form-control" id="desc" name="desc" value=<?php echo $r['descrizione'];?> required>
				</div>

				<div class="form-group">
					<label for="aggiornamento">Mail</label> <font color="red">*</font>
					<input type="email" class="form-control" id="mail" name="mail" aria-describedby="emailHelp" value=<?php echo $r['mail'];?> required>
				</div>
			            <!-- /.row -->
			            <div class="row">
							<div class="col-md-12"> 
							<button  type="submit" class="btn btn-primary">Modifica</button>
          				</div>
							</form>
              </div>
					
			<?php
				}
			?>
			
            <hr>
            <div class="row">
            <a class="btn btn-info" href="./lista_mail_meteo.php"> Torna alla lista </a><br><br>
            </div>
            
            
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    

</body>

</html>
