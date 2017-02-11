<?php
$version = "1.5.2 Github";

include_once("../library.php");

$tsvData = file_get_contents($data_root."chronological_districts_v2_simple.tsv");
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
    <meta http-equiv="cache-control" content="max-age=86400" />
	<meta http-equiv="cache-control" content="public" />
	<meta property="og:title" content="Feinstaub in Stuttgart">
	<meta property="og:description" content="Hier finden Sie die OpenData-Feinstaubmessungen von OK Lab Stuttgart nach Stadtteilen geordnet und als Diagramm aufbereitet.">
	<meta property="og:image" content="<?php echo $url; ?>/districts/districts.png">
	<meta property="og:url" content="<?php echo $url; ?>/districts/">
    <script src="../js/d3.v4.min.js" type="text/javascript"></script>
    <script src="../js/jquery.min.js" type="text/javascript"></script>
	<script src="../js/ol.js" type="text/javascript"></script>
    <script src="../library.js" type="text/javascript"></script>
	<link rel="stylesheet" href="../js/ol.css" type="text/css" media="all">
	<link rel="stylesheet" href="../styles.css" type="text/css" media="all">
	<link rel="stylesheet" href="chart.css" type="text/css" media="all">

    <!--
	ToDo
	- reduce size (1.5MB)
	1.5.2
	zoom
	moved to Github
	
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
</style>
</head>
  <body>
    <div id="chart">
		<svg id="graph"></svg>
		<svg id="graph2"></svg>
		<div id="mapdiv"><div id="mapTimeInfo">Aktuelles 24h-Mittel PM10</div></div>
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
/**
 * Step 1: Load the map
 */
var view = new ol.View({
	  projection: 'EPSG:3857',
	  maxZoom: 10,
	  center: ol.proj.fromLonLat([9.193, 48.786]),
	  zoom: 10
});
// Get Stuttgart Geodata
var jsonDistricts = "";
$.get( "../data/stuttgart_districts.json", function( data ) {
	console.log("stuttgart_districts.json loaded");
	jsonDistricts = data;

	districts = (new ol.format.GeoJSON())
		.readFeatures(
						  jsonDistricts,
						  {
							  featureProjection: 'EPSG:3857'
						  }
					  );

	var vectorSourceStuttgart = new ol.source.Vector({
	  features: districts
	});

	vectorDistricts = new ol.layer.Vector({
	  source: vectorSourceStuttgart,
	  style: styleFunctionAQIPM10floating,
	  opacity: 1
	});
  
	map = new ol.Map({
	  layers: [
		vectorDistricts
	  ],
	  target: 'mapdiv',
	  controls: [],
	  interactions: [],
	  view: view
	});
    init_map();
}); // end of $.get( "../data/stuttgart_districts.json", function( data ) {

/**
 * Step 2: Init stuff for the line chart 
 */

var margin = 80,
    margin_left = 70,
    margin_right = 200, //40
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

var graph2 = d3.select("#graph2")
    .attr("width", 200 )
    .attr("height", height + margin_top + margin_bottom)
  .append("g")
    .attr("transform", "translate(0," + margin_top + ")");

  
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

/**
 * Load governmental data
 */
