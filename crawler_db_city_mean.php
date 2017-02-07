<?php
include_once("library.php");

$city_id = 1;

// get start time
$starttime = false;
$sql = "SELECT MIN(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id";
$result = db_select($sql);
if($result[0]->timestamp == "") {
  echo "cities_mean for city_id = $city_id is empty";
  $sql = "SELECT MIN(`timestamp`) AS timestamp 
          FROM `sensor_data` 
          WHERE `lon` IN (SELECT `lon` 
                          FROM `districts`
                          LEFT JOIN `x_coordinates_districts` ON `x_coordinates_districts`.`district_id` = `districts`.`id`
                          WHERE `districts`.`city_id` = $city_id)
          AND `lat` IN (SELECT `lat` 
                          FROM `districts`
                          LEFT JOIN `x_coordinates_districts` ON `x_coordinates_districts`.`district_id` = `districts`.`id`
                          WHERE `districts`.`city_id` = $city_id)";
  $result = db_select($sql);
  $starttime = $result[0]->timestamp;
}
else{
  $sql = "SELECT MIN(`timestamp`) AS timestamp FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id AND `timestamp` > '".$result[0]->timestamp."')";
  $result = db_select($sql);
  $starttime = $result[0]->timestamp;
}
if(!$starttime){
  echo "no start time because of empty table districts_mean at city_id = $city_id";
  exit;
}
// get sensor data
$sql = "SELECT * 
          FROM `sensor_data` 
          WHERE `lon` IN (SELECT `lon` 
                          FROM `districts`
                          LEFT JOIN `x_coordinates_districts` ON `x_coordinates_districts`.`district_id` = `districts`.`id`
                          WHERE `districts`.`city_id` = $city_id)
          AND `lat` IN (SELECT `lat` 
                          FROM `districts`
                          LEFT JOIN `x_coordinates_districts` ON `x_coordinates_districts`.`district_id` = `districts`.`id`
                          WHERE `districts`.`city_id` = $city_id)
          AND `sensor_data`.`timestamp` >  '".substr($starttime,0,13).":00:00'
          AND `sensor_data`.`timestamp` <= DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 HOUR)";
$result = db_select($sql);

// calculate city data
$statistics = get_min_max_mid($result);
$result = db_select("SELECT DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 HOUR) AS timestamp");
$statistics["timestamp"] = $result[0]->timestamp;
echo "<pre>".print_r($statistics,true)."</pre>";
$sql = "INSERT INTO `cities_mean` (`id`, `city_id`, `timestamp`, `P1h`, `P2h`, `P1d`, `P2d`, `P1h_min`, `P1h_max`, `P2h_min`, `P2h_max`, `P1h_50_min`, `P1h_50_max`, `P2h_50_min`, `P2h_50_max`, `P1min_sensor_id`, `P1max_sensor_id`, `P2min_sensor_id`, `P2max_sensor_id`, `num_sensors`, `num_values`)
        VALUES (NULL, '$city_id', '".$statistics["timestamp"]."', '".$statistics["P1"]["mid"]."', '".$statistics["P2"]["mid"]."', 0, 0, '".$statistics["P1"]["min"]."', '".$statistics["P1"]["max"]."', '".$statistics["P2"]["min"]."', '".$statistics["P2"]["max"]."', '".$statistics["P1"]["max_main"]."', '".$statistics["P1"]["min_main"]."', '".$statistics["P2"]["max_main"]."', '".$statistics["P2"]["min_main"]."', '".$statistics["P1"]["min_sensor_id"]."', '".$statistics["P1"]["max_sensor_id"]."', '".$statistics["P2"]["min_sensor_id"]."', '".$statistics["P2"]["max_sensor_id"]."', ".$statistics["P2"]["num_sensors"].", ".$statistics["P2"]["num_values"].")";
echo $sql;
// add 24h floating
$sql = "SELECT MID(P1h) AS P1d, MID(P2h) AS P2d
        FROM `cities_mean` 
        WHERE `city_id` = $city_id)
        AND `timestamp` >  '".substr($starttime,0,13).":00:00'
        AND `timestamp` <= DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 DAY)";
$result = debug_query($sql);
  
  
/**
 *
 */
function get_min_max_mid($data){
    //echo "get_min_max_mid";
    $statistics = array("timestamp"=>0,
                        "num_sensors"=>array(),
                        "num_values"=>0,
                        "P1"=>array("min"=>1000000,
                                    "max"=>0,
                                    "mid"=>0,
                                    "max_main"=>"",
                                    "min_main"=>"",
                                    "max_sensor_id"=>"",
                                    "min_sensor_id"=>""
                                    ),
                        "P2"=>array("min"=>1000000,
                                    "max"=>0,
                                    "mid"=>0,
                                    "max_main"=>"",
                                    "min_main"=>"",
                                    "max_sensor_id"=>"",
                                    "min_sensor_id"=>""
                                    )
                        );
    $p1 = array();
    $p2 = array();
    $timestamps = array();
    foreach($data as $dataset){
      array_push($timestamps,$dataset->timestamp);
      echo "<pre>".print_r($dataset,true)."</pre>";
      if($dataset->P1 < $statistics["P1"]["min"]) {
        $statistics["P1"]["min"] = floatval($dataset->P1);
        $statistics["P1"]["min_sensor_id"] = intval($dataset->sensor_id);
      }
      if($dataset->P1 > $statistics["P1"]["max"]) {
        $statistics["P1"]["max"] = floatval($dataset->P1);
        $statistics["P1"]["max_sensor_id"] = intval($dataset->sensor_id);
      }
      array_push($p1,floatval($dataset->P1));
      if($dataset->P2 < $statistics["P2"]["min"]) {
        $statistics["P2"]["min"] = floatval($dataset->P2);
        $statistics["P2"]["min_sensor_id"] = intval($dataset->sensor_id);
      }
      if($dataset->P2 > $statistics["P2"]["max"]) {
        $statistics["P2"]["max"] = floatval($dataset->P2);
        $statistics["P2"]["max_sensor_id"] = intval($dataset->sensor_id);
      }
      array_push($p2,floatval($dataset->P2));
      if(!in_array($dataset->sensor_id, $statistics["num_sensors"])){
        array_push($statistics["num_sensors"],$dataset->sensor_id);
      }
    }
    $statistics["timestamp"] = array_median($timestamps);
    $statistics["num_values"] = count($data);
    $statistics["num_sensors"] = count($statistics["num_sensors"]);
    $statistics["P1"]["mid"] = array_median($p1);
    $statistics["P2"]["mid"] = array_median($p2);
    $mainSectorP1 = array_main_sector($p1);
    $statistics["P1"]["max_main"] = $mainSectorP1["max"];
    $statistics["P1"]["min_main"] = $mainSectorP1["min"];
    $mainSectorP2 = array_main_sector($p2);
    $statistics["P2"]["max_main"] = $mainSectorP2["max"];
    $statistics["P2"]["min_main"] = $mainSectorP2["min"];
    return $statistics;
}

?>
