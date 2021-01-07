<?php 

$subtitle="Dettagli profilo utente"

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

require('./token_telegram.php')
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
                    <h1 class="page-header">Dettagli profilo <?php echo $CF; ?>;</h1>
                </div>
				<?php 
				//echo "profilo_sistema=".$profilo_sistema;
				//echo basename($_SERVER['PHP_SELF']);
					echo "<b>Nome</b>: ". $nome;
					echo "<br><b>Cognome</b>: ". $cognome;
					echo "<br><b>Livello 1</b>: ".$livello1;
					echo "<br><b>Livello 2</b>: ".$livello2;
					echo "<br><b>Livello 3</b>: ".$livello3;
					echo "<br><b>Provacy</b>: ".$provacy;	
					if ($matricola!=''){
						echo "<br><b>Matricola</b>: ". $matricola;
					}
					echo "<br><b>Profilo</b>: ". $descrizione_profilo;
					if ($profilo_nome_munic!=''){
						echo "<br><b>Municipio</b>: ". $profilo_nome_munic;
					}
					echo "<br><b>Squadra</b>: ". $nome_squadra_operatore;
					if($profilo_sistema==8){
						echo "<br><b>Unità operativa esterna</b>: ". $uo_inc;
					} else {
						echo "<br><b>Unità operativa interna</b>: ". $periferico_inc;
					}
					echo "<br><hr>";
					if ($check_esterno ==1){
						echo "<br><a class=\"btn btn-primary btn-sm\" href=\"update_volontario.php?id=".$CF."\" > <i class=\"fa fa-pencil-alt\"></i> Aggiorna dati anagrafici</a>";
					}
					
					?>
					<hr>
					<h2> <i class="fab fa-telegram"  style="color:#0088CC" ></i> Notifiche telegram (servizio sperimentale) 
					<i class="fab fa-telegram"  style="color:#0088CC"></i></h2>
					<h4>Il servizio funziona tramite il bot telegram chiamato <b>@<?php echo $bot_name; ?></b>. 
					Per info sui bot telegram <a href="https://telegram.org/faq/it#bot" target="_blank">clicca qua</a>. </h4>
					<?php
					$query = "SELECT telegram_id, telegram_attivo from users.v_utenti_sistema 
					where matricola_cf='".$operatore."';";
					$result = pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						$telegram_id=$r['telegram_id'];
						$telegram_attivo=$r['telegram_attivo'];
					}
					
					?>
					<hr>
					<form class="form-inline" action="./update_chatid.php?cf=<?php echo $operatore;?>" method="POST">
						<div class="form-group">
							<label for="chatid">Id telegram</label>
							<input type="chatid" class="form-control-plaintext" name="chatid" value="<?php echo $telegram_id; ?>" >
						</div>
						<button  type="submit" class="btn btn-primary btn-sm">Edit</button>
					</form>
					Per recuperare l'id telegram:
					<ul>
					<li> Se necessario, scaricare e installare l'applicazione telegram </li>
					<li> Aggiungere il bot chiamato <b>@<?php echo $bot_name; ?></b>. 
					E' sufficiente cercarlo come fosse un proprio contatto, selezionarlo e cliccare su avvia.</li>
					<li> Sul bot usare il comando <b>/telegram_id</b> che fornisce il proprio id personale</li>
					</ul>
					<hr>
					<?php
					//echo $telegram_attivo;
					if($telegram_id!='') {
					if($telegram_attivo=='f') {
					?>
						<i class="fas fa-times faa-ring animated" style="color:#ff0000"></i> Notifiche disattivate
						<a class="btn btn-success btn-sm" href="attiva_notifiche.php?cf=<?php echo $operatore; ?>" >
						<i class=\"fa fa-check\"></i> Attiva notifiche</a>
					<?php
					} else {
					?>
						<i class="fas fa-check faa-ring animated" style="color:#007c37"></i> Notifiche attivate
						<a class="btn btn-danger btn-sm" href="disattiva_notifiche.php?cf=<?php echo $operatore; ?>" >
						<i class=\"fa fa-times\"></i> Disattiva notifiche</a>
					<?php
					} 
					}
					?>
				
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