function append_lubw(){
  // statDEBW013pm10 aka Gnesener Straße
  d3.tsv("/<?php echo $data_dir; ?>data_lubw.tsv", function(error, data2) {
    if (error) throw error;
	console.log("data_lubw.tsv loaded");
  
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
		.attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".statDEBW013pm10").addClass("hover");
            $(".statDEBW013pm10").removeClass("fadeout");
            $("#statDEBW013pm10_text").addClass("texthover");
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" PM10 24h-Mittel des LUBW-Sensors Bad Cannstatt"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
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
		.attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".statDEBW118pm10").addClass("hover");
            $(".statDEBW118pm10").removeClass("fadeout");
            $("#statDEBW118pm10_text").addClass("texthover");
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" PM10 24h-Mittel des LUBW-Sensors Neckartor"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
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

/**
 * Load citizen science data
 */
var data = false;
function append_data(){
	d3.tsv("/<?php echo $data_dir; ?>chronological_districts_v2_simple.tsv", function(error, data) {
	if (error) throw error;
	console.log("chronological_districts_v2_simple.tsv loaded");

  data.forEach(function(d) {
    d.timestamp = parseDate(d.timestamp);
<?php foreach($districts as $dataset){ ?>
d.P1floating_<?php echo $dataset; ?> = +d.P1floating_<?php echo $dataset; ?>;
<?php } ?>
  });
  init_map();
  // var parseDate = d3.timeParse("%Y%m%d%H%M%S");

	$("#timestamp").html(versionTimeFormat(d3.max(data, function(d) { return d.timestamp; })));
  xScale.domain(d3.extent(data, function(d) { return d.timestamp; }));
  //y.domain([d3.min(data, function(d) { return d.P2low; }), d3.max(data, function(d) { return d.P1high; })]);
  yScale.domain([0, d3.max(data, function(d) { return Math.max(d.P1floating_<?php echo join(",d.P1floating_",$districts); ?>); })]);
  //y.domain([d3.min(data, function(d) { return 0; }), d3.max(data, function(d) { return 250; })]);

/**
 * Step 4: Load governmental data
 */
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
      .on("mousemove", function(d) {data2map(mousemove(d3.mouse(this)));});

/**
 * Zoom
 */
new_xScale = xScale;
graph.call(d3.zoom().on("zoom", zoom));
function zoom(){
	//console.log("zoom");
	new_xScale = d3.event.transform.rescaleX(xScale);
	// re-scale y axis during zoom; ref [2]
	graph.select('.x.axis')
      //.duration(50)
      .call(xAxis.scale(new_xScale));
	//graph.select('#P1floating_Stuttgart_Feuerbach')
		//.attr("cx", function(d) { console.log(new_xScale(d[1])); return new_xScale(d[1]); });
	//	.attr("cx", new_xScale(d[1]));
	//graph.select('.P1').attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
<?php foreach($districts as $dataset){ ?>
  graph.select("#P1floating_<?php echo $dataset; ?>").attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
<?php } ?>
  graph.select("#line_statDEBW118pm10").attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
  graph.select("#line_statDEBW013pm10").attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
  graph.select("#overlay_rect").attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
}
graph.on('mousemove', function () {
   coordinates = d3.mouse(this);
   //console.log(coordinates[0]);
	graph.select("#overline")
		.attr("x1",coordinates[0])
		.attr("x2",coordinates[0]);
});
/**
 * update map
 */
function data2map(d){
	//alert(d);
	if(d){
<?php
$counter = 0;
foreach($districts as $dataset){ ?>
	districts[<?php echo $counter; ?>].set("P1floating",d.P1floating_<?php echo $dataset; ?>);
<?php
$counter++;
} ?>
		$("#mapTimeInfo").html("PM10: 24h-Mittel am "+hoverTimeFormat(d.timestamp));
	}
	var select_color_mode = document.getElementById('color-mode');
	var function_name = "styleFunction";
	function_name = function_name+select_color_mode.value+"PM10floating";
	vectorDistricts.setStyle(eval(function_name));
	map.render();
}
<?php foreach($districts as $dataset){ ?>

  graph.append("path")
      .datum(data)
      .attr("id", "P1floating_<?php echo $dataset; ?>")
      .attr("class", "P1 <?php echo $dataset; ?>")
      .attr("d", P1floating_<?php echo $dataset; ?>)
	  .attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function(d) {		
			highlight_district_on_map("<?php echo $dataset; ?>");
            $(".P1").addClass("fadeout");
            $(".<?php echo $dataset; ?>").addClass("hover");
            $(".<?php echo $dataset; ?>").removeClass("fadeout");
            $("#<?php echo $dataset; ?>_text").addClass("texthover");
			data2map(mousemove(d3.mouse(this)));
            div	.html(((Math.round(mousemove(d3.mouse(this)).P1floating_<?php echo $dataset; ?>))==-1)?hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+": keine Daten für <?php echo urldecode(str_replace(["Stuttgart","_"]," ",$dataset)); ?>":hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+" PM10 24h-Mittel in <?php echo urldecode(str_replace(["Stuttgart","_"]," ",$dataset)); ?>"+": "+(Math.round(mousemove(d3.mouse(this)).P1floating_<?php echo $dataset; ?>))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");
            })					
        .on("mouseout", function(d) {		
			highlight_district_on_map("");
            $(".P1").removeClass("fadeout");
            $(".<?php echo $dataset; ?>").removeClass("hover");
            $("#<?php echo $dataset; ?>_text").removeClass("texthover");
            div.style("opacity", 0);	
			data2map(mousemove(d3.mouse(this)));
        });

<?php } ?>
/**
 *
 */
function highlight_district_on_map(district_name){
<?php
$counter = 0;
foreach($districts as $dataset){ ?>
	districts[<?php echo $counter; ?>].set("fadeout",(district_name=="")?0:(district_name=="<?php echo $dataset; ?>")?0:1);
<?php
$counter++;
} ?>
	console.log(district_name);
}

// Infos
var infox = 10;
var infoy = 0;

<?php
$counter = 0;
$step = 12;
foreach($districts as $dataset){ ?>
  graph2.append("line")
    .attr("class", "P1 legend_line <?php echo $dataset; ?>")
    .attr("x1", infox)
    .attr("y1", infoy+<?php echo ($counter*$step);?>)
    .attr("x2", infox+20)
    .attr("y2", infoy+<?php echo ($counter*$step);?>)
      .on("mouseover", function(d) {		
			highlight_district_on_map("<?php echo $dataset; ?>");
            $(".P1").addClass("fadeout");
            $(".<?php echo $dataset; ?>").addClass("hover");
            $(".<?php echo $dataset; ?>").removeClass("fadeout");
            $("#<?php echo $dataset; ?>_text").addClass("texthover");
			data2map(false);
            })					
        .on("mouseout", function(d) {		
			highlight_district_on_map("");
            $(".P1").removeClass("fadeout");
            $(".<?php echo $dataset; ?>").removeClass("hover");
            $("#<?php echo $dataset; ?>_text").removeClass("texthover");
			data2map(false);
        });
    
  graph2.append("text")
      .text("<?php echo urldecode(str_replace(["Stuttgart","_"]," ",$dataset)); ?>")
      .attr("class", "legend_text")
	  .attr("id", "<?php echo $dataset; ?>_text")
      .attr("x",infox+30)
      .attr("y",infoy+3+<?php echo ($counter*$step);?>)
		.on("mouseover", function(d) {
			highlight_district_on_map("<?php echo $dataset; ?>");
            $(".P1").addClass("fadeout");
			$(".<?php echo $dataset; ?>").addClass("hover");
            $("#<?php echo $dataset; ?>_text").addClass("texthover");
			data2map(false);
            })					
        .on("mouseout", function(d) {		
			highlight_district_on_map("");
            $(".P1").removeClass("fadeout");
            $(".<?php echo $dataset; ?>").removeClass("hover");
            $("#<?php echo $dataset; ?>_text").removeClass("texthover");
			data2map(false);
        });
<?php
$counter++;
} ?>

  graph2.append("line")
    .attr("class", "P1 legend_line statDEBW118pm10")
    .attr("x1", infox)
    .attr("y1", infoy+<?php echo ($counter*$step);?>)
    .attr("x2", infox+20)
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
    
  graph2.append("text")
      .text("LUBW Neckartor")
      .attr("class", "legend_text")
	  .attr("id", "statDEBW118pm10_text")
      .attr("x",infox+30)
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

  graph2.append("line")
    .attr("class", "P1 legend_line statDEBW013pm10")
    .attr("x1", infox)
    .attr("y1", infoy+<?php echo (($counter+1)*$step);?>)
    .attr("x2", infox+20)
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
    
  graph2.append("text")
      .text("LUBW Bad Cannstatt")
      .attr("class", "legend_text")
	  .attr("id", "statDEBW013pm10_text")
      .attr("x",infox+30)
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
/**
 *
 */
  function resize() {
		if(window.innerWidth > 800){
			if(!controlVisible && !infoVisible){
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

/**
 * Step 3: Load citizen science data
 */
append_data();

d3.select(window).on('resize', resize);

/**
 * set map to most recent data
 * very ugly way...
 */
function init_map(){
    if($.isArray(districts)){
        data.forEach(function(d) {
            $("#mapTimeInfo").html("PM10: 24h-Mittel am "+hoverTimeFormat(d.timestamp));
<?php
$counter = 0;
foreach($districts as $dataset){ ?>
	districts[<?php echo $counter; ?>].set("P1floating",d.P1floating_<?php echo $dataset; ?>);
<?php
$counter++;
} ?>
        });
        map.render();
        console.log("map initialized with most recent data");
    }
}
</script>
</body>
</html>
