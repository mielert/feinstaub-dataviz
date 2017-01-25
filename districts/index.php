<?php
$version = str_replace(array("districts_",".php"),"",basename(__FILE__));

include_once("../library.php");

$tsvData = file_get_contents($data_root."chronological_districts.tsv");
$tsvData = explode("\n",$tsvData);
$firstRow = explode("	",$tsvData[0]);
$districts = array();
foreach($firstRow as $dataset){
	if(substr($dataset,0,11) == "P1floating_" && strlen(substr($dataset,11,100))>4){
		array_push($districts,substr($dataset,11,100));
	}
}
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
		<meta property="og:description" content="Hier finden Sie die OpenData-Feinstaubmessungen von OK Lab Stuttgart nach Stadtteilen geordnet und als Diagramm aufbereitet.">
		<meta property="og:image" content="https://fritzmielert.de/feinstaub/districts/districts.png">
		<meta property="og:url" content="https://fritzmielert.de/feinstaub/districts/">
    <script src="/feinstaub/js/d3.v4.min.js" type="text/javascript"></script>
    <script src="/feinstaub/js/jquery.min.js" type="text/javascript"></script>
		<script src="/feinstaub/js/ol.js" type="text/javascript"></script>
    <script src="/feinstaub/library.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/feinstaub/js/ol.css" type="text/css" media="all">
		<link rel="stylesheet" href="/feinstaub/styles.css" type="text/css" media="all">

    <!--
	ToDo
	- reduce size (1.5MB)
	
	1.5.1
	just little enhancements
	
	1.5.2
	geodata from mysql
	
	1.5.0
	data since 2016-09-01 from mysql, no more json needed
	readability
	
	1.4.0
	readability
	autoscale to maximum
	
	1.3.0
	readability
	
	1.2.0
	map included
	
    1.1.0
	less data to transfer
	code cleanup
	color
	legend
		
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
  stroke-width: 1.5px;
	opacity: 0.7;
}
.fadeout{
	opacity: 0.15;
}

<?php
$counter = 0;
$step = 360/count($districts);
foreach($districts as $dataset){
?>
.<?php echo $dataset; ?>{
	stroke: hsl(<?php echo ($counter*$step); ?>, 90%, 45%);
}
<?php
$counter++;
}
?>

.statDEBW013pm10{
  fill: none;
  stroke: rgba(49, 163, 84, 1);
  stroke-width: 1.5px;
  stroke-dasharray: 5,1;
}

.statDEBW118pm10{
  fill: none;
  stroke: rgba(150, 150, 150, 1);
  stroke-width: 1.5px;
  stroke-dasharray: 5,1;
}

.hover {
  stroke-width: 3px;
	opacity: 1;
}


.legend_text{
  fill: #000 !important;
  stroke: none !important;
  font-weight: normal;
}
.texthover{
	font-weight: bold;
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
div.tooltip {	
    position: absolute;			
    text-align: center;			
    /*width: 60px;	*/				
    /*height: 28px;		*/			
    padding: 4px;				
    background-color: rgba(255,255,255,0.8);	
    border: 0px;		
    /*border-radius: 8px;			*/
	font-size: 0.8em;
    pointer-events: none;			
}
.hiddenItem{
	display: none;
}
#mapdiv{
	width: 230px;
	height: 220px;
	position: absolute;
	left: 100px;
	top:20px;
}
#overline{
	stroke: #ccc;
}
#mapTimeInfo{
	position: absolute;
}
</style>
</head>
  <body>
    <div id="chart">
		<svg id="graph"></svg>
		<div id="mapdiv"><div id="mapTimeInfo">Aktuelles 24h-Mittel P10</div></div>
	</div>
	<div id="controlBar2Button" class="shadow bgcolor"><div id="controlBar2ButtonText">Einstellungen</div></div>
    <div id="controlBar2" class="shadow">
      <div id="controlBar2Header" class="bgcolor">
        <h1>Feinstaub in Stuttgart</h1>
        <!--<h2>Einstellungen</h2>-->
			<p>Dargestellt wird das fließende 24-Stunden-Mittel der Feinstaub-P10-Werte der einzelnen Stuttgarter Stadtbezirke (Grundlage: Sensoren vom OK Lab Stuttgart) und, zum Vergleich, das fließende 24-Stunden-Mittel zweier Meßstellen des LUBW.</p>
			<p>Das Diagramm reagiert auf Mausbewegungen – was natürlich für Smartphones keine Lösung darstellt.</p>
			<!--<pre><?php print_r($districts); ?></pre>-->
        <!--<button id="toggleAreas" class="toggle">Streuung <span class="display" style="display: none;">aus</span><span class="display">ein</span>blenden</button>
        <button id="toggleLUBW" class="toggle">LUBW-Daten <span class="display" style="display: none;">ein</span><span class="display">aus</span>blenden</button>-->
						<label>
						  Farbtabelle
						  <select id="color-mode" class="form-control">
							<option value="AQI" selected="selected">Air Quality Index</option>
							<option value="GreenRedPink">Grün-Rot-Pink</option>
						  </select>
						</label>
		<!--<p id="debugger"></p>-->
      </div>
      <div id="controlBar2Footer">
        <!--<h2>Hilfe</h2>-->
        <iframe src="/feinstaub/help/?context=districts"></iframe>
      </div>
    </div>
		<span id="copyright">Version <?php echo $version; ?> | Daten: <span id="timestamp"></span></span>
  </div>
