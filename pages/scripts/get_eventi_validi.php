<?php

session_start();


include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';


header('Content-Type: application/json');

// Controlla se la connessione al database è valida
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Connessione al database fallita']);
    exit;
}

$query = "SELECT e.id, e.descrizione, 
                to_char(e.data_ora_inizio_evento, 'YYYY/MM/DD HH24:MI'::text) AS data_ora_inizio_evento,  
                e.valido
            FROM eventi.v_eventi e
            where e.valido = true
            ORDER by data_ora_inizio_evento DESC;";

$result = pg_query($conn, $query);

// Controlla il risultato della query
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Errore durante la query al database']);
    exit;
}

// Crea un array per memorizzare i dati degli eventi
$eventi = [];

// Itera sui risultati della query
while ($row = pg_fetch_assoc($result)) {
    $eventi[] = [
        'id' => $row['id'],
        'descrizione' => $row['descrizione'],
        'data_ora_inizio_evento' => $row['data_ora_inizio_evento']
    ];
}

pg_close($conn);
echo json_encode($eventi);
