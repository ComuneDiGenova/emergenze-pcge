<?php

session_start();

$EMERGENZE_ROOT = dirname(__DIR__, 2);
include $EMERGENZE_ROOT . '/conn.php';

$uo = '';
$mail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$uo = isset($_POST['cod']) ? str_replace("'", '', trim($_POST['cod'])) : '';
	$mail = isset($_POST['mail']) ? $_POST['mail'] : '';
} else {
	$uo = isset($_GET['cod']) ? str_replace("'", '', trim($_GET['cod'])) : '';
	$mail = isset($_GET['mail']) ? trim(rawurldecode($_GET['mail'])) : '';
}

if ($uo === '' || $mail === '') {
	header('Location: ../lista_mail.php');
	exit;
}

$result = pg_query_params(
	$conn,
	'DELETE FROM users.t_mail_incarichi WHERE cod = $1 AND mail = $2',
	array($uo, $mail)
);

$operatore = isset($_SESSION['operatore']) ? $_SESSION['operatore'] : (isset($_SESSION['user']) ? $_SESSION['user'] : '');
$msg = ($result && pg_affected_rows($result) > 0) ? 'deleted' : 'failed';

if ($msg === 'deleted' && $operatore !== '') {
	$testo = 'Eliminata mail ' . $mail . ' da Unità Operativa ' . $uo;
	pg_query_params($conn, "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('users', $1, $2)", array($operatore, $testo));
}

header('Location: ../edit_mail_uo.php?id=' . rawurlencode($uo) . '&msg=' . $msg);
exit;

?>
