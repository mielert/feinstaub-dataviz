/**
 * Mobile Detection
 * @copyright: cc by sa 3.0 sweets-BlingBling / stackoverflow
 * @see http://stackoverflow.com/questions/3514784/what-is-the-best-way-to-detect-a-mobile-device-in-jquery
 */
var isMobile = false; //initiate as false
// device detection
if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)  || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;
var startTime = Date.now();
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
 * @param string message
 */
function log(message){
	var time = Date.now() - startTime;
	console.log(time+": "+message);
	$("#status_bar").html("<p>"+message+" after "+(time/1000)+" seconds</p>"+$("#status_bar").html());
}
function close_log(){
	$("#status_bar").hide();
}
/**
 * @param feature {object}
 * @param attribute {string} feature to map "P1", "P2", "P1floating", "P2floating"
 * @param lut {object} Color lookup table
 * @param deadSensorColor {string} rgb value "0,0,0"
 * @param missingValueColor {string} rgb value "255,255,255"
 */
function styleFunctionGlobal(feature,attribute,lut,deadSensorColor = "0,0,0",missingValueColor = "255,255,255"){
	var color;
	if(feature.getGeometry().getType() == "Point"){
		color=colorMapping(lut,feature.get(attribute),deadSensorColor);
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
		color=colorMapping(lut,feature.get(attribute),missingValueColor);
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
 * Color lookup table
 * Dust PM10
 * https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI
 */
var colorLookupTableAQIPM10 = {"type":"step","values":[[54,[0,228,0]],[154,[255,255,0]],[254,[255,126,0]],[354,[255,0,0]],[424,[143,63,151]],[false,[126,0,35]]]};
/**
 * Color lookup table
 * Dust PM2.5
 * https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI
 */
var colorLookupTableAQIPM25 = {"type":"step","values":[[12.0,[0,228,0]],[35.4,[255,255,0]],[55.4,[255,126,0]],[150.4,[255,0,0]],[250.0,[143,63,151]],[false,[126,0,35]]]};
/**
 * Color lookup table
 * Dust PM10
 * http://www4.lubw.baden-wuerttemberg.de/servlet/is/20152/
 */
var colorLookupTableLuQxPM10 = {"type":"step","values":[[10,[52,153,255]],[20,[103,204,255]],[35,[153,255,255]],[50,[255,255,153]],[100,[255,153,52]],[false,[255,52,52]]]};
/**
 * Color lookup table
 * Gradient Green Red Pink
 */
var colorLookupTableGreenRedPink = {"type":"gradient","values":[[0,[0,150,0]],[50,[255,0,0]],[200,[255,0,255]],[false,[255,0,255]]]};
/**
 * Color lookup table
 * Gradient Red Green
 */
var colorLookupTableRedGreen = {"type":"gradient","values":[[0,[255,0,0]],[10,[0,240,0]],[false,[0,240,0]]]};

/**
 * Calculate color from color lookup table
 * @param {object} Color lookup table
 * @param {float} Value to map
 * @param {string} Color ("123,55,212") if value is undefined or 0
 * @returns {string} Color ("123,55,212")
 */
var colorMapping = function(lut,value,undefinedColor) {
	var color;
	if(value === undefined || value < 0 || isNaN(value)){
		if(typeof undefinedColor === 'string' || undefinedColor instanceof String)
			color = undefinedColor;
		else
			color = undefinedColor+","+undefinedColor+","+undefinedColor;
	}
	else{
		value = parseFloat(value);
		// step
		if(lut.type=="step"){
			color = lut.values[lut.values.length-1][1][0]+","+lut.values[lut.values.length-1][1][1]+","+lut.values[lut.values.length-1][1][2];
			for(i=0;i<lut.values.length-1;i++){
				if(value<=lut.values[i][0]) {
					color = lut.values[i][1][0]+","+lut.values[i][1][1]+","+lut.values[i][1][2];
					break;
				}
			}
		}
		// gradient
		else if(lut.type=="gradient"){
			color = lut.values[lut.values.length-1][1][0]+","+lut.values[lut.values.length-1][1][1]+","+lut.values[lut.values.length-1][1][2];
			for(i=0;i<lut.values.length-1;i++){
				if(value<=lut.values[i+1][0]) {
					var r = Math.round((lut.values[i+1][1][0]-lut.values[i][1][0])/(lut.values[i+1][0]-lut.values[i][0])*value+lut.values[i][1][0]);
					var g = Math.round((lut.values[i+1][1][1]-lut.values[i][1][1])/(lut.values[i+1][0]-lut.values[i][0])*value+lut.values[i][1][1]);
					var b = Math.round((lut.values[i+1][1][2]-lut.values[i][1][2])/(lut.values[i+1][0]-lut.values[i][0])*value+lut.values[i][1][2]);
					color = r+","+g+","+b;
					break;
				}
			}
		}
	}
	return color;
};
/**
 * Styling for OpenLayers
 * @param {object} Feature
 * @returns {object} Result from styleFunctionGlobal (applied color lookup table)
 */
var styleFuntionOpenLayers = function(feature) {
	var lut;
	var attribute = select_source.value;
	attribute = attribute.replace("PM25", "P2");
	attribute = attribute.replace("PM10", "P1");
	if(feature.getGeometry().getType() == "Point"){
		attribute = attribute.replace("floating", "");
	}
	if(select_color_mode.value == "AQI") {
		  if(attribute == "P1")
				lut = colorLookupTableAQIPM10;
		  else
				lut = colorLookupTableAQIPM25;
	}
	else if(select_color_mode.value == "GreenRedPink"){
		  lut = colorLookupTableGreenRedPink;
	}
	else if(select_color_mode.value == "LuQx"){
		  lut = colorLookupTableLuQxPM10;
	}
	return styleFunctionGlobal(feature,attribute,lut,"0,0,0","255,255,255");
}
/**
 * @param {String} div Name of the div to use for scale
 * @param {String} orientation "vertical" or "horizontal"
 * @param {Int} width Width of the scale
 * @param {Int} height Height of the scale
 * @param {Object} lut Color lookup table to use
 */
function d3ScaleComplex(div,orientation,width,height,lut){
	// clear legend
	$(div).html("");
	
	var scale = d3.select(div).append("svg").attr("width", width).attr("height", height);

	var minValue = 0;
	var maxValue = 0;
	if(lut.type === "step"){
		minValue = 0;
		maxValue = lut.values[lut.values.length-2][0] + (lut.values[lut.values.length-2][0]-lut.values[lut.values.length-3][0]);
	}
	else{
		minValue = lut.values[0][0];
		maxValue = lut.values[lut.values.length-2][0]+50;
	}
	var dimension = (maxValue-minValue)/height;
	var i = 0;
	if(orientation==="vertical"){
		//colored bar
		for(i=0;i<height;i++){
			var color = colorMapping(lut,i*dimension,"255,255,255");
			//console.log("rect "+i+"-"+(i+1)+": "+i*dimension+": "+color);
			scale.append("rect")
				.attr("fill", "rgb("+color+")")
				.attr("width", width/3)
				.attr("height", 1)
				.attr("transform", "translate(0,"+(height-i)+")");
		}
		//labels
		for(i=0;i<lut.values.length;i++){
			if(lut.values[i][0]){
				//text
				scale.append("text")
					.text(lut.values[i][0])
					.attr("class", "legend_text")
					.attr("x",width/3+3)
					.attr("y",(height-lut.values[i][0]/dimension)+4);
				//little black dot
				scale.append("line")
					.attr("style", "stroke: #000;")
					.attr("x1", width/3)
					.attr("y1", (height-lut.values[i][0]/dimension))
					.attr("x2", width/3+2)
					.attr("y2", (height-lut.values[i][0]/dimension));
			}
		}
	}
	else{
		//ToDo
	}
}
// read get variables
var $_GET = {};
document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
	function decode(s) {
		return decodeURIComponent(s.split("+").join(" "));
	}
	$_GET[decode(arguments[1])] = decode(arguments[2]);
});
