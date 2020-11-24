<?php
if (gethostname()=='vm-lxprotcivemet'){
	$note_ambiente='(<font color=red> <i class="fas fa-exclamation-triangle"></i> test )</font>';
	?>
	<script>
		//alert("Questo e' l'ambiente di test, da non usare in caso di emergenza!.\nSi prega di usare l'indirizzo https://emergenze.comune.genova.it");
	</script>
	<?php
	$check_test=1;
	$note_ambiente_mail = "(versione di test)";
	$note_debug =' <h4> <i><br> Questo è l\'ambiente di test. Per segnalare bug scrivi una mail a ';
	$note_debug = $note_debug .'<a href="mailto:roberto.marzocchi@gter.it?subject=Nuovo%20Sistema%20PC%20GE%20bug">Gter srl</a></h4></i>';
} else {
	$note_ambiente ="";
	$note_ambiente_mail = "";
	$note_debug="";
	$check_test=0;
}
?>