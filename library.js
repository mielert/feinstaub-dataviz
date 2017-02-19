var isMobile = false; //initiate as false
// device detection
if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)  || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;
var controlVisible = false;
var infoVisible = false;

$( document ).ready(function() {
	$("#controlBar2Button").click(function(){
		if(controlVisible){
			controlVisible = false;
			if(isMobile){
				$("#controlBar2Button").css({left: -38}).show();
				$("#infoBar2Button").css({left: -12}).show();
			}
			else{
				$("#controlBar2Button").css({left: -38}).show();
				$("#infoBar2Button").css({left: -12}).show();
			}
			$("#controlBar2Button").addClass("bgcolor");
			$("#controlBar2Button").removeClass("bgcolor_white");
			$("#controlBar2").hide();
			//$("#chart").css({left: 0, width: "100%"});
			resize();
			resize_Bar2();
		}
		else{
			controlVisible = true;
			infoVisible = false;
			if(isMobile){
				$("#controlBar2Button").css({left: $("#controlBar2").width()-38}).show();
				$("#infoBar2Button").css({left: $("#controlBar2").width()-12}).show();
			}
			else{
				$("#controlBar2Button").css({left: $("#controlBar2").width()-38}).show();
				$("#infoBar2Button").css({left: $("#controlBar2").width()-12}).show();
			}
			$("#controlBar2Button").removeClass("bgcolor");
			$("#controlBar2Button").addClass("bgcolor_white");
			$("#infoBar2Button").removeClass("bgcolor_white");
			$("#infoBar2Button").addClass("bgcolor");
			$("#controlBar2").show();
			$("#infoBar2").hide();
			resize();
			resize_Bar2();
		}
	});
	$("#infoBar2Button").click(function(){
		if(infoVisible){
			infoVisible = false;
			if(isMobile){
				$("#controlBar2Button").css({left: -38}).show();
				$("#infoBar2Button").css({left: -12}).show();
			}
			else{
				$("#controlBar2Button").css({left: -38}).show();
				$("#infoBar2Button").css({left: -12}).show();
			}
			$("#infoBar2Button").addClass("bgcolor");
			$("#infoBar2Button").removeClass("bgcolor_white");
			$("#infoBar2").hide();
			resize();
			resize_Bar2();
		}
		else{
			infoVisible = true;
			controlVisible = false;
			if(isMobile){
				$("#infoBar2Button").css({left: $("#infoBar2").width()-12}).show();
				$("#controlBar2Button").css({left: $("#infoBar2").width()-38}).show();
			}
			else{
				$("#infoBar2Button").css({left: $("#infoBar2").width()-12}).show();
				$("#controlBar2Button").css({left: $("#infoBar2").width()-38}).show();
			}
			$("#infoBar2Button").removeClass("bgcolor");
			$("#infoBar2Button").addClass("bgcolor_white");
			$("#controlBar2Button").removeClass("bgcolor_white");
			$("#controlBar2Button").addClass("bgcolor");
			$("#infoBar2").show();
			$("#controlBar2").hide();
			//$("#chart").css({left: 400, width: window.innerWidth-400});
			resize();
			resize_Bar2();
		}
	});
});
$( window ).resize(function() {
	resize_Bar2();
});
function resize_Bar2(){
	var new_info_height = window.innerHeight - $("#infoBar2 .Bar2Header").height()-20;
	var new_control_height = window.innerHeight - $("#controlBar2 .Bar2Header").height()-20;
	//alert(window.innerHeight);
	//alert($("#controlBar2Header").height());
	//alert(new_height);
	if(new_info_height > 100)
		$("#infoBar2 .Bar2Footer").height(new_info_height);
	if(new_control_height > 100)
		$("#controlBar2 .Bar2Footer").height(new_control_height);
}
/**
 * @param feature Object
 * @param attribute String feature to map "P1", "P2", "P1floating", "P2floating"
 * @param style String "AQI" "GreenRedPink"
 * @param deadSensorColor String rgb value "0,0,0"
 * @param missingValueColor String rgb value "255,255,255"
 */
