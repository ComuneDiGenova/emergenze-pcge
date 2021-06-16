<?php 

session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$uo=str_replace("'", "", $_GET["id"]);


// recupero info su U.O. 
$uo_array=explode("_",$uo);
if ($uo_array[0]=='com'){
  $query_uo= "SELECT * FROM varie.incarichi_comune where cod='".$uo_array[1]."';";
} else if ($uo_array[0]=='uo'){
  $query_uo= "SELECT * FROM users.uo_1_livello where id1=".$uo_array[1].";";
};

$result_uo = pg_query($conn, $query_uo);
while($r_uo = pg_fetch_assoc($result_uo)) {
	$uo_descrizione=$r_uo['descrizione'];
}
//echo $query_uo."<br>";

//echo "Descrizione uo:".$uo_descrizione. "<br>";

//echo $uo."<br>";

$subtitle="Specifica contatti mail per Unità Operativa"

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
                    <h1 class="page-header">Contatti mail Unità Operativa: <?php echo $uo_descrizione;?></h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

            <div class="row">
				<h2>Contatti già a sistema</h2>
				<?php
				$check_mail=0; // non ci sono mail diventa 1 se ce ne sono già
				$query="SELECT cod, mail, matricola_cf, id_telegram FROM users.t_mail_incarichi WHERE cod='".$uo."';";
				$result = pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					$check_mail=1;
					echo '<b>Mail</b>: '.$r['mail'].' ';
					//echo '<a class="btn btn-danger" href="./incarichi/elimina_mail.php?cod='.$r['cod'].'&mail='.$r['mail'].'"> Elimina </a><br><br>';
					if($r['id_telegram']!=''){
						echo '- <b>Telegram ID</b>: '.$r['id_telegram'].' ';
						echo '<a class="btn btn-danger" href="./incarichi/elimina_mail.php?cod='.$r['cod'].'&idt='.$r['id_telegram'].'&mail='.$r['mail'].'"> Elimina </a><br><br>';
					}else{
						echo '<a class="btn btn-danger" href="./incarichi/elimina_mail.php?cod='.$r['cod'].'&mail='.$r['mail'].'"> Elimina </a>
						<button type="button" class="btn btn-primary noprint"  data-toggle="modal" data-target="#add_tid"> Aggiungi Telegram ID </button><br><br>
						<!-- Modal sopralluogo-->
						<div id="add_tid" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Aggiungi telegram ID</h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off" action="./incarichi/add_telegramid.php?cod='.$r['cod'].'&mail='.$r['mail'].'"method="POST">
    
									 
									<div class="form-group">
											 <label for="descrizione"> Telegram ID</label> <font color="red">*</font>
										<input type="text" name="idt" class="form-control" required="">
									  </div>            
										  



								<button  id="conferma" type="submit" class="btn btn-primary noprint"  data-toggle="tooltip" data-placement="top" title="Cliccando su questo tasto confermi le informazioni precedenti e assegni l\'id telegram all\'unità operatica">Aggiungi</button>
									</form>

							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default noprint" data-dismiss="modal">Annulla</button>
							  </div>
							</div>

						  </div>
						</div>';

					}
				}
				
				?>

						

				<hr>
				<br>
				<h2>Aggiungi contatto</h2> 
			</div>
            <!-- /.row -->
            <div class="row">
            
					<?php
						if ($uo_array[0]=='uo'){
							?>
							<h4>Aggiungi a partire dalla lista</h4>
							<form action="incarichi/import_mail.php?uo=<?php echo $uo;?>" method="POST">
							
							 <script>
			            function getMail(val) {
			            	//alert(val);
				            $.ajax({
				            type: "POST",
				            url: "get_mail.php",
				            data:'cod='+val,
				            success: function(data){
								console.log(data),
					            $("#mail-input").html(data);
					            //$("#telegramid-input").html(data);
					            //$(this).after('<input id="mail-input" class="form-control" type="text" name="mail-input" />')
				            }
				            });
							$.ajax({
				            type: "POST",
				            url: "get_telegramid.php",
				            data:'cod='+val,
				            success: function(data){
								console.log(data),
					            $("#telegramid-input").html(data);
					            //$("#telegramid-input").html(data);
					            //$(this).after('<input id="mail-input" class="form-control" type="text" name="mail-input" />')
				            }
				            });
			            }
			
			            </script>
			
			

							<div class="col-md-6"> 
			             <div class="form-group  ">
			              <label for="cf">Utente:</label> <font color="red">*</font>
			                            <select name="cf" id="cf" class="selectpicker show-tick form-control" data-live-search="true" onChange="getMail(this.value);" required="">
			                            <option  id="cf" name="cf" value="">Seleziona l'utente</option>
			            <?php            
			            $query2="SELECT * From users.v_utenti_esterni ORDER BY cognome ;";
				        $result2 = pg_query($conn, $query2);
			            //echo $query1;    
			            while($r2 = pg_fetch_assoc($result2)) { 
			                $valore=  $r2['cf']. ";".$r2['nome'];            
			            ?>
			                        
			                    <option id="cf" name="cf" value="<?php echo $r2['cf'];?>" ><?php echo $r2['cognome'].' '.$r2['nome'];?></option>
			             <?php } ?>
			
			             </select>            
			             </div>
			
							</div>
							<!--div class="col-md-4"> 
			
			            <div class="form-group">
			            <label for="cf-input">CF:</label> <font color="red">*</font>
			                <select readonly="" class="form-control" name="cf" id="cf-input" class="demoInputBox" required="">
			                <option value="">CF utente...</option>
			            </select> 
			             </div>
			             
			             </div-->
						<div class="col-md-3"> 
			
			            	<div class="form-group">
								<label for="mail-input">Mail:</label> <font color="red">*</font>
								<select readonly="" class="form-control" name="mail" id="mail-input" class="demoInputBox" required="">
									<option value="">Mail utente..</option>
								</select> 
			             	</div>
	
			             
			            </div>
						<div class="col-md-3"> 
			
							<div class="form-group">
							<label for="telegramid-input">Telegram ID:</label> <font color="red">*</font>
								<select readonly="" class="form-control" name="telegramid" id="telegramid-input" class="demoInputBox" required="">
								<option value="">Telegram ID utente..</option>
							</select> 
							</div>
			             
			            </div>
			             
			             
			             </div>
			            <!-- /.row -->
			            <div class="row">
							<div class="col-md-12"> 
							<button  type="submit" class="btn btn-primary">Aggiungi</button>
          				</div>
							</form>
              </div>
							<?php
						}
					?>
					
					<br>
           <div class="row">
					<h4>Aggiungi nuova mail</h4>
          <form action="incarichi/import_mail.php?uo=<?php echo $uo;?>" method="POST">
            <div class="col-md-12"> 
                <div class="form-group">
			            <label for="mail-input">Mail:</label> <font color="red">*</font>
			                <input class="form-control" type="email" name="mail" id="mail-input"  required="">
			                
			             </div>
            </div>

<div class="col-md-12"> 
							<button  type="submit" class="btn btn-primary">Aggiungi</button>
          				</div>
							</form>

            
            </div>
            <!-- /.row -->
            <hr>
            <div class="row">
            <a class="btn btn-info" href="./lista_mail.php"> Torna alla lista </a><br><br>
            </div>
            
            
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    

</body>

</html>
