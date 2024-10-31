<?php
	session_start();
	require('../validate_input.php');

	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

	// Verifica se la richiesta è POST
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (isset($_POST['ids']) && is_array($_POST['ids'])) {
			$ids = $_POST['ids'];
			$value = intval($_POST['value']);

			// Ottieni la data e l'ora correnti
			$data_ora = date('Y-m-d H:i:s');
			
			$id_list = array_map('intval', $ids);

			// Crea una stringa per l'inserimento massivo
			$values = [];
			foreach ($ids as $id) {
				$id_int = intval($id); // Converti ogni ID in intero
				$values[] = "($id_int, $value, '$data_ora')"; // Crea la stringa per l'inserimento
			}

			// Creare la query di inserimento
			$query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora) VALUES ". implode(', ', $values);
			$result = pg_query($conn, $query);

			if ($result) {
				echo "Aggiornamento effettuato";
			} else {
				echo "Errore durante l'aggiornamento: " . $query;
			}
		} else {
			echo "Nessun ID fornito!";
		}
	} else {
		echo "Richiesta non valida!";
	}
?>
