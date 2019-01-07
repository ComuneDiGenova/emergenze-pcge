
<?php 
//require('./req.php');
?>



        
        
        
        
        <?php 

//require('./req_bottom.php');


	

?>


        <script src="l_map/js/qgis2web_expressions.js"></script>
        <!--script src="js/leaflet.js"></script><script src="js/L.Control.Locate.min.js"></script-->
        <script src="l_map/js/leaflet-svg-shape-markers.min.js"></script>
        <script src="l_map/js/leaflet.rotatedMarker.js"></script>
        <!--script src="l_map/js/leaflet.pattern.js"></script-->
        <script src="l_map/js/leaflet-hash.js"></script>
        <script src="l_map/js/Autolinker.min.js"></script>
        <script src="l_map/js/rbush.min.js"></script>
        <script src="l_map/js/labelgun.min.js"></script>
        <script src="l_map/js/labels.js"></script>
        <script src="l_map/js/leaflet-measure.js"></script>
        <script src="l_map/js/leaflet.markercluster.js"></script>
		<script src="../vendor//leaflet-search/src/leaflet-search.js"></script>
		<!--script src="out.json"></script-->
        <!--script src="l_map/data/v_segnalazioni_0.js"></script-->
		<script type="text/javascript">
        var highlightLayer;
        function highlightFeature(e) {
            highlightLayer = e.target;

            if (e.target.feature.geometry.type === 'LineString') {
              highlightLayer.setStyle({
                color: '#ffff00',
              });
            } else {
              highlightLayer.setStyle({
                fillColor: '#ffff00',
                fillOpacity: 1
              });
            }
        }
        /*var map = L.map('map', {
            zoomControl:true, maxZoom:28, minZoom:1
        }).fitBounds([[44.4069527187,8.86036251006],[44.4847013265,8.96496972789]]);*/
         
		 <?php
        if ($zoom!=''){
		?>	
		    var map = L.map('map').setView([<?php echo $lat;?>, <?php echo $lon;?>], <?php echo $zoom;?>),
		<?php
		} else {
		?>
        	var map = L.map('map').setView([44.441266, 8.912661], 12),
		<?php	
		}
		?>
    			createSquare = function (latlng, options) {
    			 	
        var point = map.latLngToContainerPoint(latlng),
            size = options.radius || options.size || 6,
            point1 = L.point(point.x - size*2/map.getZoom(), point.y - 1),
            point2 = L.point(point.x + size*2/map.getZoom(), point.y + 1),
            latlng1 = map.containerPointToLatLng(point1),
            latlng2 = map.containerPointToLatLng(point2);
        return new L.rectangle([latlng1, latlng2], options);
    }
        	
        	;
        
        map.scrollWheelZoom.disable();
        
        var hash = new L.Hash(map);
        //map.attributionControl.addAttribution('<a href="https://github.com/tomchadwin/qgis2web" target="_blank">qgis2web</a>');
        
        //L.control.locate().addTo(map);
        
        
        
        var measureControl = new L.Control.Measure({
            primaryLengthUnit: 'meters',
            secondaryLengthUnit: 'kilometers',
            primaryAreaUnit: 'sqmeters',
            secondaryAreaUnit: 'hectares'
        });
        measureControl.addTo(map);
        
        
        
        var bounds_group = new L.featureGroup([]);
        
        
        
        
		var realvista = L.tileLayer.wms("https://mappe.comune.genova.it/realvista/reflector/open/service", {
                layers: 'rv1',
                format: 'image/jpeg',attribution: '<a href="http://www.realvista.it/website/Joomla/" target="_blank">RealVista &copy; CC-BY Tiles</a> | <a href="http://openstreetmap.org">OpenStreetMap</a> contributors.'
              });

        var basemap2 = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors,<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>,Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>',
            maxZoom: 28
        });

        map.addLayer(basemap2);
        
        function setBounds() {
        }
        
        
        
       
        
        
		
		
		function formatJSON(rawjson) {	//callback that remap fields name
		var json = {},
			key, loc, disp = [];

		for(var i in rawjson)
		{
			disp = rawjson[i].display_name.split(',');	

			key = disp[0] +', '+ disp[1];
			
			loc = L.latLng( rawjson[i].lat, rawjson[i].lon );
			
			json[ key ]= loc;	//key,value format
		}
		
		return json;
	}
	
	var searchOpts = {
			url: 'https://nominatim.openstreetmap.org/search?format=json&q={s}&viewbox=8,44,9.00,44.9&bounded=1',
			jsonpParam: 'json_callback',
			formatData: formatJSON,
			zoom: 17,
			minLength: 2,
			autoType: false,
			marker: {
				icon: false,
				animate: false
			}
		};
		
	map.addControl( new L.Control.Search(searchOpts) );
	
	
	
	
	
        
        var segn_non_lav = [
        
        <?php 
        $query_g="SELECT id, ST_AsGeoJson(geom) as geo, rischio, criticita, descrizione, note FROM segnalazioni.v_segnalazioni WHERE id_lavorazione is null;";


			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
				} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}
				$i=$i+1;
			}
			?>
			];
        
        
        
        var segn_lav = [
        
        <?php 
        $query_g="SELECT id, ST_AsGeoJson(geom) as geo, rischio, criticita, descrizione, note FROM segnalazioni.v_segnalazioni WHERE id_lavorazione > 0 and in_lavorazione='t';";


			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
				} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}
				$i=$i+1;
			}
			?>
			];



			var sopralluoghi_no_segnalazione = [
        
        <?php 
        $query_g="SELECT id, ST_AsGeoJson(geom) as geo, descrizione_uo, descrizione FROM segnalazioni.v_sopralluoghi_last_update WHERE id_stato_sopralluogo < 3 ;";


			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id"].', "descrizione_uo": "';
					echo $r_g["descrizione_uo"].'",  "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
				} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "descrizione_uo": "';
					echo $r_g["descrizione_uo"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}
				$i=$i+1;
			}
			?>
			];
			
			
			
			var pc_punti = [
        
        <?php 
        $query_g="SELECT id, ST_AsGeoJson(geom_inizio) as geo, descrizione_uo, descrizione_stato, tipo_provvedimento, descrizione FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE geom is null ;";
			//echo $query_g;

			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id"].', "descrizione_uo": "';
					echo $r_g["descrizione_uo"].'",  "descrizione_stato": " '.str_replace('"',' ',$r_g["descrizione_stato"]);
					echo '",  "tipo_provvedimento": "'.str_replace('"',' ',$r_g["tipo_provvedimento"]);
					echo '", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
				} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "descrizione_uo": "';
					echo $r_g["descrizione_uo"].'",  "descrizione_stato": " '.str_replace('"',' ',$r_g["descrizione_stato"]);
					echo '",  "tipo_provvedimento": "'.str_replace('"',' ',$r_g["tipo_provvedimento"]);
					echo '", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}
				$i=$i+1;
			}
			?>
			];
			
			
			var pc_linee = [
        
        <?php 
        $query_g="SELECT id, ST_AsGeoJson(geom) as geo, descrizione_uo, descrizione_stato, tipo_provvedimento, descrizione FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE geom_inizio is null ;";
			//echo $query_g;

			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id"].', "descrizione_uo": "';
					echo $r_g["descrizione_uo"].'",  "descrizione_stato": " '.str_replace('"',' ',$r_g["descrizione_stato"]);
					echo '",  "tipo_provvedimento": "'.str_replace('"',' ',$r_g["tipo_provvedimento"]);
					echo '", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
				} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "descrizione_uo": "';
					echo $r_g["descrizione_uo"].'",  "descrizione_stato": " '.str_replace('"',' ',$r_g["descrizione_stato"]);
					echo '",  "tipo_provvedimento": "'.str_replace('"',' ',$r_g["tipo_provvedimento"]);
					echo '", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}
				$i=$i+1;
			}
			?>
			];
			
			

			/*var myStyle = {
			    color: 'red',
				fillColor: '#f03',
				fillOpacity: 0
			};
			
			
			L.geoJSON(geojsonFeature, {
			    style: myStyle
			}).addTo(map);*/
        
        
        var stile_non_lavorazione = {
		    radius: 8,
		    fillColor: "#FFD700",
		    color: "#000",
		    weight: 0.5,
		    opacity: 1,
		    fillOpacity: 0.8
		};
		
		var stile_lavorazione = {
		    radius: 8,
		    fillColor: "#228B22",
		    color: "#000",
		    weight: 0.5,
		    opacity: 1,
		    fillOpacity: 0.8
		};
		
		
		var stile_rischio = {
		    radius: 10,
		    fillColor: "#ff0000",
		    color: "#000",
		    weight: 0.5,
		    opacity: 1,
		    fillOpacity: 0.8
		};
		
		
		var stile_sopralluogo = {
		    radius: 6,
		    fillColor: "#0F5",
		    color: "#000",
		    weight: 0.5,
		    opacity: 1,
		    fillOpacity: 0.8
		};
		
		var stile_pc = {
		    radius: 6,
		    fillColor: "#F00",
		    color: "#000",
		    weight: 0.5,
		    opacity: 1,
		    fillOpacity: 0.8
		};
		
		
		var stile_pc_linea = {
	    "color": "red",
	    "weight": 5,
	    "opacity": 0.65
	};	
		
		
		
		/*var layer_v_segnalazioni_0 = new L.geoJson(geojsonFeature, {
		    pointToLayer: function (feature, latlng) {
		        return L.circleMarker(latlng, geojsonMarkerOptions);
		    }
		}).addTo(map);*/
        
		
		
		

        
        //var markers0 = L.markerClusterGroup();
        var markers1 = L.markerClusterGroup();
		  
		  var layer_v_segnalazioni_0 = L.geoJson(segn_non_lav, {
		    pointToLayer: function (feature, latlng) {
		        return L.circleMarker(latlng, stile_non_lavorazione);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:grey"><i class="fas fa-pause-circle"></i> Da prendere in carico </div>'+
				'<h4><b>Tipo</b>: '+
				feature.properties.criticita+'</h4>'+
				'<a class="btn btn-primary active" role="button" target="_new" href="./dettagli_segnalazione.php?id='+
				feature.properties.id +
				'"> Dettagli segnalazione </a>' );
			}
		});
		
		var layer_v_segnalazioni_1 = L.geoJson(segn_lav, {
		    pointToLayer: function (feature, latlng) {
		        return L.circleMarker(latlng, stile_lavorazione);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:grey"><i class="fas fa-play-circle"></i> In lavorazione </div>'+
				'<h4><b>Tipo</b>: '+
				feature.properties.criticita+'</h4>'+
				'<a class="btn btn-primary active" role="button" target="_new" href="./dettagli_segnalazione.php?id='+
				feature.properties.id +
				'"> Dettagli segnalazione </a>' );
			}
		});
		
		
		
		var layer_v_sopralluoghi = L.geoJson(sopralluoghi_no_segnalazione, {
		    pointToLayer: function (feature, latlng) {
		        //return createSquare(latlng, stile_sopralluogo); // rettangolo (vedi funzione definita sopra)
		        return L.circleMarker(latlng, stile_sopralluogo);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:grey"><i class="fas fa-pencil-ruler"></i> Sopralluogo </div>'+
				'<h4><b>Squadra</b>: '+
				feature.properties.descrizione_uo+'</h4>'+
				'<h4><b>Descrizione</b>: '+
				feature.properties.descrizione+'</h4>'+
				'<a class="btn btn-info active" role="button" target="_new" href="./dettagli_sopralluogo.php?id='+
				feature.properties.id +
				'"> Dettagli sopralluogo </a>' );
			}
		});
		
		
		
		var layer_v_pc = L.geoJson(pc_punti, {
		    pointToLayer: function (feature, latlng) {
		        //return createSquare(latlng, stile_sopralluogo); // rettangolo (vedi funzione definita sopra)
		        return L.circleMarker(latlng, stile_pc);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:grey"><i class="fas fa-pencil-ruler"></i>' +feature.properties.tipo_provvedimento+ '</div>'+
				'<h4><b>Squadra</b>: '+
				feature.properties.descrizione_uo+'</h4>'+
				'<h4><b>Stato</b>: '+
				feature.properties.descrizione_stato+'</h4>'+
				'<h4><b>Descrizione</b>: '+
				feature.properties.descrizione+'</h4>'+
				'<a class="btn btn-info active" role="button" target="_new" href="./dettagli_provvedimento_cautelare.php?id='+
				feature.properties.id +
				'"> Dettagli PC </a>' );
			}
		});
		
		var layer_v_pc_linee = L.geoJson(pc_linee, {
		    style:stile_pc_linea,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:grey"><i class="fas fa-pencil-ruler"></i>' +feature.properties.tipo_provvedimento+ '</div>'+
				'<h4><b>Squadra</b>: '+
				feature.properties.descrizione_uo+'</h4>'+
				'<h4><b>Stato</b>: '+
				feature.properties.descrizione_stato+'</h4>'+
				'<h4><b>Descrizione</b>: '+
				feature.properties.descrizione+'</h4>'+
				'<a class="btn btn-info active" role="button" target="_new" href="./dettagli_provvedimento_cautelare.php?id='+
				feature.properties.id +
				'"> Dettagli PC </a>' );
			}
		});
		
		//markers0.addLayer(layer_v_segnalazioni_0);
		//map.addLayer(markers0);
		map.addLayer(layer_v_segnalazioni_0);
		
		markers1.addLayer(layer_v_segnalazioni_1);
		map.addLayer(markers1);
		
		
		map.addLayer(layer_v_sopralluoghi);
    
    	var pc = L.layerGroup([layer_v_pc, layer_v_pc_linee]);

      //map.addLayer(layer_v_pc);   
      //map.addLayer(layer_v_pc_linee);  
		map.addLayer(pc);
		
		
		<?php

 if ($descrizione_oggetto_rischio=='Sottopassi'){

?>

var sottopasso = [
        
        <?php 
        $query_g="SELECT id_crit, ST_AsGeoJson(st_transform(geom,4326)) as geo, nome_crit, tipo_crit, note_crit, rischio_idro, gestore FROM geodb.sottopassi WHERE id_crit =".$id_oggetto_rischio.";";


			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				//if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id_crit"].', "rischio": "';
					echo $r_g["rischio_idro"].'", "nome": "'.$r_g["nome_crit"].'","tipo": "'.$r_g["tipo_crit"].'", "note": "'.preg_replace('#\R+#',' ',str_replace('"',' ',$r_g["note_crit"])).'"},"geometry":';
					echo $r_g["geo"].'}';
				/*} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}*/
				$i=$i+1;
			}
			?>
			];
			
			
			
			

		var layer_v_sottopassi = L.geoJson(sottopasso, {
		    pointToLayer: function (feature, latlng) {
		        //return createSquare(latlng, stile_sopralluogo); // rettangolo (vedi funzione definita sopra)
		        return L.circleMarker(latlng, stile_rischio);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:red"><i class="fas fa-exclamation-triangle"></i> Sottopasso </div>'+
				'<b>Nome</b>: '+
				feature.properties.nome+'<br>'+
				'<b>Tipo</b>: '+
				feature.properties.tipo+'<br>'+
				'<b>Note</b>: '+
				feature.properties.note+'<br>'+
				'<b>Rischio</b>: '+
				feature.properties.rischio+'<br>');
				//'<a class="btn btn-info active" role="button" target="_new" href="./dettagli_sopralluogo.php?id='+
				//feature.properties.id +
				//'"> Dettagli sopralluogo </a>'+
				
			}
		});
		
		
		
		map.addLayer(layer_v_sottopassi);

		<?php

 } else if($descrizione_oggetto_rischio=='Edifici') {

?>
 
var edificio = [
        
        <?php 
        $query_g="SELECT id_oggetto, ST_AsGeoJson(st_centroid(st_transform(geom,4326))) as geo, superficie, tipo FROM geodb.edifici WHERE id_oggetto =".$id_oggetto_rischio.";";


			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				//if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id_oggetto"].', "superficie": "';
					echo $r_g["superficie"].'", "tipo": "'.preg_replace('#\R+#',' ',str_replace('"',' ',$r_g["tipo"])).'"},"geometry":';
					echo $r_g["geo"].'}';
				/*} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}*/
				$i=$i+1;
			}
			?>
			];
			
			
			
			

		var layer_v_edifici = L.geoJson(edificio, {
		    pointToLayer: function (feature, latlng) {
		        //return createSquare(latlng, stile_sopralluogo); // rettangolo (vedi funzione definita sopra)
		        return L.circleMarker(latlng, stile_rischio);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:red"><i class="fas fa-exclamation-triangle"></i> Edificio a rischio </div>'+
				'<b>Id</b>: '+
				feature.properties.id+'<br>'+
				'<b>Superficie </b>: '+
				feature.properties.superficie+' mq<br>'+
				'<b>Tipo</b>: '+
				feature.properties.tipo+'<br>');
				//'<a class="btn btn-info active" role="button" target="_new" href="./dettagli_sopralluogo.php?id='+
				//feature.properties.id +
				//'"> Dettagli sopralluogo </a>'+
				
			}
		});
		
		
		
		map.addLayer(layer_v_edifici);

