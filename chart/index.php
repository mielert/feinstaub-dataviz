<?php
include_once("../library.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <title>Feinstaub in Stuttgart</title>
    <meta http-equiv="cache-control" content="max-age=86400" />
    <meta property="og:title" content="Feinstaub in Stuttgart"/>
    <meta property="og:description" content="Hier finden Sie die OpenData-Feinstaubmessungen von OK Lab Stuttgart zusammengefasst und als Diagramm aufbereitet."/>
    <meta property="og:image" content="<?php echo $url; ?>/chart/chart.png"/>
    <meta property="og:url" content="<?php echo $url; ?>/chart/"/>
    <script src="../js/d3.v4.min.js" type="text/javascript"></script>
    <!--<script src="../js/jquery.min.js" type="text/javascript"></script>-->
    <script src="../library.js" type="text/javascript"></script>
    <link rel="stylesheet" href="../styles.css" type="text/css" media="all"/>
    <link rel="stylesheet" href="chartstyles.css" type="text/css" media="all"/>
    <!--
    Change Log
    
planned
    add zoom
    P10/P2.5 Switch
    Hover for LUBW
	- reduce size (786KB)


    2.9.0
    css extracted
		
    2.8.0
    mobile friendly

    2.7.0
    corporate design
    resizable

    2.6.0
    non-public version
    ranking removed

    2.5.0
    improved geofilter
    Update OpenLayers
    Links from ranking to Madavi
    
    2.4.1
    Fix for embedded ranking in Firefox
    
    2.4.0
    integrate LUBW

    2.3.1
    improved list
    remove Google & co
    
    2.3.0
    List of dirtiest locations aka ranking
    
    2.2.0
    group data by id
    show floating 24h
    
    2.1.0
    added help
    
    2.0.1
    integrate sensor IDs of highest an lowest values
    
    2.0.0
    switch from d3 V3 to d3 V4
    
    1.0.0
    first public release
    -->
  </head>
  <body>
    <div id="chart"><svg id="graph"></svg></div>
    <div id="controlBar2Button" class="bar2Button shadow bgcolor"><div class="bar2ButtonText">Einstellungen</div></div>
    <div id="infoBar2Button" class="bar2Button shadow bgcolor"><div class="bar2ButtonText">Info</div></div>
    <div id="controlBar2" class="Bar2 shadow">
      <div class="Bar2Header bgcolor">
        <h1>Feinstaub in Stuttgart</h1>
        <button id="toggleAreas" class="toggle">Streuung <span class="display" style="display: none;">aus</span><span class="display">ein</span>blenden</button>
        <button id="toggleLUBW" class="toggle">LUBW-Daten <span class="display" style="display: none;">ein</span><span class="display">aus</span>blenden</button>
      </div>
    </div>
    <div id="infoBar2" class="Bar2 shadow">
      <div class="Bar2Header bgcolor">
        <h1 style="margin-bottom: 0;">Feinstaub in Stuttgart</h1>
      </div>
      <div class="Bar2Footer">
        <iframe src="../help/?context=chart"></iframe>
      </div>
    </div>
    <span id="copyright">Version 2.9.0 | Daten: <span id="timestamp"></span></span>
    <script src="chart.js" type="text/javascript"></script>
  </body>
</html>