<script>

var margin = 80,
    margin_left = 60,
    margin_right = 40,
    inner_margin_right = 100,
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

var hoverTimeFormat = d3.timeFormat("%d.%m., %H Uhr");
var versionTimeFormat = d3.timeFormat("%d.%m.%Y, %H:%M");



var graph = d3.select("#graph")
    .attr("width", width + margin_left + margin_right )
    .attr("height", height + margin_top + margin_bottom)
  .append("g")
    .attr("transform", "translate(" + margin_left + "," + margin_top + ")");

<?php
foreach($districts as $dataset){
?>
var P1floating_<?php echo $dataset; ?> = d3.line()
    .x(function(d) { return xScale(d.timestamp); })
    .y(function(d) { return yScale(d.P1floating_<?php echo $dataset; ?>); })
	.defined(function(d) { return d; });
<?php
}
?>
var line_statDEBW013pm10 = d3.line()
    .x(function(d2) { return xScale(d2.timestamp); })
    .y(function(d2) { return yScale(d2.statDEBW013pm10); });

var line_statDEBW118pm10 = d3.line()
    .x(function(d2) { return xScale(d2.timestamp); })
    .y(function(d2) { return yScale(d2.statDEBW118pm10); });

var parseDate = d3.timeParse("%Y%m%d%H%M%S");

var bisectDate = d3.bisector(function(d) { return d.timestamp; }).left;

// Define the div for the tooltip
var div = d3.select("body").append("div")	
    .attr("class", "tooltip")				
    .style("opacity", 0);

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
        .attr("class", "P1 statDEBW013pm10")
        .attr("d", line_statDEBW013pm10)
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".statDEBW013pm10").addClass("hover");
            $(".statDEBW013pm10").removeClass("fadeout");
            $("#statDEBW013pm10_text").addClass("texthover");
			/*data2map(d3.mouse(this)[0]);*/
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" P10 24h-Mittel des LUBW-Sensors Bad Cannstatt"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".statDEBW013pm10").removeClass("hover");
            $("#statDEBW013pm10_text").removeClass("texthover");
            div.style("opacity", 0);	
        });
        
    graph.append("path")
        .datum(data2)
        .attr("id", "line_statDEBW118pm10")
        .attr("class", "P1 statDEBW118pm10")
        .attr("d", line_statDEBW118pm10)
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".statDEBW118pm10").addClass("hover");
            $(".statDEBW118pm10").removeClass("fadeout");
            $("#statDEBW118pm10_text").addClass("texthover");
			/*data2map(d3.mouse(this)[0]);*/
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" P10 24h-Mittel des LUBW-Sensors Neckartor"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".statDEBW118pm10").removeClass("hover");
            $("#statDEBW118pm10_text").removeClass("texthover");
            div.style("opacity", 0);	
        });
  });
}