function styleFunctionGlobal(feature,attribute,style,deadSensorColor = "0,0,0",missingValueColor = "255,255,255"){
	var color;
	if(feature.getGeometry().getType() == "Point"){
		if(attribute=="P1"||attribute=="P1floating"){
			if(style=="AQI") color=colorMappingAQIPM10(feature.get(attribute),deadSensorColor);
			else if(style=="LuQx") color=colorMappingLuQxPM10(feature.get(attribute),deadSensorColor);
			else color=colorMappingGreenRedPink(feature.get(attribute),deadSensorColor);
		}
		else if(attribute=="P2"||attribute=="P2floating"){
			if(style=="AQI") color=colorMappingAQIPM25(feature.get(attribute),deadSensorColor);
			else if(style=="LuQx") color=colorMappingLuQxPM10(feature.get(attribute),deadSensorColor);
			else color=colorMappingGreenRedPink(feature.get(attribute),deadSensorColor);
		}
		return 	new ol.style.Style({
					image: 	new ol.style.Circle({
							radius: (isMobile?20:8),
							fill: new ol.style.Fill({
									color: 'rgba(255,255,255,0.3)'
							}),
							stroke: new ol.style.Stroke({color: 'rgba('+color+',1)', width: (isMobile?6:3)})
					})
				});
	}
	if(feature.getGeometry().getType() == "Polygon"){
		if(attribute=="P1"||attribute=="P1floating"){
			if(style=="AQI") color=colorMappingAQIPM10(feature.get(attribute),missingValueColor);
			else if(style=="LuQx") color=colorMappingLuQxPM10(feature.get(attribute),missingValueColor);
			else color=colorMappingGreenRedPink(feature.get(attribute),missingValueColor);
		}
		else if(attribute=="P2"||attribute=="P2floating"){
			if(style=="AQI") color=colorMappingAQIPM25(feature.get(attribute),missingValueColor);
			else if(style=="LuQx") color=colorMappingLuQxPM10(feature.get(attribute),missingValueColor);
			else color=colorMappingGreenRedPink(feature.get(attribute),missingValueColor);
		}
		else if(attribute=="Num_Sensors"){
			color=colorMappingStepsGreenRed(feature.get(attribute),missingValueColor);
		}
		return 	new ol.style.Style({
					stroke: new ol.style.Stroke({
							color: 'rgba(0, 0, 0, 1)',
							width: ((feature.get("fadeout")==1)?0.5:1.2)
					}),
					fill: new ol.style.Fill({
							color: 'rgba('+color+','+((feature.get("fadeout")==1)?0.2:1)+')'
					})
				});
	}
}
/**
 * Dust PM10
 * https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI
 * @param {float} Value to map
 * @param {string} Color ("123,55,212") if value is undefined or 0
 * @returns {string} Color ("123,55,212")
 */
var colorMappingAQIPM10 = function(value,undefinedColor) {
	// https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI
	var color;
	if(value === undefined || value <= 0 || isNaN(value)){
		if(typeof undefinedColor === 'string' || undefinedColor instanceof String)
			color = undefinedColor;
		else
			color = undefinedColor+","+undefinedColor+","+undefinedColor;
	}
	else{
		if      (value<=54)  { color = "0,228,0"; }
		else if (value<=154) { color = "255,255,0"; }
		else if (value<=254) { color = "255,126,0"; }
		else if (value<=354) { color = "255,0,0"; }
		else if (value<=424) { color = "143,63,151"; }
		else                 { color = "126,0,35"; }

	}
	return color;
};
/**
 * Dust P2.5
 * https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI
 * @param {float} Value to map
 * @param {string} Color ("123,55,212") if value is undefined or 0
 * @returns {string} Color ("123,55,212")
 */
var colorMappingAQIPM25 = function(value,undefinedColor) {
	var color;
	if(value === undefined || value <= 0 || isNaN(value)){
		if(typeof undefinedColor === 'string' || undefinedColor instanceof String)
			color = undefinedColor;
		else
			color = undefinedColor+","+undefinedColor+","+undefinedColor;
	}
	else{
		if      (value<=12.0) { color = "0,228,0"; }
		else if (value<=35.4) { color = "255,255,0"; }
		else if (value<=55.4) { color = "255,126,0"; }
		else if (value<=150.4){ color = "255,0,0"; }
		else if (value<=250.4){ color = "143,63,151"; }
		else                  { color = "126,0,35"; }

	}
	return color;
};
/**
 * Dust PM10
 * http://www4.lubw.baden-wuerttemberg.de/servlet/is/20152/
 * @param {float} Value to map
 * @param {string} Color ("123,55,212") if value is undefined or 0
 * @returns {string} Color ("123,55,212")
 */
