<button type="button" class="btn btn-info" data-toggle="modal" data-target="#download">
			Geoservizi WMS e WFS <i class="fa fa-map"></i></button>

			<div class="modal fade" id="download" role="dialog"> 
				<div class="modal-dialog"> 
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal">&times;</button>
					  <h4 class="modal-title">Download dati</h4>
					</div>
					<div class="modal-body">
					        <h4> <i class="fa fa-map"></i>  OWS GeoWebService </h4>
							In questa sezione sono disponibili i servizi WMS e WFS messi a disposizione dal Comune di Genova 
							attraverso il proprio geoportale. Si tratta di geoservizi standard che consentono di fruire della 
							cartografia prodotta dal Comune. 
							<br><b>Solo da rete locale del Comune</b> tra gli altri, sono disponibili 
							anche i geoservizi delle segnalazioni. 

							<ul> 
							<li> Il servizio WMS (Web Map Service) è una specifica tecnica standard per la visualizzazione di mappe su Internet, 
							fornite da un server in seguito ad una richiesta interattiva. La risposta alla richiesta è una o più immagini di mappa 
							(nel formato JPEG, PNG, ...) che può essere mostrata in un browser Internet. 
							Con il servizio WMS a disposizione si può sovrapporre la cartografia comunale ad altri livelli informativi (mappe) 
							creati all’interno di un ente o di un professionista.</li>

							<li> Il servizio WFS (Web Feature Service) è un servizio standard che permette la richiesta, l’analisi e/o l'importazione
							di oggetti geografici da uno o più server distribuiti in Internet.
							I meccanismi di richiesta e risposta sono simili al WMS, con la differenza che non vengono restituite immagini,
							bensì dati sugli oggetti spaziali (coordinate spaziali ed eventuali attributi alfanumerici) attraverso il Web.
							Il servizio  WFS consente la fruizione diretta e lo scarico dei dati relativi ai livelli informativi della cartografia
							comunale.</li>
							</ul>
<div style="text-align: center">
<a class="btn btn-success" role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
  <i class="fa fa-link"></i> WMS Link</a>
</a>
<a class="btn btn-success" role="button" data-toggle="collapse" href="#collapseExample2" aria-expanded="false" aria-controls="collapseExample">
  <i class="fa fa-link"></i> WFS Link</a>
</a>
<div class="collapse" id="collapseExample">
  <div class="well">
    Copia il seguente link per accedere ai geowebservice WMS del Comune di Genova:<br>
    <input readonly="readonly" id="wms" size="50" value="https://mappe.comune.genova.it/geoserver/wms?"><br><button class="btn" onclick="copybuttonwms()">Copia Url</button>    
  </div>
</div>
<div class="collapse" id="collapseExample2">
  <div class="well">
    Copia il seguente link per accedere ai geowebservice WFS del Comune di Genova:<br><!--a href="http://sit.comune.vicenza.it/geoserver/Internet_VI/wfs?" target="_blank"> http://sit.comune.vicenza.it/geoserver/Internet_VI/wfs?</a-->
    <input readonly="readonly" id="wfs" size="50" value="https://mappe.comune.genova.it/geoserver/wfs?"><br><button class="btn" onclick="copybuttonwfs()">Copia Url</button>
    

  </div>
</div>

</div>

						
						
					</div>
				  </div>
				</div>
			  </div>
			</div>