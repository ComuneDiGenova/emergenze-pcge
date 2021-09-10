<?php 

$subtitle="Report 8h (riepilogo segnalazioni in corso di evento)";

$id=pg_escape_string($_GET['id']);


?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione emergenze</title>
<?php 

function roundToQuarterHour($now){
	$minutes = $now['minutes'] - $now['minutes']%15;
	$test = $now['minutes']%15;
	//echo 'minuti now: '.$now['minutes'].'</br>';
	//echo 'minuti now 15: '.$test.'</br>';
	//echo 'minuti: '.$minutes.'</br>';
	if ($minutes < 10) {
		$minutes = '0'.$minutes;
		//echo 'minuti if: ' .$minutes.'</br>';
	}

	$rounded = $now["mday"]."/".$now["mon"]."/".substr($now["year"],-2)."<br>".$now['hours'].":".$minutes;
	//echo 'ora finale: '.$rounded.'</br>';
	return $rounded;
}

//require('./tables/griglia_dipendenti_save.php');
require('./req.php');
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');
//require('./conn.php');

require('./check_evento.php');


?>


    
</head>

<body>

    <div id="wrapper">

        <div id="navbar1">
<?php
require('navbar_up.php');
?>
</div>  
        <?php 
            require('./navbar_left.php');
            
         

        ?> 
            

        <div id="page-wrapper">
            <div class="row">
                <!--div class="col-sm-12">
                    <h1 class="page-header">Dashboard</h1>
                </div-->
                <!-- /.col-sm-12 -->
            </div>
            <!-- /.row -->
            
            
            <?php //echo $note_debug; ?>
           

            
			

            <div class="row">
			<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
			<h3>Evento n. <?php echo str_replace("'", "", $id); ?> - Tipo: 
			<?php
			$query_e='SELECT e.id, tt.descrizione, n.nota, to_char(e.data_ora_inizio_evento, \'DD/MM/YYYY HH24:MI\'::text) AS data_ora_inizio_evento, 
			to_char(e.data_ora_chiusura, \'DD/MM/YYYY HH24:MI\'::text) AS data_ora_chiusura, 
			to_char(e.data_ora_fine_evento, \'DD/MM/YYYY HH24:MI\'::text) AS data_ora_fine_evento 
            FROM eventi.t_eventi e
            JOIN eventi.join_tipo_evento t ON t.id_evento=e.id
			LEFT JOIN eventi.t_note_eventi n ON n.id_evento=e.id
            JOIN eventi.tipo_evento tt on tt.id=t.id_tipo_evento
			 	WHERE e.id =' .$id.';';
				$result_e = pg_query($conn, $query_e);
				while($r_e = pg_fetch_assoc($result_e)) {
					echo $r_e['descrizione'];
					$descrizione_evento=$r_e['descrizione'];
					$nota_evento=$r_e['nota'];
					$inizio_evento=$r_e['data_ora_inizio_evento'];
					$chiusura_evento=$r_e['data_ora_chiusura'];
					$fine_evento=$r_e['data_ora_fine_evento'];
				}
			if ($profilo_sistema>0 and $profilo_sistema<=3){
			?>
			<button class="btn btn-info noprint" onclick="printDiv('page-wrapper')">
			<i class="fa fa-print" aria-hidden="true"></i> Stampa report </button>
			<?php } ?>
			</h3>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<h3> Data:
			<script>
			var d = new Date();
			var curr_date = d.getDate();
			var curr_month = d.getMonth()+1;
			var curr_year = d.getFullYear();
			document.write(curr_date + "/" + curr_month + "/" + curr_year);
			</script>
			Ora:
			<script>
			var d = new Date();
			var curr_h = ('0'+d.getHours()).slice(-2);
			var curr_min = ('0'+d.getMinutes()).slice(-2);
			document.write(curr_h + ":" + curr_min);
			</script>
			</h3>
			</div>
			</div>
			<hr>
			<?php
			echo '<div class="row"><div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">';
			echo ' <img src="../img/pc_ge_sm.png" alt=""></div>';
			echo '<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">';
			if (isset($nota_evento)){
				echo '<h2>'.$nota_evento.'</h2>'; 
			}
			echo '<b>Municipi interessati</b>: ';
			$query3="SELECT  b.nome_munic From eventi.join_municipi a,geodb.municipi b  WHERE a.id_evento=".$id." and a.id_municipio::integer=b.codice_mun::integer;";
			//echo $query3;
			$result3 = pg_query($conn, $query3);
			$k=0;
			while($r3 = pg_fetch_assoc($result3)) {
				if ($k>0){
					echo ', ';
				}
				echo $r3["nome_munic"];
				$k=$k+1;
				//$municipir[]=array($id,$r3["nome_munic"]);
			}
			
			echo '<br><b>Data e ora inizio</b>: '.$inizio_evento;
			$check_chiusura=0;
			if ($chiusura_evento!=''){
				echo '<br><b>Data e ora inizio fase di chiusura</b>: '.$chiusura_evento;
			}
			if ($fine_evento!=''){
				echo '<br><b>Data e ora chiusura definitiva</b>: '.$fine_evento;
			}
			if ($chiusura_evento!='' && $fine_evento=='' ){
				echo ' - <i class="fas fa-hourglass-end"></i> Evento in chiusura';
			}
			if ($chiusura_evento!='' && $fine_evento!='' ){
				$check_chiusura=1;
				echo ' - <i class="fas fa-stop"></i> Evento chiuso';
			}
			echo '</div></div>';

			// check sulle viste da usare
			if ($check_chiusura==0){
				$v_incarichi_last_update='v_incarichi_last_update';
				$v_incarichi_interni_last_update='v_incarichi_interni_last_update';
				$v_sopralluoghi_last_update='v_sopralluoghi_last_update';
				$v_sopralluoghi_mobili_last_update='v_sopralluoghi_mobili_last_update';
				$v_provvedimenti_cautelari_last_update='v_provvedimenti_cautelari_last_update';
			} else if ($check_chiusura==1) {
				$v_incarichi_last_update='v_incarichi_eventi_chiusi_last_update';
				$v_incarichi_interni_last_update='v_incarichi_interni_eventi_chiusi_last_update';
				$v_sopralluoghi_last_update='v_sopralluoghi_eventi_chiusi_last_update';
				$v_sopralluoghi_mobili_last_update='v_sopralluoghi_mobili_eventi_chiusi_last_update';
				$v_provvedimenti_cautelari_last_update='v_provvedimenti_cautelari_eventi_chiusi_last_update';
			}
			


			?>
			<hr>
			<div class="row">
				<?php require('./allerte_embed.php'); ?>
			<hr>
			</div>
			
			<div class="row">
			 <?php require('./monitoraggio_meteo_embed.php'); ?>
			</div>
			
			<hr>
			<div class="row">
			<?php require('./comunicazioni_embed.php'); ?>
			</div>
			
			<div class="row">
			
            <?php require('./attivita_sala_emergenze_embed.php'); ?>
			
			</div>
			
			<hr>
            <div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h3>Comunicazioni e informazioni alla popolazione</h3>
			</div>
			</div>
			<div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h4> 
			 <?php if( $contatore_nverde > 0) {?>
				<i>Numero verde non attivo</i>
			 <?php } else { ?>
				<i>Numero verde attivo</i>
			 <?php }  ?> 
			</h4>
			
			<?php

			$query="SELECT * FROM eventi.t_attivazione_nverde WHERE id_evento=".$id." and data_ora_fine <= now();";
			//echo $query;
			//exit;
				$result = pg_query($conn, $query);
			while($r = pg_fetch_assoc($result)) {
				$check_nverde=2;
			}
			
				
			if($check_nverde==2) {
				echo "<h5>Storico numero verde<h5>";
			$result = pg_query($conn, $query);
			while($r = pg_fetch_assoc($result)) {	

				$timestamp = strtotime($r["data_ora_inizio"]);
				setlocale(LC_TIME, 'it_IT.UTF8');
				$data_start = strftime('%A %e %B %G', $timestamp);
				$ora_start = date('H:i', $timestamp);
				$timestamp = strtotime($r["data_ora_fine"]);
				$data_end = strftime('%A %e %B %G', $timestamp);
				$ora_end = date('H:i', $timestamp);								
				$color=str_replace("'","",$r["rgb_hex"]);
				//echo $color;
				echo "<li> <i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\007c37\"></i> <b>Numero verde  attivo</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " </li>";
			}
			}
			?>
            </div>
            
            </div>
			<div class="row">
            
			<?php require('./operatore_nverde_embed.php'); ?>
            
           
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <hr>
            <h4>Numero chiamate ricevute</h4>
            
            <?php 
            /*$query_e="SELECT e.id, tt.descrizione 
            FROM eventi.t_eventi e
            JOIN eventi.join_tipo_evento t ON t.id_evento=e.id
            JOIN eventi.tipo_evento tt on tt.id=t.id_tipo_evento
			 	WHERE e.valido != 'f'
			   GROUP BY e.id, tt.descrizione;";
             
            $result_e = pg_query($conn, $query_e);
				//echo "<ul>";
				while($r_e = pg_fetch_assoc($result_e)) {*/
					//echo '<b>Tipo Evento</b>:'.$r_e['descrizione']. '<br>';
					$query="SELECT count(r.id)
					FROM segnalazioni.t_richieste_nverde r 
					WHERE r.id_evento = ".$id.";";
					//echo $query;
					$result = pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						echo "<b>Richieste generiche:</b>".$r['count']."<br>";
					}
					$query="SELECT count(r.id)
					FROM segnalazioni.t_segnalazioni r 
					WHERE r.id_evento = ".$id.";";
					//echo $query;
					$result = pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						echo "<b>Segnalazioni:</b>".$r['count']."<br><br>";
					}
				/*}*/ 
            
            
            
            ?>
            
            
            </div>            
            </div>
            <!-- /.row -->            
            <hr>
            
            
            <?php 
             
            //require('./conteggi_dashboard.php');
            
            //require('./contatori.php');
            ?>
            
            <div class="row">
                
                
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h3>Elenco segnalazioni </h3>
			</div>
