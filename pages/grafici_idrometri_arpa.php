<?php
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

				//echo strtotime("now");
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s');
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s')-3600;
				//echo "<br><br>";
				$now = new DateTime("now", new DateTimeZone('Europe/Rome'));
				//$date = $now->modify('-1 hour')->format('Y-m-d H:i:s');
				$date = $now->format('Y-m-d H:i:s');
				//$station='Montoggio';
				//echo $date;
				
				
				
				
				$query="SELECT name, shortcode FROM geodb.tipo_idrometri_arpa";
				if ($idrometro!=''){
					$query=$query ." WHERE shortcode='".$idrometro."' ";
				}
				$query=$query .";";
				//echo $query;
				$result = pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_arpa WHERE cod='".$r["shortcode"]."';";
					$result_soglie = pg_query($conn, $query_soglie);
					while($r_soglie = pg_fetch_assoc($result_soglie)) {
						$arancio=$r_soglie['liv_arancione'];
						$rosso=$r_soglie['liv_rosso'];
						$liv_max=$rosso+1;
					}
				?>
					<!-- 2. Add the JavaScript to initialize the chart on document ready -->
					<script type="text/javascript">
					// con questa riga faccio i grafici nel timezone CEST
					Highcharts.setOptions({
                    global: {
                        useUTC: false
                    }
                });
					Highcharts.getJSON('../vendor/omirl_data_ingestion/<?php echo $r["shortcode"];?>_Idro.json', function (data) {
					// Create the chart
					Highcharts.stockChart('grafico_<?php echo $r["shortcode"];?>', {


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
							text: '<?php echo $r["name"];?>'
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
							}]
						},
						series: [{
							name: '<?php echo $r["name"];?>',
							data: data,
							tooltip: {
								valueDecimals: 2
							}
						}]
					});
					});
					</script>
		
		
		
				
		
					<!-- 3. Add the container -->
					<div id="grafico_<?php echo $r["shortcode"];?>" style="width: 100%; height: 400px; margin: 0 auto"></div>
					<hr>		
							
				<?php
				}
				?>		