<?php
$version = str_replace(array("chart_",".php"),"",basename(__FILE__));
?>
<!DOCTYPE html>
<html>
  <head>
    <!--<meta http-equiv="refresh" content="300; URL=<?php if(!isset($basename)) echo basename(__FILE__); else echo $basename; ?>?help=hide"/>-->
    <meta charset="utf-8"/>
    <title>Feinstaub in Stuttgart</title>
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
		<meta property="og:title" content="Feinstaub in Stuttgart">
		<meta property="og:description" content="Hier finden Sie die OpenData-Feinstaubmessungen von OK Lab Stuttgart zusammengefasst und als Diagramm aufbereitet.">
		<meta property="og:image" content="https://fritzmielert.de/feinstaub/chart/chart.png">
		<meta property="og:url" content="https://fritzmielert.de/feinstaub/chart/">
    <script src="/feinstaub/js/d3.v4.min.js" type="text/javascript"></script>
    <script src="/feinstaub/js/jquery.min.js" type="text/javascript"></script>
    <script src="/feinstaub/library.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/feinstaub/styles.css" type="text/css" media="all">


    <!--
    Change Log
    2.9.0 planned
    add zoom
    P10/P2.5 Switch
    Hover for LUBW
		- reduce size (786KB)
		
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
    List of dirtiest locations
    
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
<style>

body{
	-webkit-user-select: none; /* Chrome/Safari */        
	-moz-user-select: none; /* Firefox */
	-ms-user-select: none; /* IE10+ */
	
	/* Rules below not implemented in browsers yet */
	-o-user-select: none;
	user-select: none;
	font-size: 0.9em !important;
}

.y .grid{
  right:10px;
}

#worstLocations{
  z-index: 100000;
  right: 145px;
  top: 100px;
  position: absolute;
  background: rgba(255,255,255,0.9);
}

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.x.axis path, .y2.axis path {
  display: none;
}

g.y .tick line {
  stroke: rgba(0, 0, 0, 0.1);
  stroke-dasharray: 2,2;
}

g.y2 .tick line{
  stroke: rgba(0, 0, 0, 0.9);
}

#area {
  fill: rgba(230, 85, 13, 0.1);
}

#area3 {
  fill: rgba(230, 85, 13, 0.2);
}

#area2 {
  fill: rgba(49, 130, 189, 0.1);
}

#area4 {
  fill: rgba(49, 130, 189, 0.2);
}

.P1 {
  fill: none;
  stroke: rgba(230, 85, 13, 1);
  stroke-width: 1.5px;
}

.P2 {
  fill: none;
  stroke: rgba(49, 130, 189, 1);
  stroke-width: 1.5px;
}

.overline {
  fill: none;
  stroke: rgba(255, 255, 255, 0.7);
  stroke-width: 3px;
}

#line231p1 {
  fill: none;
  stroke: rgba(230, 85, 13, 0.8);
  stroke-width: 0.5px;
}

#line231p2 {
  fill: none;
  stroke: rgba(49, 130, 189, 0.8);
  stroke-width: 0.5px;
}
#line217p1 {
  fill: none;
  stroke: rgba(230, 85, 13, 0.8);
  stroke-width: 0.5px;
}

#line217p2 {
  fill: none;
  stroke: rgba(49, 130, 189, 0.8);
  stroke-width: 0.5px;
}
#line50p1 {
  fill: none;
  stroke: rgba(230, 85, 13, 0.8);
  stroke-width: 0.5px;
}

#line50p2 {
  fill: none;
  stroke: rgba(49, 130, 189, 0.8);
  stroke-width: 0.5px;
}
.lineP1floating{
  fill: none;
  stroke: rgba(230, 85, 13, 1);
  stroke-width: 2.5px;
  stroke-dasharray: 5,5;
}

.lineP2floating{
  fill: none;
  stroke: rgba(49, 130, 189, 1);
  stroke-width: 2.5px;
  stroke-dasharray: 5,5;
}