<hr>
<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">			
<h4>Riepilogo</h4>
</div>



<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
<svg width="400" height="300"></svg>
<?php
require('./grafico_criticita.php');
?>
</div>


<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">			
<table  id="segnalazioni_count" class="table table-condensed" 
style="word-break:break-all; word-wrap:break-word;" data-toggle="table" 
data-url="./tables/griglia_segnalazioni_conteggi.php?id=<?php echo $id?>" 
data-show-export="false" data-search="false" data-click-to-select="false" 
data-pagination="false" data-sidePagination="false" data-show-refresh="false" 
data-show-toggle="false" data-show-columns="false" data-toolbar="#toolbar">

<thead>

<tr>
   <th data-field="criticita" data-sortable="false" data-visible="true" >Tipologia</th>
   <th data-field="pervenute" data-sortable="true" data-visible="true">Pervenute</th>
   <th data-field="risolte" data-sortable="true" data-visible="true">Risolte</th>
</tr>
</thead>
</table>
</div>             
<hr>		 
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">			 
<h4>Dettaglio segnalazioni in elaborazione o chiuse</h4>

<?php 

$query= " SELECT 
 min(s.data_ora) as data_ora,
    count(s.id) AS num,
	string_agg(s.id::text, ', '::text) AS id_segn,
    string_agg(s.descrizione::text, ', '::text) AS descrizione,
    array_to_string(array_agg(DISTINCT c.descrizione::text), ', '::text) AS criticita,
    array_to_string(array_agg(DISTINCT m.nome_munic::text), ', '::text) AS nome_munic,
    string_agg(
        CASE
            WHEN s.id_civico IS NULL THEN ( SELECT concat('~ ', civici.desvia, ' ', civici.testo) AS concat
               FROM geodb.civici
              WHERE civici.geom && st_expand(st_transform(s.geom, 3003), 250::double precision)
              ORDER BY (st_distance(civici.geom, st_transform(s.geom, 3003)))
             LIMIT 1)
            ELSE (g.desvia::text || ' '::text) || g.testo::text
        END, ', '::text) AS localizzazione,
    jl.id_segnalazione_in_lavorazione AS id_lavorazione,
    l.in_lavorazione,
    l.descrizione_chiusura,
    l.id_profilo,
        CASE
            WHEN (( SELECT count(i.id) AS sum
               FROM segnalazioni.".$v_incarichi_last_update." i
              WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_incarico < 3)) > 0 OR (( SELECT count(i.id) AS sum
               FROM segnalazioni.".$v_incarichi_interni_last_update." i
              WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_incarico < 3)) > 0 OR (( SELECT count(i.id) AS sum
               FROM segnalazioni.".$v_provvedimenti_cautelari_last_update." i
              WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_provvedimenti_cautelari < 3)) > 0 OR (( SELECT count(i.id) AS sum
               FROM segnalazioni.".$v_sopralluoghi_last_update." i
              WHERE i.id_lavorazione = jl.id_segnalazione_in_lavorazione AND i.id_stato_sopralluogo < 3)) > 0 THEN 't'::text
            ELSE 'f'::text
        END AS incarichi,
	   (SELECT count(i.id) AS sum
         FROM segnalazioni.t_incarichi i
		JOIN segnalazioni.join_segnalazioni_incarichi j ON j.id_incarico= i.id
         WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione) as conteggio_incarichi,
		(SELECT count(i.id) AS sum
         FROM segnalazioni.t_incarichi_interni i
		JOIN segnalazioni.join_segnalazioni_incarichi_interni j ON j.id_incarico= i.id
         WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione) as conteggio_incarichi_interni,
		(SELECT count(i.id) AS sum
         FROM segnalazioni.t_sopralluoghi i
		JOIN segnalazioni.join_segnalazioni_sopralluoghi j ON j.id_sopralluogo= i.id
         WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione) as conteggio_sopralluoghi,
		(SELECT count(i.id) AS sum
         FROM segnalazioni.t_provvedimenti_cautelari i
		JOIN segnalazioni.join_segnalazioni_provvedimenti_cautelari j ON j.id_provvedimento = i.id
         WHERE j.id_segnalazione_in_lavorazione = jl.id_segnalazione_in_lavorazione) as conteggio_pc,
    max(s.geom::text) AS geom 
   FROM segnalazioni.t_segnalazioni s
     JOIN segnalazioni.tipo_criticita c ON c.id = s.id_criticita
     JOIN eventi.t_eventi e ON e.id = s.id_evento
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl ON jl.id_segnalazione = s.id
     LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l ON jl.id_segnalazione_in_lavorazione = l.id
     LEFT JOIN geodb.municipi m ON s.id_municipio = m.id::integer
     LEFT JOIN geodb.civici g ON g.id = s.id_civico
  WHERE s.id_evento=".$id." and jl.id_segnalazione_in_lavorazione > 0
  GROUP BY jl.id_segnalazione_in_lavorazione, l.in_lavorazione, l.id_profilo, s.id_evento, e.fine_sospensione, l.descrizione_chiusura
  ORDER BY data_ora ASC;";
