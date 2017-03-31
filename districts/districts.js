console.log("Loading...");
var week = ($_GET.domain=="week")?true:false;
var diagram_type_abstract = ($_GET.diagram_type=="abstract")?true:false;
var diagram_data_current = ($_GET.data=="current")?true:false;
log("Domain: "+((week)?"just seven days":"complete data"));
log("Diagram type: "+((diagram_type_abstract)?"abstract":"details"));

var var_basename = "P1"+((diagram_data_current)?"h":"floating")+"_";

var currentData = false;

/**
 * Step 1: Load the map
 */

// Get Stuttgart Geodata
var jsonDistricts = "";
var districtNames = [];
var newStyles = "";

var map_width  = 150;
var map_height = 150;

var P1max_in_range = 0;

var vis = d3.select("#mapdiv").append("svg")
            .attr("width", map_width).attr("height", map_height);

var geodata_file = "../data/stuttgart_districts.json";
$.get( geodata_file, function( data ) {
	log("Map ("+geodata_file+") loaded");

      var projection = d3.geoMercator().fitSize([map_width, map_height], data);
      var path = d3.geoPath().projection(projection);
        
      vis.selectAll('path')
        .data(data.features)
        .enter()
        .append('path')
        .attr('d', path)
        .style("fill", "#fff")
        .style("stroke-width", "1")
        .style("stroke", "black");
      
	// generate styles for districts
	for(counter=0;counter<data.features.length;counter++){
		var h = 365/Object.keys(data.features).length*counter;
		var style = "."+data.features[counter].properties.name.replace(/ /g, "_")+" { stroke: hsl("+Math.round(h)+", 90%, 45%); }\n";
		newStyles+=style;
		districtNames.push(data.features[counter].properties.name);
	}

	newStyles+= ".Median { stroke: hsl(50, 90%, 45%); }\n";
	newStyles+= ".area { fill: hsla(50, 90%, 45%, 0.2); }\n";
	//$("<style type='text/css'>"+newStyles+"</style>").appendTo("head");
	var el = document.querySelector('head');
	el.innerHTML += "<style type='text/css'>"+newStyles+"</style>";
	
	if(!diagram_type_abstract){
		curves = [];
		districtNames.forEach(function(val) {
			eval("curves[\""+var_basename+val.replace(/ /g, "_")+"\"] = d3.line().x(function(d) { return xScale(d.timestamp); }).y(function(d) { return yScale(d."+var_basename+val.replace(/ /g, "_")+"); }).defined(function(d) {return !isNaN(d."+var_basename+val.replace(/ /g, "_")+"); });");
		});
		
	}
	else{
		curves[var_basename+"Median"] = d3.line()
			.x(function(d) {return xScale(d.timestamp);})
			.y(function(d) {return yScale(d[var_basename+"Median"]);})
			.defined(function(d) {return !isNaN(d[var_basename+"Median"]);});
		// P10
		curves[var_basename+"Area"] = d3.area()
			.x(function(d) { return xScale(d.timestamp); })
			.y0(function(d) { return yScale(d[var_basename+"Min"]); })
			.y1(function(d) { return yScale(d[var_basename+"Max"]); })
			.defined(function(d) {
				if(isNaN(d[var_basename+"Min"]) || isNaN(d[var_basename+"Max"]))
					return false;
				else
					return true;
			});
	}
	

    initMap();
    /**
      * Step 3: Load citizen science data
      */
     append_data();
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

var hoverTimeFormat = d3.timeFormat("%d.%m.%Y, %H Uhr");
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

var line_DEBW013pm10 = d3.line()
    .x(function(d2) { return xScale(d2.timestamp); })
    .y(function(d2) { return yScale(d2.DEBW013pm10); })
    .defined(function(d2) {return !isNaN(d2.DEBW013pm10); });

var line_DEBW118pm10 = d3.line()
    .x(function(d2) { return xScale(d2.timestamp); })
    .y(function(d2) { return yScale(d2.DEBW118pm10); })
    .defined(function(d2) {return !isNaN(d2.DEBW118pm10); });

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
	if(diagram_data_current) return false; // say no to Äpfel with Birnen
  // DEBW013pm10 aka Gnesener Straße
  var data_file = "../data/chronological_data_lubw.tsv";
  d3.tsv(data_file, function(error, data2) {
    if (error) throw error;
	log("LUBW data ("+data_file+") loaded");
  
    data2.forEach(function(d2) {
      d2.timestamp = parseDate(d2.timestamp);
      d2.DEBW013pm10 = +((d2.DEBW013pm10>0)?d2.DEBW013pm10:NaN);
      d2.DEBW118pm10 = +((d2.DEBW118pm10>0)?d2.DEBW118pm10:NaN);
    });
    
    graph.append("path")
        .datum(data2)
        .attr("id", "line_DEBW013pm10")
        .attr("class", "P1 graph DEBW013pm10")
        .attr("d", line_DEBW013pm10)
		.attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function() {		
            $(".P1").addClass("fadeout");
            $(".DEBW013pm10").addClass("hover");
            $(".DEBW013pm10").removeClass("fadeout");
            $("#DEBW013pm10_text").addClass("texthover");
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" PM10 24h-Mittel des LUBW-Sensors Bad Cannstatt"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function() {		
            $(".P1").removeClass("fadeout");
            $(".DEBW013pm10").removeClass("hover");
            $("#DEBW013pm10_text").removeClass("texthover");
            div.style("opacity", 0);	
        });
        
    graph.append("path")
        .datum(data2)
        .attr("id", "line_DEBW118pm10")
        .attr("class", "P1 graph DEBW118pm10")
        .attr("d", line_DEBW118pm10)
		.attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function() {		
            $(".P1").addClass("fadeout");
            $(".DEBW118pm10").addClass("hover");
            $(".DEBW118pm10").removeClass("fadeout");
            $("#DEBW118pm10_text").addClass("texthover");
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" PM10 24h-Mittel des LUBW-Sensors Neckartor"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function() {		
            $(".P1").removeClass("fadeout");
            $(".DEBW118pm10").removeClass("hover");
            $("#DEBW118pm10_text").removeClass("texthover");
            div.style("opacity", 0);	
        });
      close_log();
  });
}

/**
 * Load citizen science data
 */
var citizen_science_data = false;
var district_data_file = "../data/chronological_districts_v2_"+((diagram_data_current)?"complete":"simple")+((week)?"_week":"")+".tsv";
console.log(district_data_file);
var P1max = 0;
function append_data(){
	d3.tsv(district_data_file, function(error, data) {
	if (error) throw error;
	log("District data ("+district_data_file+") loaded");

	data.forEach(function(d) {
		d.timestamp = parseDate(d.timestamp);
		var tmpP1Data = [];
		districtNames.forEach(function(val) {
			if(d[var_basename+val.replace(/ /g, "_")] > 0){
				d[var_basename+val.replace(/ /g, "_")] = + d[var_basename+val.replace(/ /g, "_")];
				tmpP1Data.push(d[var_basename+val.replace(/ /g, "_")]);
			}
			else{
				d[var_basename+val.replace(/ /g, "_")] = + NaN;
			}
		});
		if(tmpP1Data.length > 0){
			d[var_basename+"Median"] = + d3.median(tmpP1Data);
			d[var_basename+"Max"] = + d3.max(tmpP1Data);
			d[var_basename+"Min"] = + d3.min(tmpP1Data);
		}
		else{
			d[var_basename+"Median"] = + NaN;
			d[var_basename+"Max"] = + NaN;
			d[var_basename+"Min"] = + NaN;
		}

		if(P1max < d[var_basename+"Max"]) P1max = d[var_basename+"Max"];
	});
	log("P1max: "+P1max);
	P1max_in_range = P1max;
	citizen_science_data = data;
	initMap();
	document.getElementById("timestamp").innerHTML = versionTimeFormat(d3.max(data, function(d) { return d.timestamp; }));
	xScale.domain(d3.extent(data, function(d) { return d.timestamp; }));
	yScale.domain([0, 1.3*P1max]);

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
      .on("mousemove", function() {data2map(mousemove(d3.mouse(this)));});

/**
 * Zoom
 */
var min_xScale = xScale.domain()[0];
var max_xScale = xScale.domain()[1];
new_xScale = xScale;
new_yScale = yScale;
P1max_in_range = P1max;
//if(!week) graph.call(d3.zoom().on("zoom", zoom));
function zoom(){
	log(d3.event.transform);
	xScale.domain([xScale.domain()[0]-(d3.event.transform.x*-1),xScale.domain()[1]-(d3.event.transform.x*-1)]);
	yScale.domain([0,getMaxYinXrange(xScale.domain())*1.3]);

	graph.select('.x.axis').call(xAxis.scale(xScale));
	
	$(".graph").attr("transform", "translate(" + d3.event.transform.x+',0 )');

	graph.select('#yaxis').call(yAxis1.scale(yScale));
	graph.select('#y2axis').call(yAxis2.scale(yScale));

}


graph.on('mousemove', function () {
   coordinates = d3.mouse(this);
	graph.select("#overline")
		.attr("x1",coordinates[0])
		.attr("x2",coordinates[0]);
});



/**
 * init graphs
 */
if(!diagram_type_abstract){
	districtNames.forEach(function(val) {
	  graph.append("path")
		  .datum(data)
		  .attr("id", var_basename+val.replace(/ /g, "_"))
		  .attr("class", "P1 graph "+val.replace(/ /g, "_"))
		  .attr("d", curves[var_basename+val.replace(/ /g, "_")])
		  .attr( 'vector-effect' , 'non-scaling-stroke' )
		  .on("mousemove", function() {		
				highlightRegionOnMap(val.replace(/ /g, "_"));
				$(".P1").addClass("fadeout");
				$("."+val.replace(/ /g, "_")).addClass("hover");
				$("."+val.replace(/ /g, "_")).removeClass("fadeout");
				$("#"+val.replace(/ /g, "_")+"_text").addClass("texthover");
				data2map(mousemove(d3.mouse(this)));
				div	.html(((Math.round(mousemove(d3.mouse(this))[var_basename+val.replace(/ /g, "_")]))==-1)?hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+": keine Daten für "+val.replace(/Stuttgart /, ""):hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+" PM10 24h-Mittel in "+val.replace(/Stuttgart /, "")+": "+(Math.round(mousemove(d3.mouse(this))[var_basename+val.replace(/ /g, "_")]))+" µg/m³")	
					.style("opacity", 0.9)	
					.style("left", (d3.event.pageX) + "px")		
					.style("top", (d3.event.pageY - 28) + "px");
				})					
			.on("mouseout", function() {		
				highlightRegionOnMap("");
				$(".P1").removeClass("fadeout");
				$("."+val.replace(/ /g, "_")).removeClass("hover");
				$("#"+val.replace(/ /g, "_")+"_text").removeClass("texthover");
				div.style("opacity", 0);	
				data2map(mousemove(d3.mouse(this)));
			});
	});
}
else{
    graph.append("path")
      .datum(data)
      .attr("id", var_basename+"Area")
      .attr("class", "P1 graph area")
      //.attr("style", "display: none;")
      .attr("d", curves[var_basename+"Area"])
	  		.on("mousemove", function(d) {		
			  $(".P1").addClass("fadeout");
			  $(".area").addClass("hover");
			  $(".area").removeClass("fadeout");
			  $("#area_text").addClass("texthover");
			  data2map(mousemove(d3.mouse(this)));
			  div	.html(((Math.round(mousemove(d3.mouse(this))[var_basename+"Median"]))==-1)?hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+": keine Daten":hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+" PM10 24h-Mittel: "+(Math.round(mousemove(d3.mouse(this))[var_basename+"Min"]))+" - "+(Math.round(mousemove(d3.mouse(this))[var_basename+"Max"]))+" µg/m³")	
				  .style("opacity", 0.9)	
				  .style("left", (d3.event.pageX) + "px")		
				  .style("top", (d3.event.pageY - 28) + "px");
			  })					
		  .on("mouseout", function() {		
			  $(".P1").removeClass("fadeout");
			  $(".area").removeClass("hover");
			  $("#area_text").removeClass("texthover");
			  div.style("opacity", 0);	
			  data2map(mousemove(d3.mouse(this)));
		  });
	graph.append("path")
		.datum(data)
		.attr("id", var_basename+"Median")
		.attr("class", "P1 graph Median")
		.attr("d", curves[var_basename+"Median"])
		.attr( 'vector-effect' , 'non-scaling-stroke' )
		.on("mousemove", function() {		
			  $(".P1").addClass("fadeout");
			  $(".Median").addClass("hover");
			  $(".Median").removeClass("fadeout");
			  $("#Median_text").addClass("texthover");
			  data2map(mousemove(d3.mouse(this)));
			  div	.html(((Math.round(mousemove(d3.mouse(this))[var_basename+"Median"]))==-1)?hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+": keine Daten für Median":hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+" PM10 24h-Mittel im Median"+": "+(Math.round(mousemove(d3.mouse(this))[var_basename+"Median"]))+" µg/m³")	
				  .style("opacity", 0.9)	
				  .style("left", (d3.event.pageX) + "px")		
				  .style("top", (d3.event.pageY - 28) + "px");
			  })					
		  .on("mouseout", function() {		
			  $(".P1").removeClass("fadeout");
			  $(".Median").removeClass("hover");
			  $("#Median_text").removeClass("texthover");
			  div.style("opacity", 0);	
			  data2map(mousemove(d3.mouse(this)));
		  });


}



// Infos
var infox = 10;
var infoy = 0;
var step = 12;

counter = 0;
if(!diagram_type_abstract){
	for(i=0;i<districtNames.length;i++){
		legendAddElement(graph2,districtNames[i].replace(/Stuttgart /g, ""),districtNames[i].replace(/ /g, "_"),"P1",infox,infoy+counter*step,true);
		counter++;
	}
}
else{
	legendAddElement(graph2,"Median","Median","P1",infox,infoy+counter*step,true);
	counter++;
}
if(!diagram_data_current){
	legendAddElement(graph2,"LUBW Neckartor","DEBW118pm10","P1",infox,infoy+counter*step,false);
	counter++;
	legendAddElement(graph2,"LUBW Bad Cannstatt","DEBW013pm10","P1",infox,infoy+counter*step,false);
}
		
// remove districts without values
if(!diagram_type_abstract){
	districtNames.forEach(function(val) {
		if(d3.max(data, function(d) { return d[var_basename+val.replace(/ /g, "_")]; }) === undefined){
			document.getElementById(val.replace(/ /g, "_")+"_text").innerHTML = document.getElementById(val.replace(/ /g, "_")+"_text").innerHTML+" (keine Werte)";
			$("."+val.replace(/ /g, "_")).addClass("hiddenItem");
			//var el = document.querySelector('div');
			//addClass(el, 'hiddenItem');
		}
	});
}

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
  
  	yScale.domain([0,getMaxYinXrange(xScale.domain())*1.3]);

  
  /* Update the range of the scale with new width/height */
  new_xScale.range([0, width]);
  yScale.range([height, 0]);

  /* Update the axis with the new scale */
  graph.select('.x.axis')
	.attr("transform", "translate(0," + height + ")")
	.call(xAxis);

  graph.select('#yaxis')
	.attr("transform", "translate(0,0)")
	.attr("tickSize",width)
	.call(yAxis1);

  graph.select('#y2axis')
	.attr("transform", "translate(" + width + ",0)")
	.attr("tickSize",-width)
	.call(yAxis2);
	
  graph.selectAll('#yaxis line')
	.attr("x2", width);
	
  graph.selectAll('#y2axis line')
	.attr("x2", -width);

  graph.select("#overline")
	  .attr("y1",0)
	  .attr("y2",height);

  /* Force D3 to recalculate and update the lines and areas */
	if(!diagram_type_abstract){
		districtNames.forEach(function(val) {
			graph.select("#"+var_basename+val.replace(/ /g, "_"))
				.attr("d", curves[var_basename+val.replace(/ /g, "_")]);
				//d["P1floating_"+val.replace(/ /g, "_")]
		});
	}
	else{
		graph.select("#"+var_basename+Median)
			.attr("d", curves[var_basename+"Median"]);
		graph.select("#"+var_basename+"Area")
			.attr("d", curves[var_basename+"Area"]);
	}

  graph.select('#line_DEBW118pm10')
	.attr("d", line_DEBW118pm10);
  graph.select('#line_DEBW013pm10')
	.attr("d", line_DEBW013pm10);

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
	setAnimationParameters();
}



d3.select(window).on('resize', resize);

var mapScaleId = "#mapscale";
var mapScaleOrientation = "vertical";
var mapScaleWidth = 40;
var mapScaleHeight = 100;
var mapScaleLut = colorLookupTableAQIPM10;
/**
 * set map to most recent data
 */
function initMap(){
    if($.isFunction(citizen_science_data.forEach)){
		var tmpD = false;
		log("Initializing map");
		tmpD = citizen_science_data[citizen_science_data.length-1];
		for(key=0;key<districtNames.length;key++){
			 d3colorSVG(key,tmpD[var_basename+districtNames[key].replace(/ /g, "_")],"255,255,255");
			 log(key+" "+var_basename+districtNames[key].replace(/ /g, "_"));
		}
		log("Map initialized with most recent data");
		if(!diagram_data_current)
			document.getElementById("mapTimeInfo").innerHTML = "PM10: 24h-Mittel am "+hoverTimeFormat(tmpD.timestamp);
		else
			document.getElementById("mapTimeInfo").innerHTML = "PM10: Stunden-Mittel am "+hoverTimeFormat(tmpD.timestamp);
		currentData = tmpD;
    }
	d3ScaleComplex(mapScaleId,mapScaleOrientation,mapScaleWidth,mapScaleHeight,mapScaleLut);
}
/**
 * update map
 */
function data2map(d = currentData){
	currentData = d;
	if(d){
		for(key=0;key<districtNames.length;key++){
			var value = d[var_basename+districtNames[key].replace(/ /g, "_")];
			if(value<=0) value = NaN;
            d3colorSVG(key,value,"255,255,255");
		}//);
		if(!diagram_data_current)
			document.getElementById("mapTimeInfo").innerHTML = "PM10: 24h-Mittel am "+hoverTimeFormat(d.timestamp);
		else
			document.getElementById("mapTimeInfo").innerHTML = "PM10: Stunden-Mittel am "+hoverTimeFormat(d.timestamp);
	}
}

$("#color-mode").on("change", function() {
	var mode = document.getElementById('color-mode').value;
	if(mode==="AQI") mapScaleLut = colorLookupTableAQIPM10;
	if(mode==="LuQx") mapScaleLut = colorLookupTableLuQxPM10;
	if(mode==="GreenRedPink") mapScaleLut = colorLookupTableGreenRedPink;
	d3ScaleComplex(mapScaleId,mapScaleOrientation,mapScaleWidth,mapScaleHeight,mapScaleLut);
	data2map();
});
/**
 *
 */
function d3colorSVG(element,value,default_color="255,255,255"){
      var mode = document.getElementById('color-mode').value;
      //console.log("value: "+value+" mode: "+mode+" element: "+element+" default_color: "+default_color);
      if(mode==="AQI") color = colorMapping(colorLookupTableAQIPM10,value,default_color);
      if(mode==="LuQx") color = colorMapping(colorLookupTableLuQxPM10,value,default_color);
      if(mode==="GreenRedPink") color = colorMapping(colorLookupTableGreenRedPink,value,default_color);
      $("#mapdiv svg path:nth-of-type("+(element+1)+")").css("fill", "rgb("+color+")");
}
/**
 *
 */
function getMaxYinXrange(domain){
	var max = 0;
	citizen_science_data.forEach(function(d) {
		if(d.timestamp >= domain[0] && d.timestamp <= domain[1]){
			if(max < d[var_basename+"Max"]) max = d[var_basename+"Max"];
		}
	});
	return max;
}
// Animation variables
var animator;
var animation_position = 0;
var animation_speed = 20;
var animation_step = 2;
/**
 * @description Loops over visible data (animation)
 */
function animation(){
	if(citizen_science_data[animation_position].timestamp > xScale.domain()[1])
		animation_position = 0;
	if(citizen_science_data[animation_position].timestamp < xScale.domain()[0])
		animation_position = getIndexFromTimestamp(citizen_science_data, xScale.domain()[0]);
	currentData = citizen_science_data[animation_position];
	var time_width = xScale.domain()[1]-xScale.domain()[0];
	var current_time_width = currentData.timestamp-xScale.domain()[0];
	var factor = current_time_width/time_width;
	var x = graph.select("#overlay_rect").attr("width")*factor;
	graph.select("#overline")
		.attr("x1",x)
		.attr("x2",x);
	data2map();
	animation_position+=animation_step;
	if(animation_position >= citizen_science_data.length-1) animation_position = 0;
}
/**
 * Start animation
 */
function animationStart(){
	animation_position = getIndexFromTimestamp(citizen_science_data, currentData.timestamp);
	animator = setInterval(animation, animation_speed);
	console.log("start animation");
}
/**
 * Stop animation
 */
function animationStop(){
	clearInterval(animator);
	animator = false;
	console.log("stop animation");
}
/**
 * Helper
 */
function getIndexFromTimestamp(my_data, my_timestamp){
	for(var k=0;k<my_data.length;k++){
		if(my_data[k].timestamp >= my_timestamp) {
			break;
		}
	}
	return k;
}
/**
 * Init animation
 */
function setAnimationParameters(){
	var index_low = getIndexFromTimestamp(citizen_science_data, xScale.domain()[0]);
	var index_high = getIndexFromTimestamp(citizen_science_data, xScale.domain()[1]);
	var num_datasets = index_high-index_low;
	console.log("num_datasets: "+num_datasets);
	
	animation_speed = 100;
	animation_step = Math.round(num_datasets/200);
	if(animation_step > 24)
		animation_step = 24;
	else if(animation_step > 12)
		animation_step = 12;
	else if(animation_step > 6)
		animation_step = 6;
	else if(animation_step < 1)
		animation_step = 1;
	console.log("animation_step: "+animation_step);
	console.log("animation_speed: "+animation_speed);
}
/**
 * Zoom of line charts
 */
function zoomByKeyOrWheel(dir){
	//log(xScale.domain());
	var min = currentData.timestamp;
	var max = currentData.timestamp;
	if (dir==1){
		if(xScale.domain()[0]<currentData.timestamp)
			min = currentData.timestamp-(currentData.timestamp-xScale.domain()[0])*0.9;
		if(xScale.domain()[1]>currentData.timestamp){
			max = currentData.timestamp-(xScale.domain()[1]-currentData.timestamp)*-0.9;
		}
	}
	if (dir==-1){
		if(xScale.domain()[0]<currentData.timestamp)
			min = currentData.timestamp-(currentData.timestamp-xScale.domain()[0])*1.1;
		if(xScale.domain()[1]>currentData.timestamp)
			max = currentData.timestamp-(xScale.domain()[1]-currentData.timestamp)*-1.1;
	}
	xScale.domain([min,max]);
	resize();
}
/**
 * Keyboard events
 */
function keyboard(e){
    var evtobj=window.event? event : e; //distinguish between IE's explicit event object (window.event) and Firefox's implicit.
    var unicode=evtobj.charCode? evtobj.charCode : evtobj.keyCode;
    var actualkey=String.fromCharCode(unicode);
	// Animation
	if (actualkey==" "){
		if(animator) animationStop();
		else animationStart();
	}
	// Zoom in
	if (actualkey=="+"){
		zoomByKeyOrWheel(1);
	}
	// Zoom out
	if (actualkey=="-"){
		zoomByKeyOrWheel(-1);
	}
}
// Register keyboard event listener
document.onkeypress=keyboard;

/**
 * Mouse wheel events
 */
function displaywheel(e){
    var evt=window.event || e; //equalize event object
    var delta=evt.detail? evt.detail*(-120) : evt.wheelDelta; //check for detail first so Opera uses that instead of wheelDelta
    //delta returns +120 when wheel is scrolled up, -120 when down
	if(delta > 0) delta = 1;
	if(delta < 0) delta = -1;
	zoomByKeyOrWheel(delta);
}
// Register mouse wheel event listener
var mousewheelevt=(/Firefox/i.test(navigator.userAgent))? "DOMMouseScroll" : "mousewheel"; //FF doesn't recognize mousewheel as of FF3.x
if (document.attachEvent) //if IE (and Opera depending on user setting)
    document.attachEvent("on"+mousewheelevt, displaywheel);
else if (document.addEventListener) //WC3 browsers
    document.addEventListener(mousewheelevt, displaywheel, false);