.statDEBW013pm10{
  fill: none;
  stroke: rgba(49, 163, 84, 1);
  stroke-width: 2.5px;
  stroke-dasharray: 5,5;
}

.statDEBW118pm10{
  fill: none;
  stroke: rgba(150, 150, 150, 1);
  stroke-width: 2.5px;
  stroke-dasharray: 5,5;
}
.legend_text{
  fill: #000 !important;
  stroke: none !important;
}

.headline {
  fill: black;
  font: 15px sans-serif;
}

.overlay {
  fill: none;
  pointer-events: all;
}

.focus circle {
  fill: none;
  stroke: steelblue;
}

.domain {
  display: none; 
}
#graph{
	font-size: 0.7em;
}

#html_overlay, #worstLocations_overlay {
  height: 100%;
  width: 100%;
  background: rgba(200,200,200,0.6);
  position: relative;
  z-index: 10000;
}

#html_overlay iframe{
  background: white;
  height: 80%;
  width: 400px;
  position:absolute;
  top:30px;
  left:50%;
  margin-top:0px; /* this is half the height of your div*/  
  margin-left:-200px; /*this is half of width of your div*/
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  border-bottom-right-radius: 5px;
  border-bottom-left-radius: 5px;
  z-index: 10000;
  overflow-y: scroll; border: none;
  -webkit-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
  box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
}
#worstLocations_overlay iframe{
  background: white;
  height: 320px;
  width: 455px;
  position:absolute;
  top:30px;
  left:50%;
  margin-top:0px; /* this is half the height of your div*/  
  margin-left:-225px; /*this is half of width of your div*/
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  border-bottom-right-radius: 5px;
  border-bottom-left-radius: 5px;
  z-index: 10000;
  overflow: hidden;
  border: none;
  -webkit-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
  box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.5);
}
.toggle{
  position: relative;
  background: #f00;
  color: #fff;
  border: none;
}
#toggleHelp{
}
#toggleAreas{
}
#toggle231{
}
#buttons{
  position: absolute;
  z-index: 10000;
  left: 24px;
  bottom: 0px;
}

svg{
  position: absolute;
  top: 0px;
  left: 0px;
}
#chart,#graph{
  background-color: #fff;
  width: 100%;
  height: 100%;
  position: absolute;
  left: 0px;
  top: 0px;
}
</style>
</head>
  <body>
    <div id="chart"><svg id="graph"></svg></div>
		<div id="controlBar2Button" class="shadow bgcolor"><div id="controlBar2ButtonText">Einstellungen</div></div>
    <div id="controlBar2" class="shadow">
      <div id="controlBar2Header" class="bgcolor">
        <h1>Feinstaub in Stuttgart</h1>
        <!--<h2>Einstellungen</h2>-->
        <button id="toggleAreas" class="toggle">Streuung <span class="display" style="display: none;">aus</span><span class="display">ein</span>blenden</button>
        <!--<button id="toggle50" class="toggle">Sensor 50 <span class="display" style="display: none;">aus</span><span class="display">ein</span>blenden</button>
        <button id="toggle231" class="toggle">Sensor 231 <span class="display" style="display: none;">aus</span><span class="display">ein</span>blenden</button>
        <button id="toggle217" class="toggle">Sensor 217 <span class="display" style="display: none;">aus</span><span class="display">ein</span>blenden</button>-->
        <button id="toggleLUBW" class="toggle">LUBW-Daten <span class="display" style="display: none;">ein</span><span class="display">aus</span>blenden</button>
      </div>
      <div id="controlBar2Footer">
        <!--<h2>Hilfe</h2>-->
        <iframe src="/feinstaub/help/?context=chart"></iframe>
      </div>
    </div>
		<span id="copyright">Version <?php echo $version; ?> | Daten: <span id="timestamp"></span></span>
  </div>



<script>

var margin = 80,
    margin_left = 60,
    margin_right = 40,
    margin_top = 40,
    margin_bottom = 40,
    width = parseInt(d3.select("#graph").style("width")) - margin_left - margin_right,
    height = parseInt(d3.select("#graph").style("height")) - margin_top - margin_bottom;

