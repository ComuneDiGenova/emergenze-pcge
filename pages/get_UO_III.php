<?php
session_start();
//require('../validate_input.php');;
//echo"OK";
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
if(!empty($_POST["cod"])) {
	 $id1=explode('_',$_POST["cod"])[0];
	 $id2=explode('_',$_POST["cod"])[1];
    $query = "SELECT * FROM users.\"uo_3_livello\" where id1=".$id1." and id2=".$id2.";";
    echo $query;
    $result = pg_query($conn, $query);

     while($r = pg_fetch_assoc($result)) { 
    ?>

        <option name="UO_III" value="<?php echo $r['id1'];?>_<?php echo $r['id2'];?>_<?php echo $r['id3'];?>" ><?php echo $r['descrizione'];?></option>
<?php
    }
}
?>
