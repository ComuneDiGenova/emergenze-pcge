<?php
session_start();

//echo "cod=" .$_POST["cod"];

//echo "<br> field= " .$_POST["f"];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
if(!empty($_POST["cod"])) {
    $query = "SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome
			FROM geodb.punti_monitoraggio_ok p
			WHERE p.id is not null ";
	if (!empty($_POST["f"])){
		$query= $query. " and ".$_POST["f"]." = '".$_POST["cod"]."' ";
	}
	$query= $query. " order by nome ;";
    //echo $query."<br>";
    $result = pg_query($conn, $query);

     while($r = pg_fetch_assoc($result)) { 
    ?>

        <option name="mira" value="<?php echo $r['id'];?>" ><?php echo $r['nome'];?></option>
<?php
    }
} else {
	$query = "SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome
			FROM geodb.punti_monitoraggio_ok p
			WHERE p.id is not null and ".$_POST["f"]."  is null  
			order by nome;";
	$result = pg_query($conn, $query);

	while($r = pg_fetch_assoc($result)) { 
?>
		<option name="mira" value="<?php echo $r['id'];?>" ><?php echo $r['nome'];?></option>
<?php
	}
}
?>
