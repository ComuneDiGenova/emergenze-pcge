<?php
	session_start();
	include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';

	$cod = isset($_POST["cod"]) ? pg_escape_string($conn, $_POST["cod"]) : '';
	$f = isset($_POST["f"]) ? pg_escape_string($conn, $_POST["f"]) : '';

	if (!empty($cod)) {
		$query = "SELECT p.id, concat(p.nome, ' (', replace(p.note, 'LOCALITA', ''), ')') as nome
				FROM geodb.punti_monitoraggio_ok p
				WHERE p.id IS NOT NULL";
		
		if (!empty($f)) {
			$query .= " AND $f = '$cod'";
		}
		
		$query .= " ORDER BY p.id;";
	} else {
		$query = "SELECT p.id, concat(p.nome, ' (', replace(p.note, 'LOCALITA', ''), ')') as nome
				FROM geodb.punti_monitoraggio_ok p
				WHERE p.id IS NOT NULL AND $f IS NULL
				ORDER BY p.id;";
	}

	$result = pg_query($conn, $query);

	while ($r = pg_fetch_assoc($result)) {
?>
    <option name="mira" value="<?php echo $r['id'];?>" ><?php echo $r['nome'];?></option>
<?php
	}
?>
