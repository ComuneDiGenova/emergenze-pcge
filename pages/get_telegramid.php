<?php
session_start();
//require('../validate_input.php');;


$cf= $_POST["cod"];
echo $cf;


include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!empty($cf)) {
	$query = "select telegram_id from users.v_utenti_sistema WHERE matricola_cf='".$cf."';";
    //$query = "select mail, telegram_id from users.v_utenti_esterni, users.v_utenti_sistema vus WHERE cf='".$cf."' and matricola_cf='".$cf."';";
    
    #echo $query;
    //exit;
    $result = pg_query($conn, $query);

     while($r = pg_fetch_assoc($result)) { 
    ?>
			<!--option id="mail-input" value="<?php echo $r['mail'];?>" ><?php echo $r['mail'];?></option-->
            <option id="telegramid-input" value="<?php echo $r['telegram_id'];?>" ><?php echo $r['telegram_id'];?></option>
    <?php
    }

	
}
?>