//echo $query;
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	echo "<b>Id segnalazioni:</b>".$r['id_segn']." - ";
	if ($r['num'] > 1){
		echo "<b>Num. segnalazioni collegate:</b>".$r['num']." - ";
	}
	echo "<b>Stato</b>: ";
	if ($r['in_lavorazione']=='t'){
		echo '<i class="fas fa-play" style="color:#5cb85c"></i> in lavorazione';
	} else if ($r['in_lavorazione']=='f') {
		echo '<i class="fas fa-stop"></i> chiusa';
	} else {
		echo '<i class="fas fa-exclamation" style="color:#ff0000"></i> da prendere in carico';
	}
	echo "<br>";
	if($r['num']>1){
		echo "<b>Data e ora prina segnalazione:</b>".$r['data_ora']."<br>";
	} else {
		echo "<b>Data e ora segnalazione:</b>".$r['data_ora']."<br>";
	}
	echo "<b>Tipo criticit&agrave:</b>".$r['criticita']."<br>";
	echo "<b>Descrizione:</b>".$r['descrizione']."<br>";
	echo "<b>Municipio:</b>".$r['nome_munic']." - ";
	echo "<b>Indirizzo:</b>".$r['localizzazione']."<br>";
	if ($r['descrizione_chiusura']!=''){
		echo "<b>Note chiusura:</b>".$r['descrizione_chiusura']."<br>";
	}
	if ($r['descrizione_chiusura']=='') {
		if ($r['incarichi']=='t'){
			echo '<i class="fas fa-circle" title="incarichi in corso" style="color:#f2d921"></i> Lavorazione in corso - ';
		} else if ($r['incarichi']=='f') {
			echo '<i class="fas fa-circle" title="nessun incarico in corso" style="color:#ff0000"></i> Nessuna lavorazione in corso - ';
		} 
		if ($r['conteggio_incarichi']>0){
			echo ' '.$r['conteggio_incarichi'].' incarichi assegnati - ';
		} else {
			echo 'Nessun incarico assegnato - ';
		}
		if ($r['conteggio_incarichi_interni']>0){
			echo ' '.$r['conteggio_incarichi_interni'].' incarichi interni assegnati - ';
		} else {
			echo 'Nessun incarico interno assegnato - ';
		}
		if ($r['conteggio_sopralluoghi']>0){
			echo ' '.$r['conteggio_incarichi'].' presidi assegnati - ';
		} else {
			echo 'Nessun presidio assegnato - ';
		}
		if ($r['conteggio_pc']>0){
			echo ' '.$r['conteggio_pc'].' provvedimenti cautelari assegnati - ';
		} else {
			echo 'Nessun provvedidimento cautelare assegnato - ';
		}
	}
	
		if ($r['conteggio_incarichi']>0){
			echo '<br>--<br><b>Incarichi:</b> ';
			$query_i = 'SELECT 
			data_ora_invio, 
			descrizione, 
			descrizione_uo, descrizione_stato
			FROM segnalazioni.'.$v_incarichi_last_update.' s 
			WHERE s.id_lavorazione='.$r['id_lavorazione'].' GROUP BY data_ora_invio, 
			descrizione, 
			descrizione_uo, descrizione_stato ORDER BY data_ora_invio asc;';
			//echo $query_i;
			$result_i = pg_query($conn, $query_i);
			while($r_i = pg_fetch_assoc($result_i)) {
				echo '<br>' .$r_i['data_ora_invio'];
				echo ' - ' . $r_i['descrizione_stato']. ' - ';
				echo $r_i['descrizione_uo'] .' ('.$r_i['descrizione'].')';
			}
		}
	
		if ($r['conteggio_incarichi_interni']>0){
			echo '<br>--<br><b>Incarichi interni:</b> ';
			$query_i = 'SELECT 
			data_ora_invio, 
			descrizione, 
			descrizione_uo, descrizione_stato
			FROM segnalazioni.'.$v_incarichi_interni_last_update.' s 
			WHERE s.id_lavorazione='.$r['id_lavorazione'].' GROUP BY data_ora_invio, 
			descrizione, 
			descrizione_uo, descrizione_stato  
			ORDER BY data_ora_invio asc;';
			echo $query_i;
			$result_i = pg_query($conn, $query_i);
			while($r_i = pg_fetch_assoc($result_i)) {
				echo '<br>' .$r_i['data_ora_invio'];
				echo ' - ' . $r_i['descrizione_stato']. ' - ';
				echo $r_i['descrizione_uo'] .' ('.$r_i['descrizione'].')';
			}
		}
		
		
		
		if ($r['conteggio_sopralluoghi']>0){
			echo '<br>--<br><b>Presidi:</b> ';
			$query_i = 'SELECT 
			data_ora_invio, 
			descrizione, 
			descrizione_uo, descrizione_stato
			FROM segnalazioni.'.$v_sopralluoghi_last_update.' s 
			WHERE id_lavorazione='.$r['id_lavorazione'].' GROUP BY data_ora_invio, 
			descrizione, 
			descrizione_uo, descrizione_stato ORDER BY data_ora_invio asc;';
			//echo $query_i;
			$result_i = pg_query($conn, $query_i);
			while($r_i = pg_fetch_assoc($result_i)) {
				echo '<br>' .$r_i['data_ora_invio'];
				echo ' - ' . $r_i['descrizione_stato']. ' - ';
				echo $r_i['descrizione_uo'] .' ('.$r_i['descrizione'].')';
			}
		}
	//echo "<b>Note:</b>".$r['localizzazione']."<br>";
	echo "<hr>";
}
  
  
?>