var xScale = d3.scaleTime()
    .range([0, width]);

var yScale = d3.scaleLinear()
    .range([height, 0])
    .nice();

var xAxis = d3.axisBottom()
    .scale(xScale)
    .tickFormat( function(d) {return multiFormat(d);})
    ;

// gridlines in y axis function
var yAxis1 = d3.axisLeft()
    .scale(yScale)
    .tickSizeOuter(5)
    .tickPadding(1)
    .tickSize((parseInt(d3.select("#graph").style("width")) - margin_left - margin_right)*-1);

// gridlines in y2 axis function
var yAxis2 = d3.axisRight()
    .scale(yScale)
    .tickValues([50])
    .tickSize((parseInt(d3.select("#graph").style("width")) - margin_left - margin_right)*-1);

var formatMillisecond = d3.timeFormat(".%L"),
    formatSecond = d3.timeFormat(":%S"),
    formatMinute = d3.timeFormat("%H:%M"),
    formatHour = d3.timeFormat("%H:%M"),
    formatDay = d3.timeFormat("%d.%m."),
    formatWeek = d3.timeFormat("%d.%m."),
    formatMonth = d3.timeFormat("%B"),
    formatYear = d3.timeFormat("%Y");

function multiFormat(date) {
  return (d3.timeSecond(date) < date ? formatMillisecond
      : d3.timeMinute(date) < date ? formatSecond
      : d3.timeHour(date) < date ? formatMinute
      : d3.timeDay(date) < date ? formatHour
      : d3.timeMonth(date) < date ? (d3.timeWeek(date) < date ? formatDay : formatWeek)
      : d3.timeYear(date) < date ? formatMonth
      : formatYear)(date);
}

var hoverTimeFormat = d3.timeFormat("%d.%m., %H:%M");
var versionTimeFormat = d3.timeFormat("%d.%m.%Y, %H:%M");

var line = d3.line()
    .x(function(d) { return xScale(d.date); })
    .y(function(d) { return yScale(d.P1mid); });

var graph = d3.select("#graph")
    .attr("width", width + margin_left + margin_right)
    .attr("height", height + margin_top + margin_bottom)
  .append("g")
    .attr("transform", "translate(" + margin_left + "," + margin_top + ")");
// P10
var line = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P1mid); });

// P2.5
var line2 = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P2mid); });

// 50% P10
var area3 = d3.area()
    .x(function(d) { return xScale(d.timestamp); })
    .y0(function(d) { return yScale(d.P1lowmain); })
    .y1(function(d) { return yScale(d.P1highmain); });

// P10
var area = d3.area()
    .x(function(d) { return xScale(d.timestamp); })
    .y0(function(d) { return yScale(d.P1low); })
    .y1(function(d) { return yScale(d.P1high); });

// 50% P2.5
var area4 = d3.area()
    .x(function(d) { return xScale(d.timestamp); })
    .y0(function(d) { return yScale(d.P2lowmain); })
    .y1(function(d) { return yScale(d.P2highmain); });

// P2.5
var area2 = d3.area()
    .x(function(d) { return xScale(d.timestamp); })
    .y0(function(d) { return yScale(d.P2low); })
    .y1(function(d) { return yScale(d.P2high); });

var line231p1 = d3.line()
    /*.interpolate(function(points){  //interpolate straight lines with gaps for NaN
      var section = 0;
      var arrays = [[]];
      points.forEach(function(d,i){
        if(isNaN(d[1]) || d[1]==0){
          section++;
          arrays[section] = [];
        }else{
          arrays[section].push(d)
        }
      });

      var pathSections = [];
      arrays.forEach(function(points){
        pathSections.push(d3.svg.line()(points));
      })
      var joined = pathSections.join('');
      return joined.substr(1); //substring becasue D£ always adds an M to a path so we end up with MM at the start
    })*/
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P1_231); });

var line231p2 = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P2_231); });
    
var line217p1 = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P1_217); });

var line217p2 = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P2_217); });
    
var line50p1 = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P1_50); });

