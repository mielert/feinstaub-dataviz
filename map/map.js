var view = new ol.View({
	  projection: 'EPSG:3857',
	  maxZoom: 13,
	  center: ol.proj.fromLonLat([9.193, 48.786]),
	  zoom: 11
});
var jsonDistricts = "";
var jsonSensors = "";
var map = "";
var vectorDistricts = "";
var vectorSensors = "";

var mapScaleId = "#mapscale";
var mapScaleOrientation = "vertical";
var mapScaleWidth = 40;
var mapScaleHeight = 200;
var mapScaleLut = colorLookupTableAQIPM10;

scaleComplex(mapScaleId,mapScaleOrientation,mapScaleWidth,mapScaleHeight,mapScaleLut);

//getAjax('http://foo.bar/?p1=1&p2=Hello+World', function(data){
//    var json = JSON.parse(data);
var xhr = $.get( "../data/stuttgart_districts_v2.json", function( data ) {
	console.log("stuttgart_districts_v2.json loaded");
	jsonDistricts = data;
	$.get( "../data/stuttgart_sensors_v2.json", function( sensordata ) {
		console.log("stuttgart_sensors_v2.json loaded");
		jsonSensors = sensordata;
	
		var vectorSourceDistricts = new ol.source.Vector({
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
	
		vectorDistricts = new ol.layer.Vector({
		  source: vectorSourceDistricts,
		  style: styleFuntionOpenLayers,
		  opacity: 0.5
		});
	
	    var vectorSourceSensors = new ol.source.Vector({
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

		vectorSensors = new ol.layer.Vector({
		  source: vectorSourceSensors,
		  style: styleFuntionOpenLayers
		});
		
		var geoguniheidelbergMapLayer = new ol.layer.Tile({
			source: new ol.source.OSM({
				attributions: [
					'<a href=\"http://luftdaten.info/\" target=\"_blank\">luftdaten.info</a>',
					'<a href=\"http://korona.geog.uni-heidelberg.de/\" target=\"_blank\">Uni Heidelberg</a>',
					ol.source.OSM.ATTRIBUTION
				],
				url: '/osm/cache/gray-{x}-{y}-{z}.png'
			}),
			saturation: 0,
			opacity: 1
		});
  
		map = new ol.Map({
		  layers: [
			geoguniheidelbergMapLayer,
			vectorDistricts,
			vectorSensors
		  ],
		  target: 'mapdiv',
		  controls: ol.control.defaults({
			zoom: false,
                  attribution: false /*Options: ({
			  collapsible: true
			}),*/
		  }),
		  view: view
		});
		// interaction
		var target = map.getTarget();
            console.log("typeof target: "+typeof(target));
            var jTarget = typeof target === "string" ? $("#" + target) : $(target);
		if(!isMobile){
			map.on("click", function(e) {interaction_click(e);});
			map.on("pointermove", function(e) {interaction_hover(e);});
		}
		else{
			map.on("click", function(e) {interaction_hover(e);});
		}
		
		function interaction_click(e){
			map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
					if(feature.getGeometry().getType()=="Point"){
						url = "https://www.madavi.de/sensor/archiv_luftdaten_info/graph.php?sensor="+feature.get("name");
						window.open(url);
					}
					else{
						if(feature.getGeometry().getType()=="Polygon"){
							url = "https://www.madavi.de/sensor/archiv_luftdaten_info/graph.php?sensor="+encodeURIComponent(feature.get("name"));
						}
					}
			});
		}
		function interaction_hover(e){
			var out = "";
                  var out_sensors = "";
                  var district = "";
			var point = false;
                  var district_data = false;
			var districts_crossed = [];
			var sensors_crossed = [];
			var hover_something = false;
			map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
				if(feature.getGeometry().getType()=="Point"){
                              if(feature.get("name")!=="" && sensors_crossed.length < 3){
                                    hover_something = true;
                                    sensors_crossed.push(feature.get("name"));
                                    if(feature.get("P1") === undefined){
                                          P1 = "–";
                                    }
                                    else{
                                          P1 = Math.round(feature.get("P1"));
                                    }
                                    if(feature.get("P2") === undefined){
                                          P2 = "–";
                                    }
                                    else{
                                          P2 = Math.round(feature.get("P2"));
                                    }
                                    out_sensors+="<tr><td><a href=\"https://www.madavi.de/sensor/archiv_luftdaten_info/graph.php?sensor="+feature.get("name")+"\" target=\"_blank\">Sensor "+feature.get("name")+"</a></td><td align=\"right\">"+P1+"</td><td align=\"right\">"+P2+"</td></td/tr>";
                                    point = true;
                              }
				}
				else{
					
					if(feature.getGeometry().getType()=="Polygon"){
						district = "";
						if(feature.get("name")!=="" && districts_crossed.length <= 1){
							if($.inArray(feature.get("name"),districts_crossed)){
                                                hover_something = true;
                                                districts_crossed.push(feature.get("name"));
                                                district = "<strong>"+feature.get("name")+"</strong><br/>";
                                                if(feature.get("Num_Sensors")>0){
                                                      district_data = true;
                                                      district+="("+feature.get("Num_Sensors")+" Sensor[en]: "+feature.get("Sensor_IDs").replace(/,/g, ", ")+")";
                                                      district+="<table>";
                                                      district+="<tr><td><strong>Bezirk</strong></td><td align=\"right\"><strong>PM10</strong></td><td align=\"right\"><strong>PM2.5</strong></td></tr>";
                                                      district+="<tr><td>Median</td><td align=\"right\">"+Math.round(feature.get("P1"))+"</td><td align=\"right\">"+Math.round(feature.get("P2"))+"</td></tr>";
                                                      district+="<tr><td>24h-Mittel</td><td align=\"right\">"+Math.round(feature.get("P1floating"))+"</td><td align=\"right\">"+Math.round(feature.get("P2floating"))+"</td></tr>";
                                                }
                                                else{
                                                      district+="keine aktuellen Daten vorhanden";
                                                }
							}
						}
					}
				}
			});
			if(point) jTarget.css("cursor", "pointer");
			else jTarget.css("cursor", "");
                  if(out_sensors!==""){
                        out_sensors = (!district_data?"<table><tr><td>&nbsp;</td><td align=\"right\">PM10</td><td align=\"right\">PM2.5</td></tr>":"")+'<tr><td><strong>Einzelwerte</strong></td><td align=\"right\">&nbsp;</td><td align=\"right\">&nbsp;</td></tr>'+out_sensors+'';
                  }
			if(hover_something){
                        document.getElementById("mapinfo2").innerHTML = "<div id=\"innerInfo\">"+district+out_sensors+(district_data?"</table>":"")+"<br/>Alle Feinstaubwerte in µg/m³</div>";
                        document.getElementById("mapinfo2").style.top = e.pixel[1]+20+"px";
                        document.getElementById("mapinfo2").style.left = e.pixel[0]-70+"px";
                        document.getElementById("mapinfo2").style.height = document.getElementById("innerInfo").offsetHeight+"px";
                        document.getElementById("mapinfo2").style.display = "block";
			}
			else
				document.getElementById("mapinfo2").style.display = "none";
		}
	}); // end of $.get( "../data/stuttgart_sensors_v2.json", function( sensordata ) {
}); // end of $.get( "../data/stuttgart_districts.json", function( data ) {

	var districtsTimestamp = xhr.getResponseHeader("Last-Modified");
	
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
      // update map
	function change_colormapping(){
            // check whether lut is valid or not
            if(select_source.value === "PM25" || select_source.value === "PM25floating"){
                  $("#color-mode option[value='LuQx']").attr('disabled',true);
                  //var el = document.querySelector('img');
                  //console.log(el.getAttribute('disabled'));
                  if(select_color_mode.value === "LuQx") select_color_mode.value = "AQI";
            }
            else{
                  $("#color-mode option[value='LuQx']").attr('disabled',false);
            }
		vectorSensors.setStyle(styleFuntionOpenLayers);
		vectorDistricts.setStyle(styleFuntionOpenLayers);
		map.render();
            if(select_color_mode.value==="AQI") {
                  if(select_source.value === "PM25" || select_source.value === "PM25floating")
                        mapScaleLut = colorLookupTableAQIPM25;
                  else
                        mapScaleLut = colorLookupTableAQIPM10;
            }
            if(select_color_mode.value==="LuQx") mapScaleLut = colorLookupTableLuQxPM10;
            if(select_color_mode.value==="GreenRedPink") mapScaleLut = colorLookupTableGreenRedPink;
            if(select_color_mode.value==="RedGreen") mapScaleLut = colorLookupTableRedGreen;
            scaleComplex(mapScaleId,mapScaleOrientation,mapScaleWidth,mapScaleHeight,mapScaleLut);
	}
	function resize(){
		
	}
      function run() {
		var d = new Date();
		d.setSeconds(districtsTimestamp);
		document.getElementById("timestamp").innerHTML = d;
        }
        
        // in case the document is already rendered
        if (document.readyState!='loading') run();
        // modern browsers
        else if (document.addEventListener) document.addEventListener('DOMContentLoaded', run);
        // IE <= 8
        else document.attachEvent('onreadystatechange', function(){
            if (document.readyState=='complete') run();
        });
