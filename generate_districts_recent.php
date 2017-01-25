<pre><?php
include_once("library.php");

// read geodata
include_once($root."map/stuttgart_with_data_1.2.0.php");

/**
 *
 */
function cleanup_string($string){
	$string = str_replace(array(" ","ä","ö","ü","ß"),array("_","ae","oe","ue","sz"),$string);
	return $string;
}

$sql = "SELECT `timestamp` FROM `districts_mean` ORDER BY `districts_mean`.`timestamp` DESC LIMIT 1";
$recent_timestamp = debug_query($sql);
$recent_timestamp = $recent_timestamp[0]->timestamp;

$sql = "SELECT
			`districts`.`id`,
			`districts`.`name`,
			`districts`.`id`,
			`districts_mean`.`P1h`,
			`districts_mean`.`P2h`,
			`districts_mean`.`P1d`,
			`districts_mean`.`P2d`,
			(
				SELECT COUNT(sensor_id)
				FROM `sensors_hourly_mean`
				WHERE `timestamp` = '$recent_timestamp'
				AND lon IN (SELECT lon FROM `x_coordinates_districts` WHERE `district_id` = `districts`.`id`)
				AND lat IN (SELECT lat FROM `x_coordinates_districts` WHERE `district_id` = `districts`.`id`)
			) AS Num_Sensors,
			(
				SELECT GROUP_CONCAT(`sensors_hourly_mean`.`sensor_id`)
				FROM `sensors_hourly_mean`
				WHERE `timestamp` = '$recent_timestamp'
				AND lon IN (SELECT lon FROM `x_coordinates_districts` WHERE `district_id` = `districts`.`id`)
				AND lat IN (SELECT lat FROM `x_coordinates_districts` WHERE `district_id` = `districts`.`id`)
			) AS Sensor_IDs,
			-1 AS P1_Sensors,
			-1 AS P2_Sensors
		FROM `districts_mean`
		LEFT JOIN `districts` ON `districts`.`id` = `districts_mean`.`district_id`
		WHERE `districts_mean`.`timestamp`='$recent_timestamp'
		ORDER BY `districts_mean`.`district_id`";
/*
SELECT COUNT(sensor_id) FROM `sensors_hourly_mean` WHERE `timestamp` = '2017-01-24 07:00:00' AND lon IN (SELECT lon FROM `x_coordinates_districts` WHERE `district_id` = 1) AND lat IN (SELECT lat FROM `x_coordinates_districts` WHERE `district_id` = 1) ORDER BY `timestamp`  DESC
SELECT COUNT(sensor_id) FROM `sensors_hourly_mean` WHERE `timestamp` = '$recent_timestamp' AND lon IN (SELECT lon FROM `x_coordinates_districts` WHERE `district_id` = `districts`.`id`) AND lat IN (SELECT lat FROM `x_coordinates_districts` WHERE `district_id` = `districts`.`id`) ORDER BY `timestamp`  DESC
*/
$recent_data = debug_query($sql);

echo "<pre>".print_r($recent_data,true)."</pre>";
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
		LEFT JOIN `x_coordinates_districts` ON (`x_coordinates_districts`.`lat` = `sensors_hourly_mean`.`lat` AND `x_coordinates_districts`.`lon` = `sensors_hourly_mean`.`lon`)
		WHERE `sensors_hourly_mean`.`timestamp`='$recent_timestamp'
		AND `x_coordinates_districts`.`district_id` > 0
		ORDER BY `x_coordinates_districts`.`district_id`";

$recent_sensor_data = debug_query($sql);

sensors_to_feature_collection($recent_sensor_data);
/**
 *
 */
function polygons_to_feature_collection($coordinates_stuttgart,$most_recent_data){
	global $root,$data_root;
	echo "polygons_to_feature_collection<br/>";
	$out = array();
	for($i=0;$i<count($coordinates_stuttgart);$i++){
		if(isset($coordinates_stuttgart[$i]["coordinates"])){
			echo $coordinates_stuttgart[$i]["name"]."<br/>";
			echo count($coordinates_stuttgart[$i]["coordinates"])."<br/>";
			echo "coordinates ok<br/>";
			$data = false;
			for($j=0;$j<count($most_recent_data);$j++){
				if($coordinates_stuttgart[$i]["name"]==$most_recent_data[$j]->name){
					$data = true;
					echo $most_recent_data[$j]->P1d."<br/>";
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
	global $root,$data_root;
	$out = array();
	
	foreach($recent_sensor_data as $dataset){
		array_push($out, '{
			  "type": "Feature",
			  "geometry": {
				"type": "Point",
				"coordinates": ['.$dataset->lon.', '.$dataset->lat.']
			  },
				"properties": {
					"name": "'.$dataset->sensor_id.'",
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

?>
</pre>
