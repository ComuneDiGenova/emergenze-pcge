<?php
require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');
header('Content-Type: application/json');

if (isset($_GET['uo1_id'])) {
    $uo1_id = pg_escape_string($conn, $_GET['uo1_id']);

    $query = "
        SELECT id2, descrizione
        FROM \"users\".\"uo_2_livello\"
        WHERE id1 = '$uo1_id' AND valido = true
        ORDER BY descrizione;
    ";

    $result = pg_query($conn, $query);

    $output = [];
    while ($row = pg_fetch_assoc($result)) {
        $output[] = [
            'id' => $row['id2'], // usa id2 come valore dell'opzione
            'descrizione' => $row['descrizione']
        ];
    }

    echo json_encode($output);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Parametro mancante']);