<script>


 function nameFormatter(value) {
        if (value=='t'){
        		return '<i class="fas fa-play" style="color:#5cb85c"></i> in lavorazione';
        } else if (value=='f') {
        	   return '<i class="fas fa-stop"></i> chiusa';
        } else {
        	   return '<i class="fas fa-exclamation" style="color:#ff0000"></i> da prendere in carico';;
        }

    }
    
 function nameFormatterEdit(value) {
        
		return '<a class="btn btn-warning" href=./dettagli_segnalazione.php?id='+value+'> <i class="fas fa-edit"></i> </a>';
 
    }

  function nameFormatterRischio(value) {
        //return '<i class="fas fa-'+ value +'"></i>' ;
        
        if (value=='t'){
        		return '<i class="fas fa-exclamation-triangle" style="color:#ff0000"></i>';
        } else if (value=='f') {
        	   return '<i class="fas fa-check" style="color:#5cb85c"></i>';
        }
        else {
        		return '<i class="fas fa-question" style="color:#505050"></i>';
        }
    }


function nameFormatterMappa1(value, row) {
	//var test_id= row.id;
	return' <button type="button" class="btn btn-info" data-toggle="modal" data-target="#myMap'+value+'"><i class="fas fa-map-marked-alt"></i></button> \
    <div class="modal fade" id="myMap'+value+'" role="dialog"> \
    <div class="modal-dialog"> \
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal">&times;</button>\
          <h4 class="modal-title">Anteprima segnalazione '+value+'</h4>\
        </div>\
        <div class="modal-body">\
        <iframe class="embed-responsive-item" style="width:100%; padding-top:0%; height:600px;" src="./mappa_leaflet.php#17/'+row.lat +'/'+row.lon +'"></iframe>\
        </div>\
        <!--div class="modal-footer">\
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
        </div-->\
      </div>\
    </div>\
  </div>\
</div>';
}
	
	





