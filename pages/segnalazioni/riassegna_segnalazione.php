<?php

include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';

// DEBUG

// echo json_encode([
//     'success' => false,
//     'debug' => [
//         'id_segnalazione' => (int)filter_input(INPUT_POST, 'id_segnalazione', FILTER_SANITIZE_NUMBER_INT),
//         'id_evento' => (int)filter_input(INPUT_POST, 'id_evento', FILTER_SANITIZE_NUMBER_INT),
//         'raw_post' => $_POST,
//     ],
//     'message' => 'Debug dei parametri ricevuti.',
// ]);
// exit;

// Validazione dei parametri
$id_segnalazione = (int)filter_input(INPUT_POST, 'id_segnalazione', FILTER_SANITIZE_NUMBER_INT);
$id_evento = (int)filter_input(INPUT_POST, 'id_evento', FILTER_SANITIZE_NUMBER_INT);

if (!$id_segnalazione || !$id_evento) {
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti o non validi.']);
    exit;
}

// Query parametrizzata
$query = "UPDATE segnalazioni.t_segnalazioni SET id_evento = $1 WHERE id = $2;";

$result = pg_prepare($conn, "reassign_query", $query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore nella preparazione della query.',
        'debug' => pg_last_error($conn)
    ]);
    exit;
}

$result = pg_execute($conn, "reassign_query", [$id_evento, $id_segnalazione]);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore durante l\'esecuzione della query.',
        'debug' => pg_last_error($conn)
    ]);
    exit;
}


if ($result) {
    echo json_encode(['success' => true, 'message' => 'Segnalazione riassegnata con successo.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore durante la riassegnazione.']);
}
?>