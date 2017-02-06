<?php
$version = "2.7.0";

include_once("../library.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title>Feinstaub in Stuttgart – Karte</title>
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta property="og:title" content="Feinstaub in Stuttgart">
		<meta property="og:description" content="Hier finden Sie die OpenData-Feinstaubmessungen von OK Lab Stuttgart als Karte aufbereitet.">
		<meta property="og:image" content="<?php echo $url; ?>/map/map.png">
		<meta property="og:url" content="<?php echo $url; ?>/map/">
		<!--<script src="/feinstaub/js/d3.v4.min.js" type="text/javascript"></script>-->
		<script src="../js/jquery.min.js" type="text/javascript"></script>
		<script src="../js/jquery-ui.min.js" type="text/javascript"></script>
		<script src="../js/ol.js" type="text/javascript"></script>
 		<script src="../library.js" type="text/javascript"></script>
		<link rel="stylesheet" href="../js/ol.css" type="text/css" media="all">
		<link rel="stylesheet" href="../styles.css" type="text/css" media="all">
		<!--
		Change Log
		
		ToDo
		- include LUBW
		- reduce size (1,35MB)
		
		2.7.0
		Data from MySQL
		
		2.6.0
		Code cleanup
		Color mapping switchable
		
		2.5.0
		non public version
		Air Quality Index
		
		2.4.0
		mobile friendly
		
		2.3.0
		corporate design
		resizable
		
		2.2.0
		switch transparency
		switch 24h-floating - most recent
		
		2.1.0
		minor bugfixes
		
		2.0.0
		update OpenLayers
		show districts
		show recent data
		
		1.0.0
		first public release
		
		-->
		<style>
			body{
				font-size: 0.9em !important;
			}
			#mapdiv{
				width: 100%;
				height: 100%;
				position: relative;
				float: right;
			}
			#ranking{
				width: 150px;
				height: 100%;
				position: relative;
				float: left;
				text-align: center;
			}
			.longitude, .latitude{
				display: none;
			}
			.ranking_row tr td{
				cursor: pointer;
			}
			#ranking_table{
				padding: 5px;
				width: 100%;
			}
			#mapinfo{
				position: absolute;
				bottom: 0px;
				padding: 5px;
				width: 160px;
				background-color: #fff;
			}
			#mapinfo2{
				position: absolute;
				bottom: 0px;
				padding: 5px;
				width: 160px;
				background-color: #fff;
				display: none;
				height: auto;
				color: #000000;
				font-size: 0.8em;
			}
			.ol-attribution img{
				max-height: 1.6em;
			}
			div.ol-zoom{
				right: 0.5em;
				bottom: 3em;
				left: initial;
				top: initial;
			}
			@media (max-device-width: 1000px) {
				#mapinfo2{
					width: 280px;
					font-size: 1em;
				}
			}
		</style>
	</head>
	<body>
		<div style="width:100%;height:100%;">
			<div id="mapdiv"></div>
	<div id="controlBar2Button" class="bar2Button shadow bgcolor"><div class="bar2ButtonText">Einstellungen</div></div>
	<div id="infoBar2Button" class="bar2Button shadow bgcolor"><div class="bar2ButtonText">Info</div></div>
    <div id="controlBar2" class="Bar2 shadow">
      <div class="Bar2Header bgcolor">
        <h1>Feinstaub in Stuttgart</h1>
					<form>
						<label>
						  Stadtbezirke
						  <select id="district-blend-mode" class="form-control">
							<option value="0">ausgeblendet</option>
							<option value="0.5" selected="selected">durchscheinend</option>
							<option value="1.0">vollfarbig</option>
						  </select>
						</label><br/>
						<label>
						  Sensoren
						  <select id="sensor-blend-mode" class="form-control">
							<option value="0">ausgeblendet</option>
							<option value="1.0" selected="selected">vollfarbig</option>
						  </select>
						</label><br/>
						<label>
						  Farbtabelle
						  <select id="color-mode" class="form-control">
							<option value="AQI" selected="selected">Air Quality Index</option>
							<option value="GreenRedPink">Grün-Rot-Pink</option>
						  </select>
						</label><br/>
						  Datenbasis für Bezirke
						  <select id="data-source" class="form-control">
							<option value="P10floating">P10 – 24h-Mittelwert</option>
							<option value="P10" selected="selected">P10 – aktuelles Stundenmittel</option>
							<option value="P25floating">P2.5 – 24h-Mittelwert</option>
							<option value="P25">P2.5 – aktuelles Stundenmittel</option>
						  </select>
						</label>
					</form>
      </div>
    </div>
    <div id="infoBar2" class="Bar2 shadow">
      <div class="Bar2Header bgcolor">
        <h1 style="margin-bottom: 0;">Feinstaub in Stuttgart</h1>
      </div>
      <div class="Bar2Footer">
        <iframe src="../help/?context=map"></iframe>
      </div>
	</div>

			<div id="mapinfo2" class="shadow"></div>
			<span id="copyright">Version <?php echo $version; ?> | Daten: <span id="timestamp"></span></span>
		</div>
	</body>
 <script>
	
	var view = new ol.View({
		  projection: 'EPSG:3857',
		  maxZoom: 13,
		  center: ol.proj.fromLonLat([9.193, 48.786]),
		  zoom: 11
		  
	});
	



