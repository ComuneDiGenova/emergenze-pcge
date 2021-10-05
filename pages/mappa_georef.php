<?php
// mappa per la geolocalizzazione
?>

<!-- Modal -->
  <div class="modal fade" id="myDialog" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Modal Header</h4>
        </div>
        <div class="modal-body">
          <p>Some text in the modal.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>


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

<script>







<?php
if ($zoom!=''){
?>	
	var mymap = L.map('mapid').setView([<?php echo $lat;?>, <?php echo $lon;?>], <?php echo $zoom;?>);
<?php
} else {
?>
	var mymap = L.map('mapid', {scrollWheelZoom:false}).setView([44.411156, 8.932661], 13);
<?php	
}
?>



	var mapbox= L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
			'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox.streets'
	});//.addTo(mymap);

var basemap2 = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors,<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>,Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>',
            maxZoom: 28
        });
        
        
var realvista = L.tileLayer.wms("https://mappe.comune.genova.it/realvista/reflector/open/service", {
                layers: 'rv1',
                format: 'image/jpeg',attribution: '<a href="http://www.realvista.it/website/Joomla/" target="_blank">RealVista &copy; CC-BY Tiles</a>.'
              });

var base_genova = L.tileLayer.wms("https://mappe.comune.genova.it/geoserver/ows?", {
                layers: 'MEDIATORE:BASE_CARTOGRAFICA',maxZoom: 22,
                format: 'image/jpeg',attribution: '<a href="https://geoportale.comune.genova.it/" target="_blank">Comune di Genova &copy; CC-BY Tiles</a>.'
              });              
              
