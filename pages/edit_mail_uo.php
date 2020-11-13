<?php 

session_start();

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

        <?php 
            require('./navbar_up.php')
        ?>  
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
				<h2>Mail già a sistema</h2>
				<?php
				$check_mail=0; // non ci sono maio diventa 1 se ce ne sono già
				$query="SELECT cod, mail, matricola_cf FROM users.t_mail_incarichi WHERE cod='".$uo."';";
				$result = pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					$check_mail=1;
					echo '<b>Mail</b>:'.$r['mail'].' ';
					echo '<a class="btn btn-danger" href="./incarichi/elimina_mail.php?cod='.$r['cod'].'&mail='.$r['mail'].'"> Elimina </a><br><br>';
				}
				
				?>

				<hr>
				<br>
				<h2>Aggiungi mail</h2> 
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
					            $("#mail-input").html(data);
					            //$("#cf-input").html(data);
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
							<div class="col-md-6"> 
			
			            <div class="form-group">
			            <label for="mail-input">Mail:</label> <font color="red">*</font>
			                <select readonly="" class="form-control" name="mail" id="mail-input" class="demoInputBox" required="">
			                <option value="">Mail utente..</option>
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