var line50p2 = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P2_50); });
    
var lineP1floating = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P1floating); });
    
var lineP2floating = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P2floating); });
    
var line_statDEBW013pm10 = d3.line()
    .x(function(d2) { return xScale(d2.timestamp); })
    .y(function(d2) { return yScale(d2.statDEBW013pm10); });

var line_statDEBW118pm10 = d3.line()
    .x(function(d2) { return xScale(d2.timestamp); })
    .y(function(d2) { return yScale(d2.statDEBW118pm10); });
    
      
var parseDate = d3.timeParse("%Y%m%d%H%M%S");

var bisectDate = d3.bisector(function(d) { return d.timestamp; }).left;


function append_lubw(){
  // statDEBW013pm10 aka Gnesener Straße
  d3.tsv("../data_lubw.tsv", function(error, data2) {
    if (error) throw error;
  
    data2.forEach(function(d2) {
      d2.timestamp = parseDate(d2.timestamp);
      d2.statDEBW013pm10 = +d2.statDEBW013pm10;
      d2.statDEBW118pm10 = +d2.statDEBW118pm10;
    });
    
    graph.append("path")
        .datum(data2)
        .attr("id", "line_statDEBW013pm10")
        .attr("class", "statDEBW013pm10")
        .attr("d", line_statDEBW013pm10);
        
    graph.append("path")
        .datum(data2)
        .attr("id", "line_statDEBW118pm10")
        .attr("class", "statDEBW118pm10")
        .attr("d", line_statDEBW118pm10);
  });
}

