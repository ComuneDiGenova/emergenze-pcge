<?php
session_start();
//require('../validate_input.php');;
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
if(!empty($_POST["cod"])) {
    $query = "SELECT * FROM varie.comuni_italia where \"Codice Provincia\"='".$_POST["cod"]."' OR \"Codice Città Metropolitana\"='".$_POST["cod"]."';";
    #echo $query;
    $result = pg_query($conn, $query);

     while($r = pg_fetch_assoc($result)) { 
    ?>

        <option name="comune" value="<?php echo $r['Codice Comune formato alfanumerico'];?>" ><?php echo $r['Denominazione in italiano'];?></option>
<?php
    }
}
?>
