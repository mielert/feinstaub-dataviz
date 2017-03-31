/**
 * Some helpers to make d3-charts more easy
 */
/**
 * @param {Object} d3_object d3-object to add elements to
 * @param {String} text Text to display
 * @param {String} internal_name_base Text for classes & ids
 * @param {String} internal_element_group General class name
 * @param {Integer} x Coordinate
 * @param {Integer} y Coordinate
 * @param {Boolean} data_to_map Update map in hover
 * @param {Integer} line_width Length of legend line
 */
function legendAddElement(d3_object,text,internal_name_base,internal_element_group,x,y,data_to_map=false,line_width=20){

	d3_object.append("line")
		.attr("class", internal_element_group+" legend_line "+internal_name_base)
		.attr("x1", x)
		.attr("y1", y)
		.attr("x2", x+line_width)
		.attr("y2", y)
		  .on("mouseover", function() {		
				$("."+internal_element_group).addClass("fadeout");
				$("."+internal_name_base).addClass("hover");
				$("."+internal_name_base).removeClass("fadeout");
				$("#"+internal_name_base+"_text").addClass("texthover");
				if(data_to_map) {
					highlightRegionOnMap(internal_name_base);
					data2map(false);
				}
				})					
			.on("mouseout", function() {		
				$("."+internal_element_group).removeClass("fadeout");
				$("."+internal_name_base).removeClass("hover");
				$("#"+internal_name_base+"_text").removeClass("texthover");
				if(data_to_map) {
					highlightRegionOnMap("");
					data2map(false);
				}
			});
	  
	d3_object.append("text")
		.text(text)
		.attr("class", "legend_text")
		.attr("id", internal_name_base+"_text")
		.attr("x",x+line_width+10)
		.attr("y",y+3)
			.on("mouseover", function() {		
				$("."+internal_element_group).addClass("fadeout");
				$("."+internal_name_base).addClass("hover");
				$("#"+internal_name_base+"_text").addClass("texthover");
				if(data_to_map) {
					highlightRegionOnMap(internal_name_base);
					data2map(false);
				}				})					
			.on("mouseout", function() {		
				$("."+internal_element_group).removeClass("fadeout");
				$("."+internal_name_base).removeClass("hover");
				$("#"+internal_name_base+"_text").removeClass("texthover");
				if(data_to_map) {
					highlightRegionOnMap("");
					data2map(false);
				}
			});
}
/**
 *
 */
function highlightRegionOnMap(district_name){
	for(i=0;i<districtNames.length;i++){
		$("#mapdiv svg path:nth-of-type("+(i-(-1))+")").css("opacity", (district_name==="")?1:(district_name===districtNames[i].replace(/ /g, "_"))?1:0.15);
	}
}