function append_data(){
d3.tsv("../data.tsv", function(error, data) {
  if (error) throw error;

  data.forEach(function(d) {
    d.timestamp = parseDate(d.timestamp);
    d.num_sensors = +d.num_sensors;
    d.P1low = +d.P1low;
    d.P1high = +d.P1high;
    d.P1mid = +d.P1mid;
    d.P1lowmain = +d.P1lowmain;
    d.P1highmain = +d.P1highmain;
    d.P2low = +d.P2low;
    d.P2high = +d.P2high;
    d.P2mid = +d.P2mid;
    d.P2lowmain = +d.P2lowmain;
    d.P2highmain = +d.P2highmain;
    d.P1highSensorId = +d.P1highSensorId;
    d.P1lowSensorId = +d.P1lowSensorId;
    d.P2highSensorId = +d.P2highSensorId;
    d.P2lowSensorId = +d.P2lowSensorId;
    d.P2highSensorId = +d.P2highSensorId;
    d.P2lowSensorId = +d.P2lowSensorId;
    d.P1floating = +d.P1floating;
    d.P2floating = +d.P2floating;
    d.P1_231 = +d.P1_231;
    d.P2_231 = +d.P2_231;
    d.P1_217 = +d.P1_217;
    d.P2_217 = +d.P2_217;
    d.P1_50 = +d.P1_50;
    d.P2_50 = +d.P2_50;
  });
  // var parseDate = d3.timeParse("%Y%m%d%H%M%S");

	$("#timestamp").html(versionTimeFormat(d3.max(data, function(d) { return d.timestamp; })));
  xScale.domain(d3.extent(data, function(d) { return d.timestamp; }));
  //y.domain([d3.min(data, function(d) { return d.P2low; }), d3.max(data, function(d) { return d.P1high; })]);
  yScale.domain([0, d3.max(data, function(d) { return d.P1mid; })*1.5]);
  //y.domain([d3.min(data, function(d) { return 0; }), d3.max(data, function(d) { return 250; })]);

// official data
append_lubw();

  graph.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

  graph.append("g")
      .attr("class", "y axis")
      .attr("id", "yaxis")
      .attr("transform", "translate(0,0)")
      .call(yAxis1);

  graph.append("g")
      .attr("class", "y2 axis")
      .attr("id", "y2axis")
      .attr("transform", "translate(" +width + ",0)")
      .call(yAxis2);
      
  graph.append("path")
      .datum(data)
      .attr("id", "area")
      .attr("class", "area")
      .attr("style", "display: none;")
      .attr("d", area);
      
  graph.append("path")
      .datum(data)
      .attr("id", "area3")
      .attr("class", "area")
      .attr("style", "display: none;")
      .attr("d", area3);
     
  graph.append("path")
      .datum(data)
      .attr("id", "area2")
      .attr("class", "area")
      .attr("style", "display: none;")
      .attr("d", area2);
      
  graph.append("path")
      .datum(data)
      .attr("id", "area4")
      .attr("class", "area")
      .attr("style", "display: none;")
      .attr("d", area4);
      
  graph.append("path")
      .datum(data)
      .attr("id", "line")
      .attr("class", "P1")
      .attr("d", line);

  graph.append("path")
      .datum(data)
      .attr("id", "line2")
      .attr("class", "P2")
      .attr("d", line2);

  graph.append("path")
      .datum(data)
      .attr("id", "line231p1")
      .attr("style", "display: none;")
      .attr("class", "s231")
      .attr("d", line231p1);

  graph.append("path")
      .datum(data)
      .attr("id", "line231p2")
      .attr("style", "display: none;")
      .attr("class", "s231")
      .attr("d", line231p2);
      
  graph.append("path")
      .datum(data)
      .attr("id", "line217p1")
      .attr("style", "display: none;")
      .attr("class", "s217")
      .attr("d", line217p1);

  graph.append("path")
      .datum(data)
      .attr("id", "line217p2")
      .attr("style", "display: none;")
      .attr("class", "s217")
      .attr("d", line217p2);
      
  graph.append("path")
      .datum(data)
      .attr("id", "line50p1")
      .attr("style", "display: none;")
      .attr("class", "s50")
      .attr("d", line50p1);

  graph.append("path")
      .datum(data)
      .attr("id", "line50p2")
      .attr("style", "display: none;")
      .attr("class", "s50")
      .attr("d", line50p2);
      
  graph.append("path")
      .datum(data)
      //.attr("transform", "translate(" +width + ","+ height +")")
      .attr("id", "lineP1floating")
      .attr("class", "lineP1floating")
      .attr("d", lineP1floating);

  graph.append("path")
      .datum(data)
      .attr("id", "lineP2floating")
      .attr("class", "lineP2floating")
      .attr("d", lineP2floating);

// Infos
var infox = 240;
var infoy = 0;

  graph.append("line")
    .attr("class", "P1 legend_line")
    .attr("x1", width-infox)
    .attr("y1", infoy)
    .attr("x2", width-infox+20)
    .attr("y2", infoy);
    
  graph.append("text")
      .text("P10, Median aus gemittelten 5-Min-Werten")
      .attr("class", "legend_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+3);

  graph.append("line")
    .attr("class", "P2 legend_line")
    .attr("x1", width-infox)
    .attr("y1", infoy+20)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+20);

  graph.append("text")
      .text("P2.5, Median aus gemittelten 5-Min-Werten")
      .attr("class", "legend_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+23);

  graph.append("line")
    .attr("class", "lineP1floating legend_line")
    .attr("x1", width-infox)
    .attr("y1", infoy+40)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+40);

  graph.append("text")
      .text("P10, gleitender 24h-Mittelwert aus Median")
      .attr("class", "legend_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+43);

  graph.append("line")
    .attr("class", "lineP2floating legend_line")
    .attr("x1", width-infox)
    .attr("y1", infoy+60)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+60);

  graph.append("text")
      .text("P2.5, gleitender 24h-Mittelwert aus Median")
      .attr("class", "legend_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+63);

  graph.append("line")
    .attr("class", "statDEBW013pm10 legend_line")
    .attr("x1", width-infox)
    .attr("y1", infoy+80)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+80);

  graph.append("text")
      .text("P10 Bad Cannstatt, Quelle: LUBW")
      .attr("class", "statDEBW013pm10 legend_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+83);

  graph.append("line")
    .attr("class", "statDEBW118pm10 legend_line")
    .attr("x1", width-infox)
    .attr("y1", infoy+100)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+100);

  graph.append("text")
      .text("P10 Neckartor, Quelle: LUBW")
      .attr("class", "statDEBW118pm10 legend_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+103);

// Focus
  function mousemove() {
    var x0 = xScale.invert(d3.mouse(this)[0]),
        i = bisectDate(data, x0, 1),
        d0 = data[i - 1],
        d1 = data[i],
        d = x0 - d0.date > d1.date - x0 ? d1 : d0;
    
    focus.attr("transform", "translate(" + xScale(d.timestamp) + "," + height/2 + ")");
    // Flip label side
    if (xScale(d.timestamp) > width-220) {
      focus.select("#text1").attr("x", -243);
      focus.select("#text2").attr("x", -243);
      focus.select("#polytext1").attr("x", -246);
      focus.select("#polytext2").attr("x", -246);
    }
    else {
      focus.select("#text1").attr("x", 9);
      focus.select("#text2").attr("x", 9);
      focus.select("#polytext1").attr("x", 6);
      focus.select("#polytext2").attr("x", 6);
    }
    
    var y1 = yScale(d.P1mid)-height/2;
    var y2 = yScale(d.P2mid)-height/2;
    var ymid = (y1+y2)/2;
    if (y2 < y1 + 20) {
      y1 = ymid -10;
      y2 = ymid +10;
    }

    focus.select("#text1").attr("y", y1);
    focus.select("#polytext1").attr("y", y1-6);
    focus.select("#text2").attr("y", y2);
    focus.select("#polytext2").attr("y", y2-6);
    if (d.P1lowSensorId !== 0 && d.P1highSensorId !== 0 && d.P2lowSensorId !== 0 && d.P2highSensorId !== 0) {
      focus.select("#text1").text("PM10: "+d.P1mid+" µg/m³ ("+d.P1low+" [S"+d.P1lowSensorId+"] - "+d.P1high+" [S"+d.P1highSensorId+"])");
      focus.select("#text2").text("PM2.5: "+d.P2mid+" µg/m³ ("+d.P2low+" [S"+d.P2lowSensorId+"] - "+d.P2high+" [S"+d.P2highSensorId+"])");
      focus.select("#polytext1").attr("width",240);
      focus.select("#polytext2").attr("width",240);
      //focus.select("#polytext1").attr("height",28);
      //focus.select("#polytext2").attr("height",28);
    }
    else {
      focus.select("#text1").text("PM10: "+d.P1mid+" µg/m³ ("+d.P1low+"-"+d.P1high+")");
      focus.select("#text2").text("PM2.5: "+d.P2mid+" µg/m³ ("+d.P2low+"-"+d.P2high+")");
      focus.select("#polytext1").attr("width",150);
      focus.select("#polytext2").attr("width",150);
      //focus.select("#polytext1").attr("height",14);
      //focus.select("#polytext2").attr("height",14);
    }
    if(d.num_values>0) focus.select("#text3").text(d.num_values+" Werte");
    else focus.select("#text3").text("");
    if(d.num_sensors>0) focus.select("#text5").text(d.num_sensors+" Sensoren");
    else focus.select("#text5").text("");
    focus.select("#text4").text(hoverTimeFormat(d.timestamp));
  }

// Overlay with information
var focus = graph.append("g")
      .attr("class", "focus")
      .style("display", "none");

  focus.append("line")
    .attr("class", "overline")
    .attr("x1", 2)
    .attr("y1", -height/2)
    .attr("x2", 2)
    .attr("y2", height/2);
      
  focus.append("line")
    .attr("class", "overline")
    .attr("x1", -2)
    .attr("y1", -height/2) 
    .attr("x2", -2)
    .attr("y2", height/2);

  focus.append("rect")
      .attr("id", "polytext3")
      .attr("x", -33)
      .attr("y", window.innerHeight-height/2-79)
      .attr("style", "fill: white; border: 1px solid black;")
      .attr("width", 66)
      .attr("height", 40);

  focus.append("rect")
      .attr("id", "polytext1")
      .attr("x", "5")
      .attr("dy", "0")
      .attr("width", "220")
      .attr("height", "14")
      .attr("style", "fill: rgba(255,255,255,0.9)");

  focus.append("rect")
      .attr("id", "polytext2")
      .attr("x", "5")
      .attr("dy", "0")
      .attr("width", "220")
      .attr("height", "14")
      .attr("style", "fill: rgba(255,255,255,0.9)");

  focus.append("text")
      .attr("id", "text1")
      .attr("x", 9)
      .attr("dy", "4px")
      .attr("style", "fill: rgba(230, 85, 13, 1);");
        
  focus.append("text")
      .attr("id", "text2")
      .attr("x", 9)
      .attr("dy", "4px")
      .attr("style", "fill: rbga(49, 130, 189, 1);");
      
  focus.append("text")
      .attr("id", "text4")
      .attr("x", -30)
      .attr("y", window.innerHeight-height/2-64);

  focus.append("text")
      .attr("id", "text3")
      .attr("x", -30)
      .attr("y", window.innerHeight-height/2-55);

  focus.append("text")
      .attr("id", "text5")
      .attr("x", -30)
      .attr("y", window.innerHeight-height/2-46);

  focus.append("line")
    .style("stroke", "black")  // colour the line
    .style("stroke-width", "1px")
    .attr("x1", 0)     // x position of the first end of the line
    .attr("y1", window.innerHeight-height/2-74)      // y position of the first end of the line
    .attr("x2", 0)     // x position of the second end of the line
    .attr("y2", window.innerHeight-height/2-80);    // y position of the second end of the line
    
// Overlay to get moves
var view = graph.append("rect")
      .attr("id", "overlay_rect")
      .attr("class", "overlay")
      .attr("width", width)
      .attr("height", height)
      .on("mouseover", function() { focus.style("display", null); })
      .on("mouseout", function() { focus.style("display", "none"); })
      .on("mousemove", mousemove);

// Text not to be overlayed 
  graph.append("text")
      .attr("id", "legend_y")
      .attr("transform", "rotate(-90)")
      .attr("y", 25)
      .attr("x",0 - (height / 2))
      .attr("dy","-5em")
      .style("text-anchor", "middle")
      .text("Mikrogramm pro Kubikmeter Luft");


      
  resize();
});
}

  function resize() {
		if(window.innerWidth > 800){
			if(!controlVisible){
				$("#chart").css({left: 0, width: "100%"});
			}
			else{
				$("#chart").css({left: 400, width: window.innerWidth-400});
			}
		}
    width = parseInt(d3.select("#graph").style("width")) - margin_left - margin_right;
    height = parseInt(d3.select("#graph").style("height")) - margin_top - margin_bottom;

    /* Update the range of the scale with new width/height */
    xScale.range([0, width]);
    yScale.range([height, 0]);

    /* Update the axis with the new scale */
    graph.select('.x.axis')
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

    graph.select('#yaxis')
      .call(yAxis1)
      .attr("transform", "translate(0,0)")
      .attr("tickSize",width);

    graph.select('#y2axis')
      .call(yAxis2)
      .attr("transform", "translate(" + width + ",0)")
      .attr("tickSize",-width);
      
    graph.selectAll('#yaxis line')
      .attr("x2", width);
      
    graph.selectAll('#y2axis line')
      .attr("x2", -width);
      

    /* Force D3 to recalculate and update the lines and areas */
      
    graph.select('#area')
      .attr("d", area);
    graph.select('#area3')
      .attr("d", area3);
    graph.select('#area2')
      .attr("d", area2);
    graph.select('#area4')
      .attr("d", area4);
      
    graph.select('#line_statDEBW013pm10')
      .attr("d", line_statDEBW013pm10);
    graph.select('#line_statDEBW118pm10')
      .attr("d", line_statDEBW118pm10);
      
    graph.select('#line')
      .attr("d", line);      
    graph.select('#line2')
      .attr("d", line2);
    graph.select('#line231p1')
      .attr("d", line231p1);
    graph.select('#line231p2')
      .attr("d", line231p2);
    graph.select('#line217p1')
      .attr("d", line217p1);
    graph.select('#line217p2')
      .attr("d", line217p2);
    graph.select('#line50p1')
      .attr("d", line50p1);
    graph.select('#line50p2')
      .attr("d", line50p2);
    graph.select('#lineP1floating')
      .attr("d", lineP1floating);
    graph.select('#lineP2floating')
      .attr("d", lineP2floating);
      
    graph.select('#overlay_rect')
      .attr("width", width)
      .attr("height", height);
      
    //alert("window.innerHeight: "+window.innerHeight+" height: "+height+" window.innerHeight-height/2-55: "+(window.innerHeight-height/2-55));
    //alert("window.innerHeight: "+window.innerHeight+" height: "+height+" window.innerHeight-height/2-55: "+ window.innerHeight-height/2-55);
    graph.select('#text3')
      .attr("y", (window.innerHeight-height/2-55));
    graph.select('#text4')
      .attr("y", window.innerHeight-height/2-64);
    graph.select('#text5')
      .attr("y", window.innerHeight-height/2-46);
    graph.select('#polytext3')
      .attr("y", window.innerHeight-height/2-79);
    
    graph.select('#legend_y')
      .attr("x", 0 - (window.innerHeight / 2));
    graph.select('#copyright')
      .attr("y", window.innerHeight-margin_bottom-4);
      
    graph.selectAll(".legend_line")
      .attr("x1", width-240)
      .attr("x2", width-220);
    graph.selectAll(".legend_text")
      .attr("x", width-210);
      
  }

append_data();
d3.select(window).on('resize', resize); 
// d3.select("#chart").on('resize', resize); 


</script>
        <script>
          $( document ).ready(function() {
              //$("#worstLocations_overlay iframe").css("left","-1000px");
              //$("#worstLocations_overlay iframe").delay( 10 ).css("left","");
              //window.setTimeout(toggleList, 800);
          });
          
          // read get variables
          var $_GET = {};
          document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
              function decode(s) {
                  return decodeURIComponent(s.split("+").join(" "));
              }
              $_GET[decode(arguments[1])] = decode(arguments[2]);
          });
                      
          if ($_GET['help']=="hide") {
              toggleHelp();
          }
          $( "#html_overlay" ).click(function() {
              toggleHelp();
          });
          $( "#toggleHelp" ).click(function() {
              toggleHelp();
          });
          $( "#toggleAreas" ).click(function() {
              toggleAreas();
          });
          $( "#toggle231" ).click(function() {
              toggle231();
          });
          $( "#toggle217" ).click(function() {
              toggle217();
          });
          $( "#toggle50" ).click(function() {
              toggle50();
          });
          $( "#toggleList" ).click(function() {
              toggleList();
          });
          $( "#toggleLUBW" ).click(function() {
              toggleLUBW();
          });
          $( "#worstLocations_overlay" ).click(function() {
              toggleList();
          });
          function toggle50(){
              $( ".s50" ).toggle();
              $( "#toggle50 .display" ).toggle();
          }
          function toggle231(){
              $( ".s231" ).toggle();
              $( "#toggle231 .display" ).toggle();
          }
          function toggle217(){
              $( ".s217" ).toggle();
              $( "#toggle217 .display" ).toggle();
          }
          function toggleList(){
              $( "#toggleList .display" ).toggle();
              if($("#worstLocations_overlay").css("z-index") == "-10000")
                $("#worstLocations_overlay").css("z-index", "");
              else
                $("#worstLocations_overlay").css("z-index", "-10000");
          }
          function toggleLUBW(){
              $( ".statDEBW013pm10" ).toggle();
              $( ".statDEBW118pm10" ).toggle();
              $( "#toggleLUBW .display" ).toggle();
          }
          function toggleHelp(){
              $( "#html_overlay" ).toggle();
              $( "#toggleHelp .display" ).toggle();
          }
          function toggleAreas(){
              $( ".area" ).toggle();
              $( "#toggleAreas .display" ).toggle();
          }
        </script>
</body>
</html>