function append_data(){
d3.tsv("../chronological_districts_v2_simple.tsv", function(error, data) {
  if (error) throw error;

  data.forEach(function(d) {
    d.timestamp = parseDate(d.timestamp);
<?php foreach($districts as $dataset){ ?>
d.P1floating_<?php echo $dataset; ?> = +d.P1floating_<?php echo $dataset; ?>;
<?php } ?>
  });
  // var parseDate = d3.timeParse("%Y%m%d%H%M%S");

	$("#timestamp").html(versionTimeFormat(d3.max(data, function(d) { return d.timestamp; })));
  xScale.domain(d3.extent(data, function(d) { return d.timestamp; }));
  //y.domain([d3.min(data, function(d) { return d.P2low; }), d3.max(data, function(d) { return d.P1high; })]);
  yScale.domain([0, d3.max(data, function(d) { return Math.max(d.P1floating_<?php echo join(",d.P1floating_",$districts); ?>); })]);
  //y.domain([d3.min(data, function(d) { return 0; }), d3.max(data, function(d) { return 250; })]);

// official data
append_lubw();

  graph.append("line")
    .attr("id", "overline")
    .attr("x1", 0)
    .attr("y1", 0)
    .attr("x2", 0)
    .attr("y2", height);

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
	  
// Overlay to get moves
var view = graph.append("rect")
      .attr("id", "overlay_rect")
      .attr("class", "overlay")
      .attr("width", width)
      .attr("height", height)
      //.on("mouseover", function() { focus.style("display", null); })
      //.on("mouseout", function() { focus.style("display", "none"); })
      .on("mousemove", function(d) {
			data2map(mousemove(d3.mouse(this)));
			}
		);
	  

/**
 *
 */
function data2map(d){
	var out = "";
<?php
$counter = 0;
foreach($districts as $dataset){ ?>
	districts[<?php echo $counter; ?>].set("P1floating",d.P1floating_<?php echo $dataset; ?>);
	out+=<?php echo $counter; ?>+".P1floating: "+districts[<?php echo $counter; ?>].get("P1floating")+", ";
<?php
$counter++;
} ?>

	graph.select("#overline")
		.attr("x1",xScale(d.timestamp))
		.attr("x2",xScale(d.timestamp));

	var select_color_mode = document.getElementById('color-mode');
	var function_name = "styleFunction";
	function_name = function_name+select_color_mode.value+"P10floating";
	vectorDistricts.setStyle(eval(function_name));
	$("#debugger").html(out);
	$("#mapTimeInfo").html("P10: 24h-Mittel am "+hoverTimeFormat(d.timestamp));
	map.render();
}
<?php foreach($districts as $dataset){ ?>

  graph.append("path")
      .datum(data)
      .attr("id", "P1floating_<?php echo $dataset; ?>")
      .attr("class", "P1 <?php echo $dataset; ?>")
      .attr("d", P1floating_<?php echo $dataset; ?>)
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".<?php echo $dataset; ?>").addClass("hover");
            $(".<?php echo $dataset; ?>").removeClass("fadeout");
            $("#<?php echo $dataset; ?>_text").addClass("texthover");
			data2map(mousemove(d3.mouse(this)));
            div	.html(hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+" P10 24h-Mittel in <?php echo urldecode(str_replace(["Stuttgart","_"]," ",$dataset)); ?>"+": "+(Math.round(mousemove(d3.mouse(this)).P1floating_<?php echo $dataset; ?>))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".<?php echo $dataset; ?>").removeClass("hover");
            $("#<?php echo $dataset; ?>_text").removeClass("texthover");
            div.style("opacity", 0);	
        });

<?php } ?>



// Infos
var infox = 10;
var infoy = 0;

<?php
$counter = 0;
$step = 12;
foreach($districts as $dataset){ ?>
  graph.append("line")
    .attr("class", "P1 legend_line <?php echo $dataset; ?>")
    .attr("x1", width-infox)
    .attr("y1", infoy+<?php echo ($counter*$step);?>)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+<?php echo ($counter*$step);?>)
      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
            $(".<?php echo $dataset; ?>").addClass("hover");
            $(".<?php echo $dataset; ?>").removeClass("fadeout");
            $("#<?php echo $dataset; ?>_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".<?php echo $dataset; ?>").removeClass("hover");
            $("#<?php echo $dataset; ?>_text").removeClass("texthover");
        });
    
  graph.append("text")
      .text("<?php echo urldecode(str_replace(["Stuttgart","_"]," ",$dataset)); ?>")
      .attr("class", "legend_text")
	  .attr("id", "<?php echo $dataset; ?>_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+3+<?php echo ($counter*$step);?>)
			      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
			$(".<?php echo $dataset; ?>").addClass("hover");
            $("#<?php echo $dataset; ?>_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".<?php echo $dataset; ?>").removeClass("hover");
            $("#<?php echo $dataset; ?>_text").removeClass("texthover");
        });
