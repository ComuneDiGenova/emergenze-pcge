<?php
session_start();
//echo"OK";
include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';
if(!empty($_POST["cod"])) {
    $query = "SELECT * FROM users.\"uo_2_livello\" where id1=".$_POST["cod"]." order by descrizione;";
    //echo $query;
    $result = pg_query($conn, $query);
	$check_option=0;
     while($r = pg_fetch_assoc($result)) { 
    	//$check_option=1;
    ?>
		 
        <option name="UO_II" value="<?php echo $r['id1'];?>_<?php echo $r['id2'];?>" ><?php echo $r['descrizione'];?></option>
		  
<?php
    }
    
   
}
?>
