<?php
//if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit;

include_once("library.php");

// read geodata
include_once($data_root."stuttgart.php");
$coordinates_stuttgart = get_city_geodata();

/**
 *
 */
function cleanup_string($string){
	$string = str_replace(array(" ","ä","ö","ü","ß"),array("_","ae","oe","ue","sz"),$string);
	return $string;
}

$sql = "SELECT `timestamp` FROM `regions_mean` ORDER BY `regions_mean`.`timestamp` DESC LIMIT 1";
$recent_timestamp = db_select($sql);
$recent_timestamp = $recent_timestamp[0]->timestamp;

$sql = "SELECT
			`regions`.`id`,
			`regions`.`name`,
			`regions`.`id`,
			`regions_mean`.`P1h`,
			`regions_mean`.`P2h`,
			`regions_mean`.`P1d`,
			`regions_mean`.`P2d`,
			(
				SELECT COUNT(sensor_id)
				FROM `sensors_hourly_mean`
				LEFT JOIN `sensors` ON `sensors`.`id` = `sensors_hourly_mean`.`sensor_id`
				WHERE `timestamp` = '$recent_timestamp'
				AND lon IN (SELECT lon FROM `x_coordinates_regions` WHERE `region_id` = `regions`.`id`)
				AND lat IN (SELECT lat FROM `x_coordinates_regions` WHERE `region_id` = `regions`.`id`)
				AND `sensors`.`type_id` = 1
			) AS Num_Sensors,
			(
				SELECT GROUP_CONCAT(`sensors`.`name`)
				FROM `sensors_hourly_mean`
				LEFT JOIN `sensors` ON `sensors`.`id` = `sensors_hourly_mean`.`sensor_id`		
				WHERE `timestamp` = '$recent_timestamp'
				AND lon IN (SELECT lon FROM `x_coordinates_regions` WHERE `region_id` = `regions`.`id`)
				AND lat IN (SELECT lat FROM `x_coordinates_regions` WHERE `region_id` = `regions`.`id`)
				AND `sensors`.`type_id` = 1
			) AS Sensor_IDs,
			-1 AS P1_Sensors,
			-1 AS P2_Sensors
		FROM `regions_mean`
		LEFT JOIN `regions` ON `regions`.`id` = `regions_mean`.`region_id`
		WHERE `regions_mean`.`timestamp`='$recent_timestamp'
		ORDER BY `regions_mean`.`region_id`";
/*
SELECT COUNT(sensor_id) FROM `sensors_hourly_mean` WHERE `timestamp` = '2017-01-24 07:00:00' AND lon IN (SELECT lon FROM `x_coordinates_regions` WHERE `district_id` = 1) AND lat IN (SELECT lat FROM `x_coordinates_regions` WHERE `district_id` = 1) ORDER BY `timestamp`  DESC
SELECT COUNT(sensor_id) FROM `sensors_hourly_mean` WHERE `timestamp` = '$recent_timestamp' AND lon IN (SELECT lon FROM `x_coordinates_regions` WHERE `district_id` = `districts`.`id`) AND lat IN (SELECT lat FROM `x_coordinates_regions` WHERE `district_id` = `districts`.`id`) ORDER BY `timestamp`  DESC
*/
$recent_data = db_select($sql);

//echo "<pre>recent_data for polygons_to_feature_collection:\n".print_r($recent_data,true)."</pre>";
polygons_to_feature_collection($coordinates_stuttgart,$recent_data);


$sql = "SELECT
			`sensors_hourly_mean`.`sensor_id`,
			`sensors_hourly_mean`.`P1` AS P1h,
			`sensors_hourly_mean`.`P2` AS P2h,
			`sensors_hourly_mean`.`P1d`,
			`sensors_hourly_mean`.`P2d`,
			`sensors_hourly_mean`.`lon`,
			`sensors_hourly_mean`.`lat`
		FROM `sensors_hourly_mean`
		LEFT JOIN `sensors` ON `sensors`.`id` = `sensors_hourly_mean`.`sensor_id`
		LEFT JOIN `x_coordinates_regions` ON (`x_coordinates_regions`.`lat` = `sensors_hourly_mean`.`lat` AND `x_coordinates_regions`.`lon` = `sensors_hourly_mean`.`lon`)
		WHERE `sensors_hourly_mean`.`timestamp`='$recent_timestamp'
		AND `x_coordinates_regions`.`region_id` IN (SELECT `id` FROM `regions` WHERE `parent_region_id` = 24)
		AND `sensors`.`type_id` = 1
		ORDER BY `x_coordinates_regions`.`region_id`";

