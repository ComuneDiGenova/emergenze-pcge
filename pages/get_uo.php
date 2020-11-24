<?php
session_start();
//require('../validate_input.php');;


$classe= $_POST["cod"];
echo $classe;


include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!empty($classe)) {
	if($classe=='municipi'){
		$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune where cod ilike 'mu%' order by descrizione;";
	} else if($classe=='distretti'){
		$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune where cod ilike 'po%'order by descrizione;";	
	} else if($classe=='direzioni'){
		$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune where cod ilike 'coc%' order by descrizione;";	
	} else if($classe=='esterni') {
		$query = "select concat('uo_',id1) as cod, descrizione from users.uo_1_livello where invio_incarichi = 't' order by descrizione;";
    }
    echo $query;
    $result = pg_query($conn, $query);

     while($r = pg_fetch_assoc($result)) { 
    ?>

        <option name="id_uo" value="<?php echo $r['cod'];?>" ><?php echo $r['descrizione'];?></option>
<?php
    }

	
}
?>