// Get Stuttgart Sensors
var jsonSensors = <?php echo file_get_contents($data_root."stuttgart_sensors_v2.json");?>;


      var vectorSource = new ol.source.Vector({
        features: (
				   new ol.format.GeoJSON()
				   )
					.readFeatures(
						jsonSensors,
						{
							featureProjection: 'EPSG:3857'
						}
					)
      });

      var vectorSensors = new ol.layer.Vector({
        source: vectorSource,
        style: styleFunctionAQIPM10
      });	  

	// Get Stuttgart Geodata
	var jsonDistricts = <?php echo file_get_contents($data_root."stuttgart_districts_v2.json");?>;

	var districtsTimestamp = <?php echo filemtime($data_root."stuttgart_districts_v2.json"); ?>;
	var districtsDateTime = "<?php echo date("d.m.Y, H:i",filemtime($data_root."stuttgart_districts_v2.json")); ?>";
	
	var vectorSourceStuttgart = new ol.source.Vector({
	  features: (
				 new ol.format.GeoJSON()
				 )
				  .readFeatures(
					  jsonDistricts,
					  {
						  featureProjection: 'EPSG:3857'
					  }
				  )
	});

      var vectorDistricts = new ol.layer.Vector({
        source: vectorSourceStuttgart,
        style: styleFunctionAQIPM10,
		opacity: 0.5
      });
	  
      var geoguniheidelbergMapLayer = new ol.layer.Tile({
        source: new ol.source.OSM({
			attributions: [
				'<a href=\"http://luftdaten.info/\" target=\"_blank\">luftdaten.info</a>',
				'<a href=\"http://korona.geog.uni-heidelberg.de/\" target=\"_blank\">Uni Heidelberg</a>',
				ol.source.OSM.ATTRIBUTION
			],
			//url: '/osm/?g=1&x={x}&y={y}&z={z}'
			url: '/osm/cache/gray-{x}-{y}-{z}.png'
        }),
		saturation: 0,
		opacity: 1
      });
	  
	  //geoguniheidelbergMapLayer.setSaturation(0);

      var map = new ol.Map({
		layers: [
          geoguniheidelbergMapLayer,
		  /*vectorDistrictsFloating,*/
		  vectorDistricts,
		  /*vectorDistricts25Floating,
		  vectorDistricts25,*/
		  vectorSensors
        ],
        target: 'mapdiv',
        controls: ol.control.defaults({
          attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
            collapsible: true
          }),
        }),/*.extend([
          new ol.control.FullScreen()
        ])*/
        view: view
      });
	  
// interaction
	  
var target = map.getTarget();
        var jTarget = typeof target === "string" ? $("#" + target) : $(target);

if(!isMobile){
	map.on("click", function(e) {interaction_click(e);});
	map.on("pointermove", function(e) {interaction_hover(e);});
}
else{
	//map.on("dblclick", function(e) {interaction_click(e);});
	map.on("click", function(e) {interaction_hover(e);});
}

