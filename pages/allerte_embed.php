<?php

?>

<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
			
			<?php
			$query="SELECT * FROM eventi.v_allerte WHERE id_evento=".$id.";";
			$result = pg_query($conn, $query);
			while($r = pg_fetch_assoc($result)) {	

				$timestamp = strtotime($r["data_ora_inizio_allerta"]);
				setlocale(LC_TIME, 'it_IT.UTF8');
				$data_start = strftime('%A %e %B %G', $timestamp);
				$ora_start = date('H:i', $timestamp);
				$timestamp = strtotime($r["data_ora_fine_allerta"]);
				$data_end = strftime('%A %e %B %G', $timestamp);
				$ora_end = date('H:i', $timestamp);								
				$color=str_replace("'","",$r["rgb_hex"]);
				//echo $color;
				//echo '<span class="dot" style="background-color:'.$color.'"></span>';
				//echo "<style> .fas { color: ".$color."; -webkit-print-color-adjust: exact;}</style>";
				echo "<i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"></i> <b>Allerta ".$r["descrizione"]."</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " <br>";
			}
			?>
			
 
			</div>	
			
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
			<?php
			$query="SELECT * FROM eventi.v_foc WHERE id_evento=".$id.";";
			$result = pg_query($conn, $query);
			while($r = pg_fetch_assoc($result)) {
				$timestamp = strtotime($r["data_ora_inizio_foc"]);
				setlocale(LC_TIME, 'it_IT.UTF8');
				$data_start = strftime('%A %e %B %G', $timestamp);
				$ora_start = date('H:i', $timestamp);
				$timestamp = strtotime($r["data_ora_fine_foc"]);
				$data_end = strftime('%A %e %B %G', $timestamp);
				$ora_end = date('H:i', $timestamp);
				$color=str_replace("'","",$r["rgb_hex"]);								
				echo "<i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"></i> <b> Fase di ".$r["descrizione"]."</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " <br>";
			}
			?>
			</div>