<?php 

		 } else if($descrizione_oggetto_rischio=='Civici') {

?>
 
var civico = [
        
        <?php 
        $query_g="SELECT id, ST_AsGeoJson(st_centroid(st_transform(geom,4326))) as geo, desvia, testo FROM geodb.civici WHERE id =".$id_oggetto_rischio.";";
		//echo $query_g;

			// GeoJson Postgis: {"type":"Point","coordinates":[8.90092674245687,44.4828501691802]}
			

    		$i=0;
			$result_g = pg_query($conn, $query_g);
	      while($r_g = pg_fetch_assoc($result_g)) {
				//if ($i==0){ 
					echo '{"type": "Feature","properties": {"id":'.$r_g["id"].', "numero": "';
					echo $r_g["testo"].'", "via": "'.preg_replace('#\R+#',' ',str_replace('"',' ',$r_g["desvia"])).'"},"geometry":';
					echo $r_g["geo"].'}';
				/*} else {
					//echo ",". $r_g["geo"];
					echo ',{"type": "Feature","properties": {"id":'.$r_g["id"].', "rischio": "';
					echo $r_g["rischio"].'", "criticita": "'.$r_g["criticita"].'", "descrizione": "'.str_replace('"',' ',$r_g["descrizione"]).'"},"geometry":';
					echo $r_g["geo"].'}';
					
				}*/
				$i=$i+1;
			}
			?>
			];
			
			
			
			

		var layer_v_civici = L.geoJson(civico, {
		    pointToLayer: function (feature, latlng) {
		        //return createSquare(latlng, stile_sopralluogo); // rettangolo (vedi funzione definita sopra)
		        return L.circleMarker(latlng, stile_rischio);
		    }
		    ,
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<div align="right" style="color:red"><i class="fas fa-exclamation-triangle"></i> Civico a rischio </div>'+
				'<b>Id</b>: '+
				feature.properties.id+'<br>'+
				'<b>Via </b>: '+
				feature.properties.via+' mq<br>'+
				'<b>Numero</b>: '+
				feature.properties.numero+'<br>');
				//'<a class="btn btn-info active" role="button" target="_new" href="./dettagli_sopralluogo.php?id='+
				//feature.properties.id +
				//'"> Dettagli sopralluogo </a>'+
				
			}
		});
		
		
		
		map.addLayer(layer_v_civici);
		


		// 




		<?php

 } 

?> 
        
        
        
        var baseLayers = {
        		'Humanitarian OpensStreetMap': basemap2, 
        		'Realvista e-geos': realvista
        	};
        
        var overlayLayers = {'Segnalazioni non ancora in lavorazione': layer_v_segnalazioni_0,
        'Segnalazioni in lavorazione': markers1,
        'Altri sopralluoghi': layer_v_sopralluoghi,
        'Provvedimenti cautelari':pc
        }
        
        //legenda
        L.control.layers(baseLayers,overlayLayers,
        {collapsed:true}
        ).addTo(map);
        
        setBounds();
        </script>


