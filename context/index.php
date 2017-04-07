<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <title>Feinstaub-Sensor im Kontext</title>
    <meta http-equiv="cache-control" content="max-age=86400" />
	<meta http-equiv="cache-control" content="public" />
	<meta property="og:title" content="Feinstaub in Stuttgart">
	<meta property="og:description" content="Hier finden Sie die OpenData-Feinstaubmessungen von OK Lab Stuttgart nach Stadtteilen geordnet und als Diagramm aufbereitet.">
	<meta property="og:image" content="<?php echo $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; ?>districts.png">
	<meta property="og:url" content="<?php echo $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; ?>">
    <script src="../js/d3.v4.min.js" type="text/javascript"></script>
    <script src="../js/jquery.min.js" type="text/javascript"></script>
	<!--<script src="../js/ol.js" type="text/javascript"></script>-->
    <script src="../library.js" type="text/javascript"></script>
	<!--<link rel="stylesheet" href="../js/ol.css" type="text/css" media="all">-->
	<link rel="stylesheet" href="../styles.css" type="text/css" media="all">
	<link rel="stylesheet" href="context.css" type="text/css" media="all">
</head>
  <body>
    <div id="chart">
		<div id="legend2">
			<div id="mapTimeInfo">Aktuelles 24h-Mittel PM10</div>
			<div id="mapdiv"></div>
			<div id="mapscale">scale</div>
		</div>
		<svg id="graph"></svg>
		<svg id="graph2"></svg>
	</div>
	<div id="controlBar2Button" class="bar2Button shadow bgcolor"><div class="bar2ButtonText">Einstellungen</div></div>
	<div id="infoBar2Button" class="bar2Button shadow bgcolor"><div class="bar2ButtonText">Info</div></div>
    <div id="controlBar2" class="Bar2 shadow">
      <div class="Bar2Header bgcolor">
        <h1>Feinstaub in Stuttgart</h1>
		<p>Dargestellt wird das fließende 24-Stunden-Mittel der Feinstaub-PM10-Werte der einzelnen Stuttgarter Stadtbezirke (Grundlage: Sensoren vom OK Lab Stuttgart) und, zum Vergleich, das fließende 24-Stunden-Mittel zweier Meßstellen des LUBW.</p>
		<p>Das Diagramm reagiert auf Mausbewegungen – was natürlich für Smartphones keine Lösung darstellt.</p>
		<label>
		  Farbtabelle
		  <select id="color-mode" class="form-control">
			<option value="AQI" selected="selected">Air Quality Index</option>
			<option value="LuQx">Kurzzeit-Luftqualitätsindex LuQx</option>
			<option value="GreenRedPink">Grün-Rot-Pink</option>
		  </select>
		</label>
      </div>
    </div>
    <div id="infoBar2" class="Bar2 shadow">
      <div class="Bar2Header bgcolor">
        <h1 style="margin-bottom: 0;">Feinstaub in Stuttgart</h1>
      </div>
      <div class="Bar2Footer">
        <iframe src="../help/?context=districts"></iframe>
      </div>
	</div>
	<span id="copyright">Version <?php echo $version; ?> | Daten: <span id="timestamp"></span></span>
  </div>
<script>
var sensor_id = 231;

	
</script>

<script src="context.js" type="text/javascript"></script>
<script>



</script>
<!--<div id="status_bar"></div>-->
</body>
</html>
