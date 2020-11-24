<?php
session_start();
//require('../validate_input.php');;



include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
if(!empty($_POST["cod"])) {
    $query = "SELECT *, st_y(st_transform(geom,4326)) as lat, st_x(st_transform(geom,4326)) as lon FROM geodb.civici where \"codvia\"='".$_POST["cod"]."' ORDER BY testo;";
    #echo $query;
    $result = pg_query($conn, $query);

     while($r = pg_fetch_assoc($result)) { 
    ?>

        <option name="id_civico" class="story"  value="<?php echo $r['lat'];?>,<?php echo $r['lon'];?>,<?php echo $r['id'];?>" ><?php echo $r['testo'];?></option>
<?php
    }
}
?>
