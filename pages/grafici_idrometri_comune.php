<?php
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

				//echo strtotime("now");
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s');
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s')-3600;
				//echo "<br><br>";
				$now = new DateTime();
				$date = $now->modify('-1 hour')->format('Y-m-d H:i:s');
				//$station='Montoggio';
				//echo $date;
				
				
				
				
				$query="SELECT nome, id FROM geodb.tipo_idrometri_comune";
				if ($idrometro!=''){
					$query=$query ." WHERE id='".$idrometro."' ";
				} else {
					$query=$query ." WHERE usato='t' ";
				}
				$query=$query .";";
				//echo $query;
				$result = pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_comune WHERE id='".$r["id"]."';";
					$result_soglie = pg_query($conn, $query_soglie);
					while($r_soglie = pg_fetch_assoc($result_soglie)) {
						$arancio=max($r_soglie['liv_arancione'],0);
						$rosso=max($r_soglie['liv_rosso'],0);
						$liv_max=max($rosso,0)+1;
					}
					
				?>
					<!-- 2. Add the JavaScript to initialize the chart on document ready -->
					<script type="text/javascript">
					Highcharts.getJSON('./eventi/json_idrometro.php?id=<?php echo $r["id"];?>', function (data) {
					// Create the chart
					Highcharts.stockChart('grafico_<?php echo $r["id"];?>', {


						rangeSelector: {
							inputEnabled: true,
							selected: 1,
							buttons: [{
								type: 'day',
								count: 1,
								text: '1g'
							}, {
								type: 'day',
								count: 3,
								text: '3gg'
							}, {
								type: 'week',
								count: 1,
								text: '7gg'
							}, {
								type: 'day',
								count: 10,
								text: '10gg'
							}, {
								type: 'day',
								count: 14,
								text: '14gg'
							}/*, {
								type: 'all',
								text: 'Tutti'
							}*/],
							inputDateFormat:'%d/%m/%Y',
							inputEditDateFormat:'%d/%m/%Y'
						},
						/*rangeSelector: {
							selected: 1
						},*/

						title: {
							text: '<?php echo $r["nome"];?>'
						},
						yAxis: {
							title: {
								text: 'Livello idrometrico[m]'
							},
							max:<?php echo $liv_max;?>,
							plotLines: [{
								value: <?php echo $arancio;?>,
								color: '#FFC020',
								dashStyle: 'shortdash',
								width: 2,
								label: {
									text: 'Soglia arancione'
								}
							}, {
								
								color: '#FF0000',
								dashStyle: 'shortdash',
								width: 2,
								label: {
									text: 'Soglia rossa'
								},
								value: <?php echo $rosso;?>
							}
							]
							//max:5
						},
						series: [{
							name: '<?php echo $r["nome"];?>',
							data: data,
							tooltip: {
								valueDecimals: 2
							}
						}]
					});
					});
					</script>
		
		
		
				
		
					<!-- 3. Add the container -->
					<div id="grafico_<?php echo $r["id"];?>" style="width: 100%; height: 400px; margin: 0 auto"></div>
					<hr>		
							
				<?php
				}
				?>		