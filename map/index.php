<?php
include_once("../library.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title>Feinstaub in Stuttgart – Karte</title>
		<meta http-equiv="cache-control" content="max-age=86400" />
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
		<link rel="stylesheet" href="mapstyles.css" type="text/css" media="all"/>

		<!--
		Change Log
		
		ToDo
		- include LUBW
		- reduce size (1,35MB)
		
		2.8.0
		reduced php
		
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
	</head>
	<body>
		<div style="width:100%;height:100%;">
			<div id="mapdiv"></div>
			<div id="mapscale"></div>

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
							<option value="LuQx">Kurzzeit-Luftqualitätsindex LuQx</option>
						  </select>
						</label><br/>
						  Datenbasis für Bezirke
						  <select id="data-source" class="form-control">
							<option value="PM10floating">PM10 – 24h-Mittelwert</option>
							<option value="PM10" selected="selected">PM10 – aktuelles Stundenmittel</option>
							<option value="PM25floating">PM2.5 – 24h-Mittelwert</option>
							<option value="PM25">PM2.5 – aktuelles Stundenmittel</option>
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
			<span id="copyright">Version 2.8.0 | Daten: <span id="timestamp"></span></span>
		</div>
	</body>
    <script src="map.js" type="text/javascript"></script>
</html>
