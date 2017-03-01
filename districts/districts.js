console.log("Loading...");
var week = ($_GET.mode=="week")?true:false;
log("Data mode: "+((week)?"just seven days":"complete data"));
/**
 * Step 1: Load the map
 */
var view = new ol.View({
	  projection: 'EPSG:3857',
	  maxZoom: 9,
	  center: ol.proj.fromLonLat([9.193, 48.786]),
	  zoom: 9
});
// Get Stuttgart Geodata
var jsonDistricts = "";
var districtNames = [];
var newStyles = "";

var geodata_file = "../data/stuttgart_districts.json";
$.get( geodata_file, function( data ) {
	log("Map ("+geodata_file+") loaded");

	// generate styles for districts
	var counter = 0;
	$.each( data.features, function( key, val ) {
		var h = 365/Object.keys(data.features).length*counter;
		var style = "."+val.properties.name.replace(/ /g, "_")+" { stroke: hsl("+Math.round(h)+", 90%, 45%); }\n";
		newStyles+=style;
		districtNames.push(val.properties.name);
		counter++;
	});
	$("<style type='text/css'>"+newStyles+"</style>").appendTo("head");
	
	$.each(districtNames, function( key, val ) {
		eval("P1floating_"+val.replace(/ /g, "_")+" = d3.line().x(function(d) { return xScale(d.timestamp); }).y(function(d) { return yScale(d.P1floating_"+val.replace(/ /g, "_")+"); }).defined(function(d) {return d; });");
			//.defined(function(d) {return !isNaN(d.P1floating_...); });
	});
	
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
  // DEBW013pm10 aka Gnesener Straße
  var data_file = "../data/chronological_data_lubw.tsv";
  d3.tsv(data_file, function(error, data2) {
    if (error) throw error;
	log("LUBW data ("+data_file+") loaded");
  
    data2.forEach(function(d2) {
      d2.timestamp = parseDate(d2.timestamp);
      d2.DEBW013pm10 = +((d2.DEBW013pm10==="")?NaN:d2.DEBW013pm10);
      d2.DEBW118pm10 = +((d2.DEBW118pm10==="")?NaN:d2.DEBW118pm10);
    });
    
    graph.append("path")
        .datum(data2)
        .attr("id", "line_DEBW013pm10")
        .attr("class", "P1 graph DEBW013pm10")
        .attr("d", line_DEBW013pm10)
		.attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".DEBW013pm10").addClass("hover");
            $(".DEBW013pm10").removeClass("fadeout");
            $("#DEBW013pm10_text").addClass("texthover");
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" PM10 24h-Mittel des LUBW-Sensors Bad Cannstatt"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function(d) {		
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
      .on("mousemove", function(d) {		
            $(".P1").addClass("fadeout");
            $(".DEBW118pm10").addClass("hover");
            $(".DEBW118pm10").removeClass("fadeout");
            $("#DEBW118pm10_text").addClass("texthover");
            div	.html(hoverTimeFormat(xScale.invert(d3.mouse(this)[0]))+" PM10 24h-Mittel des LUBW-Sensors Neckartor"+": "+(Math.round(yScale.invert(d3.mouse(this)[1])))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");	
            })					
        .on("mouseout", function(d) {		
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
var district_data_file = "../data/chronological_districts_v2_simple"+((week)?"_week":"")+".tsv";
function append_data(){
	d3.tsv(district_data_file, function(error, data) {
	if (error) throw error;
	log("District data ("+district_data_file+") loaded");

	var P1max = 0;
	data.forEach(function(d) {
		d.timestamp = parseDate(d.timestamp);
		$.each(districtNames, function( key, val ) {
			d["P1floating_"+val.replace(/ /g, "_")] = + d["P1floating_"+val.replace(/ /g, "_")];
			if(P1max < Math.max(d["P1floating_"+val.replace(/ /g, "_")])) P1max = Math.max(d["P1floating_"+val.replace(/ /g, "_")]);
		});
		//todo: Add gaps!
		//d.P1floating_... = +((d.P1floating_...==="")?NaN:d.P1floating_...);
		//d2.DEBW013pm10 = +((d2.DEBW013pm10==="")?NaN:d2.DEBW013pm10);
	});
	citizen_science_data = data;
	init_map();
	$("#timestamp").html(versionTimeFormat(d3.max(data, function(d) { return d.timestamp; })));
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
      .on("mousemove", function(d) {data2map(mousemove(d3.mouse(this)));});

/**
 * Zoom
 */
new_xScale = xScale;
graph.call(d3.zoom().on("zoom", zoom));
function zoom(){
	new_xScale = d3.event.transform.rescaleX(xScale);
	graph.select('.x.axis').call(xAxis.scale(new_xScale));
	$(".graph").attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
	graph.select("#overlay_rect").attr("transform", 'translate('+d3.event.transform.x+',0) scale('+d3.event.transform.k+',1)');
}
graph.on('mousemove', function () {
   coordinates = d3.mouse(this);
	graph.select("#overline")
		.attr("x1",coordinates[0])
		.attr("x2",coordinates[0]);
});
/**
 * update map
 */
function data2map(d){
	if(d){
		counter = 0;
		$.each(districtNames, function( key, val ) {
			districts[counter].set("P1floating",d["P1floating_"+val.replace(/ /g, "_")]);
			counter++;
		});
		$("#mapTimeInfo").html("PM10: 24h-Mittel am "+hoverTimeFormat(d.timestamp));
	}
	var select_color_mode = document.getElementById('color-mode');
	var function_name = "styleFunction";
	function_name = function_name+select_color_mode.value+"PM10floating";
	vectorDistricts.setStyle(eval(function_name));
	map.render();
}
/**
 * init graphs
 */
$.each(districtNames, function( key, val ) {
  graph.append("path")
      .datum(data)
      .attr("id", "P1floating_"+val.replace(/ /g, "_"))
      .attr("class", "P1 graph "+val.replace(/ /g, "_"))
      .attr("d", eval("P1floating_"+val.replace(/ /g, "_")))
	  .attr( 'vector-effect' , 'non-scaling-stroke' )
      .on("mousemove", function(d) {		
			highlight_district_on_map(val.replace(/ /g, "_"));
            $(".P1").addClass("fadeout");
            $("."+val.replace(/ /g, "_")).addClass("hover");
            $("."+val.replace(/ /g, "_")).removeClass("fadeout");
            $("#"+val.replace(/ /g, "_")+"_text").addClass("texthover");
			data2map(mousemove(d3.mouse(this)));
            div	.html(((Math.round(mousemove(d3.mouse(this))["P1floating_"+val.replace(/ /g, "_")]))==-1)?hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+": keine Daten für "+val.replace(/Stuttgart /, ""):hoverTimeFormat(mousemove(d3.mouse(this)).timestamp)+" PM10 24h-Mittel in "+val.replace(/Stuttgart /, "")+": "+(Math.round(mousemove(d3.mouse(this))["P1floating_"+val.replace(/ /g, "_")]))+" µg/m³")	
                .style("opacity", 0.9)	
                .style("left", (d3.event.pageX) + "px")		
                .style("top", (d3.event.pageY - 28) + "px");
            })					
        .on("mouseout", function(d) {		
			highlight_district_on_map("");
            $(".P1").removeClass("fadeout");
            $("."+val.replace(/ /g, "_")).removeClass("hover");
            $("#"+val.replace(/ /g, "_")+"_text").removeClass("texthover");
            div.style("opacity", 0);	
			data2map(mousemove(d3.mouse(this)));
        });
});
/**
 *
 */
function highlight_district_on_map(district_name){
	counter = 0;
	$.each(districtNames, function( key, val ) {
		districts[counter].set("fadeout",(district_name=="")?0:(district_name==val.replace(/ /g, "_"))?0:1);
		counter++;
	});
	console.log(district_name);
}

// Infos
var infox = 10;
var infoy = 0;
var step = 12;

counter = 0;
$.each(districtNames, function( key, val ) {
	//districts[counter].set("fadeout",(district_name=="")?0:(district_name==val.replace(/ /g, "_"))?0:1);
	graph2.append("line")
	  .attr("class", "P1 legend_line "+val.replace(/ /g, "_"))
	  .attr("x1", infox)
	  .attr("y1", infoy+counter*step)
	  .attr("x2", infox+20)
	  .attr("y2", infoy+counter*step)
		.on("mouseover", function(d) {		
			  highlight_district_on_map(val.replace(/ /g, "_"));
			  $(".P1").addClass("fadeout");
			  $("."+val.replace(/ /g, "_")).addClass("hover");
			  $("."+val.replace(/ /g, "_")).removeClass("fadeout");
			  $("#"+val.replace(/ /g, "_")+"_text").addClass("texthover");
			  data2map(false);
			  })					
		  .on("mouseout", function(d) {		
			  highlight_district_on_map("");
			  $(".P1").removeClass("fadeout");
			  $("."+val.replace(/ /g, "_")).removeClass("hover");
			  $("#"+val.replace(/ /g, "_")+"_text").removeClass("texthover");
			  data2map(false);
		  });
	  
	graph2.append("text")
		.text(val.replace(/Stuttgart /g, ""))
		.attr("class", "legend_text")
		.attr("id", val.replace(/ /g, "_")+"_text")
		.attr("x",infox+30)
		.attr("y",infoy+3+counter*step)
		  .on("mouseover", function(d) {
			  highlight_district_on_map(val.replace(/ /g, "_"));
			  $(".P1").addClass("fadeout");
			  $("."+val.replace(/ /g, "_")).addClass("hover");
			  $("#"+val.replace(/ /g, "_")+"_text").addClass("texthover");
			  data2map(false);
			  })					
		  .on("mouseout", function(d) {		
			  highlight_district_on_map("");
			  $(".P1").removeClass("fadeout");
			  $("."+val.replace(/ /g, "_")).removeClass("hover");
			  $("#"+val.replace(/ /g, "_")+"_text").removeClass("texthover");
			  data2map(false);
		  });
	counter++;
});

  graph2.append("line")
    .attr("class", "P1 legend_line DEBW118pm10")
    .attr("x1", infox)
    .attr("y1", infoy+counter*step)
    .attr("x2", infox+20)
    .attr("y2", infoy+counter*step)
      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
            $(".DEBW118pm10").addClass("hover");
            $(".DEBW118pm10").removeClass("fadeout");
            $("#DEBW118pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".DEBW118pm10").removeClass("hover");
            $("#DEBW118pm10_text").removeClass("texthover");
        });
    
  graph2.append("text")
      .text("LUBW Neckartor")
      .attr("class", "legend_text")
	  .attr("id", "DEBW118pm10_text")
      .attr("x",infox+30)
      .attr("y",infoy+3+counter*step)
			      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
			$(".DEBW118pm10").addClass("hover");
            $("#DEBW118pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".DEBW118pm10").removeClass("hover");
            $("#DEBW118pm10_text").removeClass("texthover");
        });

  graph2.append("line")
    .attr("class", "P1 legend_line DEBW013pm10")
    .attr("x1", infox)
    .attr("y1", infoy+(counter+1)*step)
    .attr("x2", infox+20)
    .attr("y2", infoy+(counter+1)*step)
      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
            $(".DEBW013pm10").addClass("hover");
            $(".DEBW013pm10").removeClass("fadeout");
            $("#DEBW013pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".DEBW013pm10").removeClass("hover");
            $("#DEBW013pm10_text").removeClass("texthover");
        });
    
  graph2.append("text")
      .text("LUBW Bad Cannstatt")
      .attr("class", "legend_text")
	  .attr("id", "DEBW013pm10_text")
      .attr("x",infox+30)
      .attr("y",infoy+3+(counter+1)*step)
			      .on("mouseover", function(d) {		
            $(".P1").addClass("fadeout");
			$(".DEBW013pm10").addClass("hover");
            $("#DEBW013pm10_text").addClass("texthover");
            })					
        .on("mouseout", function(d) {		
            $(".P1").removeClass("fadeout");
            $(".DEBW013pm10").removeClass("hover");
            $("#DEBW013pm10_text").removeClass("texthover");
        });

		
// remove districts without values
$.each(districtNames, function( key, val ) {
	if(d3.max(data, function(d) { return d["P1floating_"+val.replace(/ /g, "_")]; }) <= 0){
		$("#"+val.replace(/ /g, "_")+"_text").html($("#"+val.replace(/ /g, "_")+"_text").html()+" (keine Werte)");
		$("."+val.replace(/ /g, "_")).addClass("hiddenItem");
	}
});

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
	$.each(districtNames, function( key, val ) {
		graph.select("#P1floating_"+val.replace(/ /g, "_"))
			.attr("d", eval("P1floating_"+val.replace(/ /g, "_")));  
	});

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
      
  }



d3.select(window).on('resize', resize);

/**
 * set map to most recent data
 * very ugly way...
 */
function init_map(){
    if($.isArray(districts) && $.isFunction(citizen_science_data.forEach)){
        citizen_science_data.forEach(function(d) {
            $("#mapTimeInfo").html("PM10: 24h-Mittel am "+hoverTimeFormat(d.timestamp));
			counter = 0;
			$.each(districtNames, function( key, val ) {
				districts[counter].set("P1floating",d["P1floating_"+val.replace(/ /g, "_")]);
				counter++;
			});
        });
        map.render();
        log("Map initialized with most recent data");
    }
}