// qua decido lo sfondo da precaricare
mymap.addLayer(basemap2);              
              
              
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
	
	
	
	
	//nominatim search
	var searchOpts = {
			url: 'https://nominatim.openstreetmap.org/search?format=json&q={s}&viewbox=8.65,44.35,9.15,44.53&bounded=1&countrycodes=IT',
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
		
		
		
		// WFS comunale search
		
		//https://mappe.comune.genova.it/geoserver/wfs?service=WFS&version=1.1.0&request=GetFeature&outputFormat=application%2Fjson&maxFeatures=20&typeName=SITGEO:V_ASTE_STRADALI_TOPONIMO_SUB
		/*var searchOpts = {
			url: 'https://mappe.comune.genova.it/geoserver/wfs?service=WFS&version=1.1.0&request=GetFeature&outputFormat=application%2Fjson&maxFeatures=20&typeName=SITGEO:V_ASTE_STRADALI_TOPONIMO_SUB',
			//jsonpParam: 'json_callback',
			formatData: formatJSON,
			zoom: 17,
			minLength: 2,
			autoType: false,
			marker: {
				icon: false,
				animate: false
			}
		};*/
		
	mymap.addControl( new L.Control.Search(searchOpts) );



var ourCustomControl = L.Control.extend({
 
  options: {
    position: 'topleft' 
    //control position - allowed: 'topleft', 'topright', 'bottomleft', 'bottomright'
  },
 
  onAdd: function (mymap) {
    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
 
    container.style.backgroundColor = 'white';     
    container.style.backgroundImage = "url(https://upload.wikimedia.org/wikipedia/commons/9/9f/%3Fuestionmark.svg)";
    //container.style.content = "\f000"; 
    //container.style.font.family= "Font Awesome 5 Free";
    container.style.backgroundSize = "30px 30px";
    container.style.width = '30px';
    container.style.height = '30px';
 
    container.onclick = function(){
      alert('Prima di tutto occorre selezionare il metodo con cui georeferenziare \
      la segnalazione. \
      \nQualora si scelga l\'opzione "tramite mappa" è possibile cliccare su un \
      punto della mappa per catturarne automaticamente le coordinate\
      \n Una volta cliccato sulla mappa è possibile spostare la localizzazione \
      della segnalazione cliccando nuovamente sulla mappa\
      nel punto desiderato.\
      \n\nNB Per effettuare una ricerca usare il tasto con la lente e digitare \
      il topononimo che si intende cercare.')
      //document.getElementById("myDialog").showModal();
    }
    return container;
  }
 
});




mymap.addControl(new ourCustomControl());



	var popup = L.popup();
	
	var marker;



	function onMapClick(e) {
		    document.getElementById('lat').value = e.latlng.lat.toString();
			 document.getElementById('lon').value = e.latlng.lng.toString();
		
		/*popup
			.setLatLng(e.latlng)
			.setContent("Le coordinate di questo punto sulla mappa sono le seguenti lat:" + e.latlng.lat.toString() +" e lon:"+ e.latlng.lng.toString() +" e sono state automaticamente inserite nel form")
			.openOn(mymap);*/
			
			popup
			.setLatLng(e.latlng)
			.setContent("Le coordinate di questo punto sulla mappa sono state automaticamente inserite nel form soprastante")
			.openOn(mymap);
			
			
			//var latlng = e.value.split(',');
	//alert(latlng);
		var lat = e.latlng.lat;
		var lng = e.latlng.lng;
		var zoom = 16;
		setTimeout(function() {
        mymap.closePopup();
    	}, 5000);
		// add a marker
		if (marker) { // check
        mymap.removeLayer(marker); // remove
    	}
		marker = L.marker([lat, lng],{}).addTo(mymap);
			
			
	}

$('input[type=radio][name=civrischio]').attr('disabled', true);
$('input[type=radio][name=oggrischio]').attr('disabled', true);
$('input[type=radio][name=oggrischio][value="f"]').attr('checked', true);
$('#tipo_oggetto').attr('disabled',true);


(function ($) {
    'use strict';
   
    $('[type="radio"][name="oggrischio"][value="t"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#tipo_oggetto').removeAttr('disabled');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
    
    $('[type="radio"][name="oggrischio"][value="f"]').on('change', function () {
        if ($(this).is(':checked')) {
        	   $('#tipo_oggetto').attr('disabled',true);
            $('#tipo_oggetto').val('');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
    
    
    
    $('select[name="tipo_segn"]').on('change', function () {

    	var others = $(this).val();
		//alert(others)
		if (others=="999"){
    		$('#altro').removeAttr('disabled');
    	} else {
    		$('#altro').attr('disabled', true);
    		$('#altro').val('');
    	}
        
    });
    
    
    
            
    $('[type="radio"][id="civico"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#via-list').removeAttr('disabled');
            $('#via-list').selectpicker('refresh');
            $('#civico-list').removeAttr('disabled');
            $('input[type=radio][name=oggrischio][value="t"]').attr('checked', false);
				$('input[type=radio][name=oggrischio][value="f"]').attr('checked', true);
				$('input[type=radio][name=oggrischio]').attr('disabled', true);
            $('input[type=radio][name=civrischio]').attr('disabled', false); 
            $('#lat').attr('disabled', true);
            $('#lon').attr('disabled', true);
            $("#collapse1").collapse('hide');
            $('#lat').val('');
            $('#lon').val('');
            $('#tipo_oggetto').attr('disabled',true);
            $('#tipo_oggetto').val('');
            $("#mapid").off("onclick");
            mymap.closePopup();
            if (marker) { // check
        			mymap.removeLayer(marker); // remove
    			}
            	mymap.off('click', onMapClick);
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
      $('[type="radio"][id="coord"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#lat').removeAttr('disabled');
            $('#lon').removeAttr('disabled');
            $('#lat').removeAttr('readonly');
            $('#lon').removeAttr('readonly');
            $('#via-list').val('');
            $('#civico-list').val('');
            $('#lat').val('');
            $('#lon').val('');
            $("#collapse1").collapse('hide');
            $('#via-list').attr('disabled', true);
            $('#via-list').selectpicker('refresh');
            $('#civico-list').attr('disabled', true);
            mymap.closePopup();
            if (marker) { // check
        			mymap.removeLayer(marker); // remove
    			}
            mymap.off('click', onMapClick);
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });  
    
    $('[type="radio"][id="mappa"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#lat').attr('readonly', true);
            $('#lon').attr('readonly', true);
            $('#lat').removeAttr('disabled');
            $('#lon').removeAttr('disabled');
            $("input[type=radio][name=oggrischio]").attr('disabled', false);
            $("input[type=radio][name=civrischio]").attr('disabled', true);            
            $('#lat').val('');
            $('#lon').val('');
            $('#via-list').val('');
            $("#collapse1").collapse('show');
            $('#collapse1').on('shown.bs.collapse', function (e) {
				    mymap.invalidateSize(true);
				});
            $('#civico-list').val('');
            $('#via-list').attr('disabled', true);
            $('#via-list').selectpicker('refresh');
            $('#civico-list').attr('disabled', true);
            var offset = -200; //Offset of 100px
            	mymap.on('click', onMapClick);

    $('html, body').animate({
        scrollTop: $("#mapid").offset().top + offset
    }, 2000);
            
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });  
    


$('[type="radio"][id="rich"]').on('change', function () {
        if ($(this).is(':checked')) {
            $("#richiesta").collapse('show');
            $("#segnalazione").collapse('hide'); 
            $('#crit').attr('disabled', true);
            $('#descrizione').attr('disabled', true);
            $('input[type=radio][name=georef][id="civico"]').prop('checked', false);
            $('input[type=radio][name=georef][id="mappa"]').prop('checked', false);
            $('input[type=radio][name=georef][id="coord"]').prop('checked', false);
            $('input[type=radio][name=georef]').attr('disabled', true);
            $('#via-list').attr('disabled', true);
            $('#via-list').selectpicker('refresh');
            $('#civico-list').attr('disabled', true);
            $('#lat').attr('disabled', true);
            $('#lon').attr('disabled', true);
            $("#collapse1").collapse('hide');
            $('form[name="form1"]').attr('action', 'segnalazioni/import_richiesta.php');

            $('#descrizione_richiesta').removeAttr('disabled');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });

$('[type="radio"][id="segn"]').on('change', function () {
        if ($(this).is(':checked')) {
            $("#richiesta").collapse('hide');
            $("#segnalazione").collapse('show');
            $('#crit').removeAttr('disabled');
            $('#descrizione').removeAttr('disabled');
            $('input[type=radio][name=georef]').removeAttr('disabled');
            $('#descrizione_richiesta').attr('disabled', true);
            $('form[name="form1"]').attr('action', 'segnalazioni/import_segnalazione.php');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });    
    
    
    
        /*$('[type="radio"][id="mappa"]').on('change', function () {
        if ($(this).is(':checked')) {
        	
        	
        	$('#my-modal').modal({
        show: 'false'
    }
            
        }
    });*/
    
    
    
}(jQuery));

    
var baseMaps = {'OpenStreetMap': basemap2, 'Realvista e-geos': realvista, 'Sfondo comunale': base_genova, 'OSM-Mapbox':mapbox};
        
        //legenda
        /*L.control.layers(baseMaps ,
        {collapsed:true}
        ).addTo(mymap);*/
		
		
		mymap.addControl( new L.control.layers(baseMaps ,  {},
        {collapsed:false}
        ) );
		
</script> 


<?php

?>