var colorMappingLuQxPM10 = function(value,undefinedColor) {
	// http://www4.lubw.baden-wuerttemberg.de/servlet/is/20152/
	var color;
	if(value === undefined || value <= 0 || isNaN(value)){
		if(typeof undefinedColor === 'string' || undefinedColor instanceof String)
			color = undefinedColor;
		else
			color = undefinedColor+","+undefinedColor+","+undefinedColor;
	}
	else{
		if      (value<=10)  { color = "52,153,255"; }
		else if (value<=20) { color = "103,204,255"; }
		else if (value<=35) { color = "153,255,255"; }
		else if (value<=50) { color = "255,255,153"; }
		else if (value<=100) { color = "255,153,52"; }
		else                 { color = "255,52,52"; }

	}
	return color;
};
/**
 * @param {float} Value to map
 * @param {string} Color ("123,55,212") if value is undefined or 0
 * @returns {string} Color ("123,55,212")
 */
var colorMappingGreenRedPink = function(value,undefinedColor) {
	//alert("huhu");
	var color;
	if(value === undefined || value <= 0 || isNaN(value)){
		if(typeof undefinedColor === 'string' || undefinedColor instanceof String)
			color = undefinedColor;
		else
			color = undefinedColor+","+undefinedColor+","+undefinedColor;
	}
	else{
		//alert("not undefined");
		value = parseFloat(value);
		if(value<=50){
			color = Math.round(value/50*255)+","+(150-Math.round(value/50*150))+",0";
			//alert(color);
		}
		else {
			if(value<200){
				color = "255,0,"+Math.round((value-50)/150*255);
			}
			else{
				color = "255,0,255";
			}
		}
	}
	return color;
};

/**
 * @param {float} Value to map
 * @param {string} Color ("123,55,212") if value is undefined or 0
 * @returns {string} Color ("123,55,212")
 */
var colorMappingStepsGreenRed = function(value,undefinedColor) {
	var color;
	if(value === undefined || value <= 0 || isNaN(value)){
		if(typeof undefinedColor === 'string' || undefinedColor instanceof String)
			color = undefinedColor;
		else
			color = undefinedColor+","+undefinedColor+","+undefinedColor;
	}
	else{
		if      (value<=0)  { color = "255,0,0"; }
		else if (value<=1)  { color = "235,20,0"; }
		else if (value<=2)  { color = "195,40,0"; }
		else if (value<=4)  { color = "155,80,0"; }
		else if (value<=6)  { color = "115,120,0"; }
		else if (value<=8)  { color = "75,160,0"; }
		else if (value<=10) { color = "35,200,0"; }
		else                { color = "0,240,0"; }

	}
	return color;
};

	
var styleFunctionAQIPM10 = function(feature) {
	return styleFunctionGlobal(feature,"P1","AQI","0,0,0","255,255,255");
};
var styleFunctionAQIPM10floating = function(feature) {
	return styleFunctionGlobal(feature,"P1floating","AQI","0,0,0","255,255,255");
};
var styleFunctionAQIPM25 = function(feature) {
	return styleFunctionGlobal(feature,"P2","AQI","0,0,0","255,255,255");
};
var styleFunctionAQIPM25floating = function(feature) {
	return styleFunctionGlobal(feature,"P2floating","AQI","0,0,0","255,255,255");
};
var styleFunctionGreenRedPinkPM10 = function(feature) {
	return styleFunctionGlobal(feature,"P1","GreenRedPink","0,0,0","255,255,255");
};
var styleFunctionGreenRedPinkPM10floating = function(feature) {
	return styleFunctionGlobal(feature,"P1floating","GreenRedPink","0,0,0","255,255,255");
};
var styleFunctionGreenRedPinkPM25 = function(feature) {
	return styleFunctionGlobal(feature,"P2","GreenRedPink","0,0,0","255,255,255");
};
var styleFunctionGreenRedPinkPM25floating = function(feature) {
	return styleFunctionGlobal(feature,"P2floating","GreenRedPink","0,0,0","255,255,255");
};
var styleFunctionLuQxPM10 = function(feature) {
	return styleFunctionGlobal(feature,"P1","LuQx","0,0,0","255,255,255");
};
var styleFunctionLuQxPM10floating = function(feature) {
	return styleFunctionGlobal(feature,"P1floating","LuQx","0,0,0","255,255,255");
};
var styleFunctionLuQxPM25 = function(feature) {
	return styleFunctionGlobal(feature,"P2","LuQx","0,0,0","255,255,255");
};
var styleFunctionLuQxPM25floating = function(feature) {
	return styleFunctionGlobal(feature,"P2floating","LuQx","0,0,0","255,255,255");
};
var styleFunctionSensorCounter = function(feature) {
	return styleFunctionGlobal(feature,"Num_Sensors","StepsGreenRed","0,0,0","255,0,0");
}

// read get variables
var $_GET = {};
document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
	function decode(s) {
		return decodeURIComponent(s.split("+").join(" "));
	}
	$_GET[decode(arguments[1])] = decode(arguments[2]);
});