<?php
$counter++;
} ?>

  graph.append("line")
    .attr("class", "P1 legend_line statDEBW118pm10")
    .attr("x1", width-infox)
    .attr("y1", infoy+<?php echo ($counter*$step);?>)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+<?php echo ($counter*$step);?>)
      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
            $(".statDEBW118pm10").addClass("hover");
            $(".statDEBW118pm10").removeClass("fadeout");
            $("#statDEBW118pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".statDEBW118pm10").removeClass("hover");
            $("#statDEBW118pm10_text").removeClass("texthover");
        });
    
  graph.append("text")
      .text("LUBW Neckartor")
      .attr("class", "legend_text")
	  .attr("id", "statDEBW118pm10_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+3+<?php echo ($counter*$step);?>)
			      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
			$(".statDEBW118pm10").addClass("hover");
            $("#statDEBW118pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".statDEBW118pm10").removeClass("hover");
            $("#statDEBW118pm10_text").removeClass("texthover");
        });

  graph.append("line")
    .attr("class", "P1 legend_line statDEBW013pm10")
    .attr("x1", width-infox)
    .attr("y1", infoy+<?php echo (($counter+1)*$step);?>)
    .attr("x2", width-infox+20)
    .attr("y2", infoy+<?php echo (($counter+1)*$step);?>)
      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
            $(".statDEBW013pm10").addClass("hover");
            $(".statDEBW013pm10").removeClass("fadeout");
            $("#statDEBW013pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".statDEBW013pm10").removeClass("hover");
            $("#statDEBW013pm10_text").removeClass("texthover");
        });
    
  graph.append("text")
      .text("LUBW Bad Cannstatt")
      .attr("class", "legend_text")
	  .attr("id", "statDEBW013pm10_text")
      .attr("x",width-infox+30)
      .attr("y",infoy+3+<?php echo (($counter+1)*$step);?>)
			      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
			$(".statDEBW013pm10").addClass("hover");
            $("#statDEBW013pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".statDEBW013pm10").removeClass("hover");
            $("#statDEBW013pm10_text").removeClass("texthover");
        });


<?php foreach($districts as $dataset){ ?>

if(d3.max(data, function(d) { return d.P1floating_<?php echo $dataset; ?>; }) <= 0){
	$("#<?php echo $dataset; ?>_text").html($("#<?php echo $dataset; ?>_text").html()+" (keine Werte)");
	$(".<?php echo $dataset; ?>").addClass("hiddenItem");
}
<?php } ?>
// Focus
  function mousemove(pos) {
    var x0 = xScale.invert(pos[0]),
        i = bisectDate(data, x0, 1),
        d0 = data[i - 1],
        d1 = data[i],
		d = d0;
	if(d1 !== null && typeof d1 === 'object')
		d = x0 - d0.date > d1.date - x0 ? d1 : d0;
	return d;
  }

    
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

	graph.select("#overline")
		//.attr("x1",timecode)
		//.attr("x2",timecode)
		.attr("y1",0)
		.attr("y2",height);


    /* Force D3 to recalculate and update the lines and areas */
<?php foreach($districts as $dataset){ ?>
    graph.select('#P1floating_<?php echo $dataset; ?>')
      .attr("d", P1floating_<?php echo $dataset; ?>);      
<?php } ?>

    graph.select('#line_statDEBW118pm10')
      .attr("d", line_statDEBW118pm10);
    graph.select('#line_statDEBW013pm10')
      .attr("d", line_statDEBW013pm10);
      
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
      .attr("x1", width-120)
      .attr("x2", width-100);
    graph.selectAll(".legend_text")
      .attr("x", width-90);
      
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
                      
          if ($_GET["help"]=="hide") {
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
		<script>
	var view = new ol.View({
		  projection: 'EPSG:3857',
		  maxZoom: 10,
		  center: ol.proj.fromLonLat([9.193, 48.786]),
		  zoom: 10
	});
	// Get Stuttgart Geodata
	var jsonDistricts = <?php echo file_get_contents($data_root."stuttgart_districts_v2.json");?>;

	var districts = (new ol.format.GeoJSON())
	.readFeatures(
					  jsonDistricts,
					  {
						  featureProjection: 'EPSG:3857'
					  }
				  );
	
	var districtsTimestamp = <?php echo filemtime($data_root."stuttgart_districts_v2.json"); ?>;
	var districtsDateTime = "<?php echo date("d.m.Y, H:i",filemtime($data_root."stuttgart_districts_v2.json")); ?>";
	
	var vectorSourceStuttgart = new ol.source.Vector({
	  features: districts
	});

      var vectorDistricts = new ol.layer.Vector({
        source: vectorSourceStuttgart,
        style: styleFunctionAQIP10floating,
		opacity: 1
      });
	  
	  //geoguniheidelbergMapLayer.setSaturation(0);

      var map = new ol.Map({
		layers: [
		  vectorDistricts
        ],
        target: 'mapdiv',
        controls: [],
		interactions: [],
		/*.extend([
          new ol.control.FullScreen()
        ])*/
        view: view
      });

		</script>
</body>
</html>