$recent_sensor_data = db_select($sql);

sensors_to_feature_collection($recent_sensor_data);
/**
 *
 */
function polygons_to_feature_collection($coordinates_stuttgart,$most_recent_data){
	global $root,$data_root;
	//echo "polygons_to_feature_collection<br/>";
	$out = array();
	for($i=0;$i<count($coordinates_stuttgart);$i++){
		if(isset($coordinates_stuttgart[$i]["coordinates"])){
			//echo $coordinates_stuttgart[$i]["name"]."<br/>";
			//echo count($coordinates_stuttgart[$i]["coordinates"])."<br/>";
			//echo "coordinates ok<br/>";
			$data = false;
			for($j=0;$j<count($most_recent_data);$j++){
				if($coordinates_stuttgart[$i]["name"]==$most_recent_data[$j]->name){
					$data = true;
					//echo $most_recent_data[$j]->P1d."<br/>";
					array_push($out, '{
						  "type": "Feature",
						  "geometry": {
								"type": "Polygon",
								"coordinates": ['.(json_encode($coordinates_stuttgart[$i]["coordinates"])).']
							},
							"properties": {
								"name": "'.$coordinates_stuttgart[$i]["name"].'",
								"Num_Sensors": "'.$most_recent_data[$j]->Num_Sensors.'",
								"P1": "'.$most_recent_data[$j]->P1h.'",
								"P2": "'.$most_recent_data[$j]->P2h.'",
								"P1floating": "'.(isset($most_recent_data[$j]->P1d)?$most_recent_data[$j]->P1d:"").'",
								"P2floating": "'.(isset($most_recent_data[$j]->P2d)?$most_recent_data[$j]->P2d:"").'",
								"P1-Sensors": "'.$most_recent_data[$j]->P1_Sensors.'",
								"P2-Sensors": "'.$most_recent_data[$j]->P2_Sensors.'",
								"Sensor_IDs": "'.$most_recent_data[$j]->Sensor_IDs.'"
							}
						}
					');
					break;
				}
			}
			if(!$data){
				array_push($out, '{
					  "type": "Feature",
					  "geometry": {
							"type": "Polygon",
							"coordinates": ['.(json_encode($coordinates_stuttgart[$i]["coordinates"])).']
						},
						"properties": {
							"name": "'.$coordinates_stuttgart[$i]["name"].'",
							"Num_Sensors": "0"
						}
					}
				');
			}
		}
	}
	//array_pop($out);
	//echo "/* ".print_r($out,true)."*/";
	
	$out = '{
			"type": "FeatureCollection",
			"crs": {
			  "type": "name",
			  "properties": {"name": "EPSG:4326"}
			},
			"features": [
			'.join(",",$out).'
			]
			}';
	
	file_put_contents($data_root."stuttgart_districts_v2.json",$out);
}

/**
 *
 */
function sensors_to_feature_collection($recent_sensor_data){
	global $root,$data_root,$statistic;
	$out = array();
	$statistic = array("P1h"=>0,"P2h"=>0,"P1d"=>0,"P2d");
	foreach($recent_sensor_data as $dataset){
		array_push($out, '{
			  "type": "Feature",
			  "geometry": {
				"type": "Point",
				"coordinates": ['.$dataset->lon.', '.$dataset->lat.']
			  },
				"properties": {
					"name": "'.get_sensor_name_by_sensor_id($dataset->sensor_id).'",
					"P1": "'.$dataset->P1h.'",
					"P2": "'.$dataset->P2h.'",
					"P1floating": "'.$dataset->P1d.'",
					"P2floating": "'.$dataset->P2d.'"
				}
			}');
	}

	$out = '{
			"type": "FeatureCollection",
			"crs": {
			  "type": "name",
			  "properties": {"name": "EPSG:4326"}
			},
			"features": [
			'.join(",",$out).'
			]
			}';
			
	file_put_contents($data_root."stuttgart_sensors_v2.json",$out);
}

/**
 *
 */
function map_values_to_properties($values){
	//print_r($values);
	if(count($values) > 0){
		$result = array();
		foreach($values as $value){
			array_push($result,'"'.$value["value_type"].'": "'.$value["value"].'"');
		}
		return ",
".join(",
",$result);
	}
	else return "";
}

/**
 *
 */
function get_current_values_by_id($id,$lastdata){
	//echo $id;
	foreach($lastdata["data"] as $dataset){
		//echo " ".$dataset["sensor"]["id"];
		if($dataset["sensor"]["id"]==$id){
			return $dataset["sensordatavalues"];
		}
	}
}
echo "$recent_timestamp dumped $statistic";
?>