function interaction_click(e){
	//alert("interaction_click");
	map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
			if(feature.getGeometry().getType()=="Point"){
				url = "https://www.madavi.de/sensor/archiv_luftdaten_info/graph.php?sensor="+feature.get("name");
				window.open(url);
			}
			else{
				if(feature.getGeometry().getType()=="Polygon"){
					url = "https://www.madavi.de/sensor/archiv_luftdaten_info/graph.php?sensor="+encodeURIComponent(feature.get("name"));
					//alert(url);
					//window.open(url);
				}
			}
	});
}
function interaction_hover(e){
	var out = "";
	var point = false;
	var districts_crossed = [];
	var hover_something = false;
    map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
        //alert(feature.getGeometry().getType());
		if(feature.getGeometry().getType()=="Point"){
			hover_something = true;
			if(feature.get("P1") === undefined){
				P1 = "kein aktueller Wert";
			}
			else{
				P1 = Math.round(feature.get("P1"))+" µg/m³ ";
			}
			out+="<br/><a href=\"https://www.madavi.de/sensor/archiv_luftdaten_info/graph.php?sensor="+feature.get("name")+"\" target=\"_blank\">Sensor "+feature.get("name")+"</a> – PM10: "+P1;
			point = true;
		}
		else{
			
			if(feature.getGeometry().getType()=="Polygon"){
				hover_something = true;
				district = "";
				if(feature.get("name")!=""){
					if($.inArray(feature.get("name"),districts_crossed)){
						districts_crossed.push(feature.get("name"));
						district = "<strong>"+feature.get("name")+"</strong><br/>";
						if(feature.get("Num_Sensors")>0){
							district+="Median P10: "+Math.round(feature.get("P1"))+" µg/m³<br/>";
							district+="24h-Mittel P10: "+Math.round(feature.get("P1floating"))+" µg/m³<br/>";
							district+="Median P2.5: "+Math.round(feature.get("P2"))+" µg/m³<br/>";
							district+="24h-Mittel P2.5: "+Math.round(feature.get("P2floating"))+" µg/m³<br/>";
							district+="("+feature.get("Num_Sensors")+" Sensor[en]: "+feature.get("Sensor_IDs")+")";
						}
						else{
							district+="keine Daten vorhanden";
						}
						out=district+"<br/>"+out;
					}
				}
			}
		}
    });
	if(point) jTarget.css("cursor", "pointer");
	else jTarget.css("cursor", "");
	$("#mapinfo2").html( "<div id=\"innerInfo\">"+out+"</div>" );
	if(hover_something){
		//alert(e.pixel);
		//document.getElementById("#mapinfo2").style.left = e.pixel[0]+"px";
		//document.getElementById("#mapinfo2").style.top = e.pixel[1]+"px";
		$("#mapinfo2").css({top: e.pixel[1]+20, left: e.pixel[0]-70, height: $("#innerInfo").height()}).show();
		/*$( "#mapinfo2" ).position({
		  my: "left+3 bottom-3",
		  of: e,
		  collision: "fit"
		});*/
	}
	else
		$("#mapinfo2").hide();
}

function map_center(lon,lat){
	map.getView().setCenter(ol.proj.fromLonLat([lon, lat]));
}
	
	$( "tr.ranking_row" )
		.mouseenter(function() {
			map_center(parseFloat($("td.longitude:first",this).text()),parseFloat($("td.latitude:first",this).text()));
		});

    // Get the form elements and bind the listeners
	var select_district_blend_mode = document.getElementById('district-blend-mode');
	var select_sensor_blend_mode = document.getElementById('sensor-blend-mode');
	var select_color_mode = document.getElementById('color-mode');
	var select_source = document.getElementById('data-source');
	  
    // Rerender map when blend mode changes
	select_district_blend_mode.addEventListener('change', function() {
	  vectorDistricts.setOpacity(select_district_blend_mode.value);
	  map.render();
	});
	select_sensor_blend_mode.addEventListener('change', function() {
	  vectorSensors.setOpacity(select_sensor_blend_mode.value);
	  map.render();
	});
	select_source.addEventListener('change', function() {
		change_colormapping();
	});
	select_color_mode.addEventListener('change', function() {
		change_colormapping();
	});
	function change_colormapping(){
		var function_name = "styleFunction";
		function_name = function_name+select_color_mode.value+select_source.value;
		vectorSensors.setStyle(eval(function_name.replace("floating", "")));
		vectorDistricts.setStyle(eval(function_name));
		map.render();
	}
	$(document).ready(function(){
		var d = new Date();
		d.setSeconds(districtsTimestamp);
		$("#timestamp").html(districtsDateTime);
	});
	function resize(){
		
	}
  </script>

</html>