</script>			 
                
                
                
                    <!--div id="panel-riepilogo" class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bell fa-fw"></i> Pannello riepilogo
                        </div>
                        
                        <div class="panel-body">
                            <div class="list-group">
                               
                                		<?php if($segn_limbo>0){?>
                                			 <a href="#segn_limbo_table" class="list-group-item">
	                                    <i class="fa fa-exclamation fa-fw" style="color:red"></i> Nuove segnalazioni da elaborare!
	                                    <span class="pull-right text-muted small"><em><?php echo $segn_limbo; ?></em>
	                                    </span>
	                                    </a>
                                    <?php }?>
                                
								
											<?php if($inc_limbo>0){?>
                                			 <div class="list-group-item" >
	                                    <i class="fa fa-exclamation fa-fw" style="color:red"></i> Nuovi incarichi ancora da prendere in carico!
	                                    <span class="pull-right text-muted small"><em><?php echo $inc_limbo; ?></em>
	                                    </span>
	                                    
	                                    </div>
                                    <?php }?>
								
								<div class="list-group-item" >
											
                                
                                    <i class="fa fa-users"></i> <b>Gestione squadre</b>
                                    <br><br>
                                     - <i class="fa fa-play"></i> Squadre in azione
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_in_azione; ?></em>
                                    </span>
                                    
                                    <br>
                                     - <i class="fa fa-pause"></i> Squadre a disposizione
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_disposizione; ?></em>
                                    </span>
                                    <br>
                                     - <i class="fa fa-stop"></i> Squadre a riposo
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_riposo; ?></em>
                                    </span>
                                    <hr>
                                    Totale squadre eventi attivi:
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_riposo; ?></em>
                                    </span>
                                </div>
                            
                            <a href="./gestione_squadre.php" class="btn btn-default btn-block">Vai alla gestione squadre</a>
							
							
							<div class="list-group-item" >
											
                                
                                    <i class="fa fa-pencil-ruler"></i> <b>Presidi</b>
                                    <br><br>
                                     - <i class="fa fa-pause"></i> Assegnati
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_assegnati; ?></em>
                                    </span>
                                    
                                    <br>
                                     - <i class="fa fa-play"></i> In corso
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_corso; ?></em>
                                    </span>
                                    <br>
                                     - <i class="fa fa-stop"></i> Conclusi
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_conclusi; ?></em>
                                    </span>
                                    <hr>
                                    Totale presidi eventi attivi:
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_tot; ?></em>
                                    </span>
                                </div>
                            
                            <a href="./nuovo_sopralluogo.php" class="btn btn-default btn-block">Crea un nuovo presidio</a>
							
							</div>
							
							<div class="list-group-item" >
											
                                
                                    <i class="fa fa-exclamation-triangle"></i> <b>Provvedimenti cautelari</b>
                                    <br><br>
                                     - <i class="fa fa-pause"></i> Assegnati
                                    <span class="pull-right text-muted small"><em><?php echo $pc_assegnati; ?></em>
                                    </span>
                                    
                                    <br>
                                     - <i class="fa fa-play"></i> In corso
                                    <span class="pull-right text-muted small"><em><?php echo $pc_corso; ?></em>
                                    </span>
                                    <br>
                                     - <i class="fa fa-stop"></i> Portati a termine
                                    <span class="pull-right text-muted small"><em><?php echo $pc_conclusi; ?></em>
                                    </span>
                                    <hr>
                                    Totale provvedimenti cautelari eventi attivi:
                                    <span class="pull-right text-muted small"><em><?php echo $pc_tot; ?></em>
                                    </span>
                                </div>
                            

                            <a href="./elenco_pc.php" class="btn btn-default btn-block">Elenco provvedimenti cautelari</a>
							
							</div>
                        
						
						
						
						

                    </div-->


                    
                    
                    
                    
                    
            </div> 
            
            
