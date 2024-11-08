<?php
	session_start();
	require('../validate_input.php');

	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

	// Verifica se la richiesta è POST
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Assicurati che i dati siano presenti
		if (!isset($_POST['mira']) || !isset($_POST['tipo'])) {
			echo "Dati mancanti.";
			exit;
		}

		// Decodifica i dati inviati
		$mira_ids = json_decode($_POST['mira']);
		$tipo_lettura = $_POST['tipo'];

		// Inizializza una lista per i valori da inserire
		$values = [];

		$data_inizio = date('Y-m-d H:i'); // Usa l'ora attuale

		// Crea la lista di valori da inserire nel database
		foreach ($mira_ids as $id) {
			$id = pg_escape_string($id); // Prevenzione di SQL Injection
			$values[] = "($id, $tipo_lettura, '$data_inizio')";
		}
		
		// Costruisci la query di inserimento
		if (!empty($values)) {
			$values_str = implode(", ", $values);
			$query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora) VALUES $values_str;";
			
			// Esegui la query di inserimento
			$result = pg_query($conn, $query);
			if (!$result) {
				echo "Errore durante l'inserimento delle letture: " . pg_last_error($conn);
				exit; // Ferma l'esecuzione in caso di errore
			}
	
			// Log dell'operazione
			$mira_ids_str = implode(",", $mira_ids);
			$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('geodb', '" . pg_escape_string($_SESSION["Utente"]) . "', 'Inserite letture per le mire: $mira_ids_str');";
			
			$result_log = pg_query($conn, $query_log);
			if (!$result_log) {
				echo "Errore durante il log dell'operazione: " . pg_last_error($conn);
			} else {
				echo "Inserimento completato con successo.";
			}
		} else {
			echo "Nessun valore da inserire.";
		}
	} else {
		echo "Richiesta non valida.";
	}

?>