</div>        
     
<div class="row">              
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h3>Elenco provvedimenti cautelari </h3>
<table  id="pc_count" class="table table-condensed" 
style="word-break:break-all; word-wrap:break-word;" data-toggle="table" 
data-url="./tables/griglia_pc_report.php?id=<?php echo $id?>" 
data-show-export="false" data-search="false" data-click-to-select="false" 
data-pagination="false" data-sidePagination="false" data-show-refresh="true" 
data-show-toggle="false" data-show-columns="false" data-toolbar="#toolbar">

<thead>

<tr>
   <th data-field="tipo_provvedimento" data-sortable="false" data-visible="true" >Tipologia</th>
   <th data-field="descrizione_stato" data-sortable="true" data-visible="true">Stato</th>
   <th data-field="count" data-sortable="true" data-visible="true">Totale</th>
</tr>
</thead>
</table>
               
               <?php
               $query="SELECT sum(residenti) from segnalazioni.v_residenti_allontanati 
               where id_evento=".$id.";";
               $result = pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						echo "<br><br><b>Residenti allontanati in questo momento::</b>".$r['sum']."<br><br>";
					}
                
                
				?>
            </div>
                <!-- /.col-sm-4 -->
            </div>
			<?php 
				if($descrizione_evento =='Avviso meteo' || $descrizione_evento =='Idrologico'){
			?>
			<hr>
			<!-- <div class="row"> -->

				
			<!-- </div> -->
			<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<?php

				//echo 'tipo evento'.$descrizione_evento;

				$now = getdate();
				//echo $now;
				// foreach ($now as $key => $value) {
				// 	echo $key.'</br>';
				// 	echo $value.'</br>';
				// }
				$ora0 = roundToQuarterHour($now);
				//echo $ora0;
				//echo "<br><br>";
				$data = getdate(strtotime('- 30 minutes'));
				$ora1 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 60 minutes'));
				$ora2 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 90 minutes'));
				$ora3 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 120 minutes'));
				$ora4 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 150 minutes'));
				$ora5 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 180 minutes'));
				$ora6 = roundToQuarterHour($data);

				$data = getdate(strtotime('- 210 minutes'));
				$ora7 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 240 minutes'));
				$ora8 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 270 minutes'));
				$ora9 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 300 minutes'));
				$ora10 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 330 minutes'));
				$ora11 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 360 minutes'));
				$ora12 = roundToQuarterHour($data);

				$data = getdate(strtotime('- 390 minutes'));
				$ora13 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 420 minutes'));
				$ora14 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 450 minutes'));
				$ora15 = roundToQuarterHour($data);
				
				$data = getdate(strtotime('- 480 minutes'));
				$ora16 = roundToQuarterHour($data);
				
				?>
                <h3>Monitoraggio (Letture Mire e Idrometri nelle 8 ore precedenti)</h3>
				<h4>Letture Mire e Rivi </h4>
				<table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_mire_report.php" 
				data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json','xml','csv','txt','sql','excel','doc','pdf'] 
				data-search="true" data-click-to-select="true" data-show-print="true"  
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
				data-filter-control="true" data-toolbar="#toolbar">
        
				<thead>

					<tr>
						<th class="noprint" data-field="state" data-checkbox="true"></th>    
						<th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Rio</th>
						<!--th data-field="tipo" data-sortable="true" data-visible="true" data-filter-control="select">Tipo</th-->
						<!--th data-field="id" data-sortable="true" data-visible="false" data-filter-control="select">Id</th-->
						<!--th data-field="perc_al_g" data-sortable="true" <?php if ($perc!='perc_al_g'){?> data-visible="false" <?php }?> data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta gialla" style="color:#ffd800;"></i></th>
						<th data-field="perc_al_a" data-sortable="true" <?php if ($perc!='perc_al_a'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percoso allerta arancione" style="color:#ff8c00;"></i></th>
						<th data-field="perc_al_r" data-sortable="true"  <?php if ($perc!='perc_al_r'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta rossa" style="color:#e00000;"></i></th>
						<th data-field="arancio" data-sortable="true" data-visible="false"> Liv arancione</th>
						<th data-field="rosso" data-sortable="true" data-visible="false" >Liv rosso</th-->
						<th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
						<th data-field="16" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora16;?> 16</th>
						<th data-field="15" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora15;?> 15</th>            
						<th data-field="14" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora14;?> 14</th>
						<th data-field="13" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora13;?> 13</th>
						<th data-field="12" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora12;?> 12</th>            
						<th data-field="11" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora11;?> 11</th>
						<th data-field="10" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora10;?> 10</th>  
						<th data-field="9" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora9;?> 9</th>
						<th data-field="8" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora8;?> 8</th>
						<th data-field="7" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora7;?> 7</th>
						<th data-field="6" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora6;?> 6</th>
						<th data-field="5" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora5;?> 5</th>            
						<th data-field="4" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora4;?> 4</th>
						<th data-field="3" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora3;?> 3</th>  
						<th data-field="2" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora2;?> 2</th>
						<th data-field="1" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora1;?> 1</th>
						<th data-field="0" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora0;?> 0</th>
						<!--th class="noprint" data-field="id" data-sortable="false" data-formatter="nameFormatterInsert" data-visible="true">Edit</th-->
					</tr>
				</thead>
				</table><br>
				<h4>Valori Idrometri ARPAL </h4>
				<table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_idro_arpal_report.php" 
				data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
				data-search="true" data-click-to-select="true" data-show-print="true"  
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
				data-filter-control="true" data-toolbar="#toolbar">
        
				<thead>

					<tr>
						<th class="noprint" data-field="state" data-checkbox="true"></th>    
						<th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Idrometro</th>
						<!--th data-field="tipo" data-sortable="true" data-visible="true" data-filter-control="select">Tipo</th-->
						<!--th data-field="id" data-sortable="true" data-visible="false" data-filter-control="select">Id</th-->
						<!--th data-field="perc_al_g" data-sortable="true" <?php if ($perc!='perc_al_g'){?> data-visible="false" <?php }?> data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta gialla" style="color:#ffd800;"></i></th>
						<th data-field="perc_al_a" data-sortable="true" <?php if ($perc!='perc_al_a'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percoso allerta arancione" style="color:#ff8c00;"></i></th>
						<th data-field="perc_al_r" data-sortable="true"  <?php if ($perc!='perc_al_r'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta rossa" style="color:#e00000;"></i></th>
						<th data-field="arancio" data-sortable="true" data-visible="false"> Liv arancione</th>
						<th data-field="rosso" data-sortable="true" data-visible="false" >Liv rosso</th-->
						<th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
						<th data-field="16" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora16;?></th>
						<th data-field="15" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora15;?></th>            
						<th data-field="14" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora14;?></th>
						<th data-field="13" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora13;?></th>
						<th data-field="12" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora12;?></th>            
						<th data-field="11" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora11;?></th>
						<th data-field="10" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora10;?></th>  
						<th data-field="9" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora9;?></th>
						<th data-field="8" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora8;?></th>
						<th data-field="7" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora7;?></th>
						<th data-field="6" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora6;?></th>
						<th data-field="5" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora5;?></th>            
						<th data-field="4" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora4;?></th>
						<th data-field="3" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora3?></th>  
						<th data-field="2" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora2;?></th>
						<th data-field="1" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora1;?></th>
						<!--th data-field="0" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora0;?></th-->
						<!--th class="noprint" data-field="id" data-sortable="false" data-formatter="nameFormatterInsert" data-visible="true">Edit</th-->
					</tr>
				</thead>
				</table><br>
				<h4>Valori Idrometri COMUNE </h4>
				<table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_idro_com_report.php" 
				data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
				data-search="true" data-click-to-select="true" data-show-print="true"  
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
				data-filter-control="true" data-toolbar="#toolbar">
        
				<thead>

					<tr>
						<th class="noprint" data-field="state" data-checkbox="true"></th>    
						<th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Idrometro</th>
						<!--th data-field="tipo" data-sortable="true" data-visible="true" data-filter-control="select">Tipo</th-->
						<!--th data-field="id" data-sortable="true" data-visible="false" data-filter-control="select">Id</th-->
						<!--th data-field="perc_al_g" data-sortable="true" <?php if ($perc!='perc_al_g'){?> data-visible="false" <?php }?> data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta gialla" style="color:#ffd800;"></i></th>
						<th data-field="perc_al_a" data-sortable="true" <?php if ($perc!='perc_al_a'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percoso allerta arancione" style="color:#ff8c00;"></i></th>
						<th data-field="perc_al_r" data-sortable="true"  <?php if ($perc!='perc_al_r'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta rossa" style="color:#e00000;"></i></th>
						<th data-field="arancio" data-sortable="true" data-visible="false"> Liv arancione</th>
						<th data-field="rosso" data-sortable="true" data-visible="false" >Liv rosso</th-->
						<th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
						<th data-field="16" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora16;?></th>
						<th data-field="15" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora15;?></th>            
						<th data-field="14" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora14;?></th>
						<th data-field="13" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora13;?></th>
						<th data-field="12" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora12;?></th>            
						<th data-field="11" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora11;?></th>
						<th data-field="10" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora10;?></th>  
						<th data-field="9" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora9;?></th>
						<th data-field="8" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora8;?></th>
						<th data-field="7" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora7;?></th>
						<th data-field="6" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora6;?></th>
						<th data-field="5" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora5;?></th>            
						<th data-field="4" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora4;?></th>
						<th data-field="3" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora3?></th>  
						<th data-field="2" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora2;?></th>
						<th data-field="1" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora1;?></th>
						<!--th data-field="0" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora0;?></th-->
						<!--th class="noprint" data-field="id" data-sortable="false" data-formatter="nameFormatterInsert" data-visible="true">Edit</th-->
					</tr>
				</thead>
				</table>
				<script>
// function nameFormatterInsert(value, row) {
// 	if(row.tipo != 'IDROMETRO COMUNE' && row.tipo != 'IDROMETRO ARPA'){
// 		return' <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#new_lettura'+value+'">\
// 		<i class="fas fa-search-plus" title="Aggiungi lettura per '+row.nome+'"></i></button> - \
// 		<a class="btn btn-info" href="mira.php?id='+value+'"> <i class="fas fa-chart-line" title=Visualizza ed edita dati storici></i></a>';
// 	} else if (row.tipo=='IDROMETRO ARPA') {
// 		return' <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#grafico_i_a'+value+'">\
// 		<i class="fas fa-chart-line" title="Visualizza grafico idro lettura per '+row.nome+'"></i></button>';
// 	 } else if (row.tipo=='IDROMETRO COMUNE') {
// 		return' <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#grafico_i_c'+value+'">\
// 		<i class="fas fa-chart-line" title="Visualizza grafico idro lettura per '+row.nome+'"></i></button>';
// 	 }
// }


function nameFormatterLettura(value,row) {
	if(row.tipo=='IDROMETRO ARPA' ){
		<?php
		$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_arpa WHERE cod='?>row.id<?php';";
		$result_soglie = pg_query($conn, $query_soglie);
		while($r_soglie = pg_fetch_assoc($result_soglie)) {
			$arancio=$r_soglie['liv_arancione'];
			$rosso=$r_soglie['liv_rosso'];
		}
		?>
		if(value < row.arancio ){
			return '<font style="color:#00bb2d;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.arancio && value < row.rosso) {
			return '<font style="color:#FFC020;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.rosso) {
			return '<font style="color:#cb3234;">'+Math.round(value*1000)/1000+'</font>';
		} else {
			return '-';
		}
	} else if(row.tipo=='IDROMETRO COMUNE'){
	//	return Math.round(value*1000)/1000;
		<?php
		$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_comune WHERE id='?>row.id<?php';";
		$result_soglie = pg_query($conn, $query_soglie);
		while($r_soglie = pg_fetch_assoc($result_soglie)) {
			$arancio=$r_soglie['liv_arancione'];
			$rosso=$r_soglie['liv_rosso'];
		}
		?>
		if(value < row.arancio ){
			return '<font style="color:#00bb2d;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.arancio && value < row.rosso) {
			return '<font style="color:#FFC020;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.rosso) {
			return '<font style="color:#cb3234;">'+Math.round(value*1000)/1000+'</font>';
		} else {
			return '-';
		}
	} else {
		if(value==1){
			return '<i class="fas fa-circle" title="Livello basso" style="color:#00bb2d;"></i>';
		} else if (value==2) {
			return '<i class="fas fa-circle" title="Livello medio" style="color:#ffff00;"></i>';
		} else if (value==3) {
			return '<i class="fas fa-circle" title="Livello alto" style="color:#cb3234;"></i>';
		} else {
			return '-';
		}
	}		
}

</script>
				
			</div>
			</div>
<?php } ?> <!-- chiudo if su tipo evento per mire -->
			
            <!-- /.row -->
            <div class="row">
                
                
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <?php
$date = date_create(date(), timezone_open('Europe/Berlin'));
$data = date_format($date, 'd-m-Y');
$ora = date_format($date, 'H:i');
//$data = date("d-m-Y");
//$ora = date("H:i:s");
	echo "<hr><div align='center'>Il presente report è stato ottenuto in maniera automatica utilizzando il Sistema 
	di Gestione delle Emergenze in data ".$data ." alle ore " .$ora.". 
	</div>";

?>
             </div>
            </div> <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>

<script>

	/*var mymap = L.map('mapid').setView([44.411156, 8.932661], 12);

	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
			'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox.streets'
	}).addTo(mymap);

	L.marker([44.411156, 8.932661]).addTo(mymap)
		.bindPopup("<b>Hello world!</b><br />I am a leafletJS popup.").openPopup();




	var popup = L.popup();

	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(mymap);
	}

	mymap.on('click', onMapClick);*/



  
$(document).ready(function() {
    $('#js-date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date2').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
      $('#js-date3').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date4').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
     $('#js-date5').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date6').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
      $('#js-date7').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date8').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    $('#js-date9').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date10').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    
    
    $('#js-date12').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date13').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date14').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
	$('#js-date15').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    
    $('#js-date100').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    }); 
});

</script>
    

</body>

</html>
