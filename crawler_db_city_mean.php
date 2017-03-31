<?php
if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; 

include_once("library.php");
$log = false;
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r6, start\n", FILE_APPEND | LOCK_EX);

$parent_region_id = 24;
$city_id = 1;

// get start time
$starttime = false;
$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id";
$result = db_select($sql);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r15, $sql\n", FILE_APPEND | LOCK_EX);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r16, ".$result[0]->timestamp."\n", FILE_APPEND | LOCK_EX);
if($result[0]->timestamp == "") {
  //echo "cities_mean for city_id = $city_id is empty";
  $sql = "SELECT MIN(`timestamp`) AS timestamp 
          FROM `sensor_data` 
          LEFT JOIN `sensors` ON `sensors`.`id` = `sensor_data`.`sensor_id`
          WHERE `lon` IN (SELECT `lon` 
                          FROM `regions`
                          LEFT JOIN `x_coordinates_regions` ON `x_coordinates_regions`.`region_id` = `regions`.`id`
                          WHERE `regions`.`parent_region_id` = $parent_region_id)
          AND `lat` IN (SELECT `lat` 
                          FROM `regions`
                          LEFT JOIN `x_coordinates_regions` ON `x_coordinates_regions`.`region_id` = `regions`.`id`
                          WHERE `regions`.`parent_region_id` = $parent_region_id)
          AND `sensors`.`type_id` = 1";
  $result = db_select($sql);
  $starttime = $result[0]->timestamp;
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r15, $sql\n", FILE_APPEND | LOCK_EX);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r16, ".$result[0]->timestamp."\n", FILE_APPEND | LOCK_EX);
}
else{
  $sql = "SELECT MIN(`timestamp`) AS timestamp 
          FROM `sensor_data` 
          LEFT JOIN `sensors` ON `sensors`.`id` = `sensor_data`.`sensor_id`
          WHERE `lon` IN (SELECT `lon` 
                          FROM `regions`
                          LEFT JOIN `x_coordinates_regions` ON `x_coordinates_regions`.`region_id` = `regions`.`id`
                          WHERE `regions`.`parent_region_id` = $parent_region_id)
          AND `lat` IN (SELECT `lat` 
                          FROM `regions`
                          LEFT JOIN `x_coordinates_regions` ON `x_coordinates_regions`.`region_id` = `regions`.`id`
                          WHERE `regions`.`parent_region_id` = $parent_region_id)
          AND `sensors`.`type_id` = 1
          AND `timestamp` > '".$result[0]->timestamp."'";
  $result = db_select($sql);
  $starttime = $result[0]->timestamp;
  if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r15, $sql\n", FILE_APPEND | LOCK_EX);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r16, ".$result[0]->timestamp."\n", FILE_APPEND | LOCK_EX);

}
//$starttime = "2017-03-21 12:00:00";
if(!$starttime){
  echo "nothing to do with parent_region_id = $parent_region_id (timestamp: $starttime)";
  if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r52, nothing to do with parent_region_id = $parent_region_id (timestamp: $starttime)\n", FILE_APPEND | LOCK_EX);
  exit;
}
else {
  echo "start time of city_id = $city_id = $starttime ";
  if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r57, start time of city_id = $city_id = $starttime\n", FILE_APPEND | LOCK_EX);
  $sql = "SELECT DATE_ADD(MAX(timestamp), INTERVAL -1 HOUR) AS timestamp 
          FROM `sensor_data`
          LEFT JOIN `sensors` ON `sensors`.`id` = `sensor_data`.`sensor_id`
          WHERE `sensors`.`type_id` = 1";
  $result = db_select($sql);
  $endtime = $result[0]->timestamp;
  if($starttime > $endtime) {
    echo "$starttime > $endtime (DATE_ADD(MAX(timestamp), INTERVAL -1 HOUR) FROM `sensor_data`) ";
    echo "nothing to do ";
    if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r67, $starttime > $endtime (DATE_ADD(MAX(timestamp), INTERVAL -1 HOUR) FROM `sensor_data`) nothing to do \n", FILE_APPEND | LOCK_EX);
    exit;
  }
}
/*
$sql = "SELECT DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 HOUR) AS added";
$result = db_select($sql);
echo $result[0]->added;
$sql = "SELECT DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 2 HOUR) AS added";
$result = db_select($sql);
echo $result[0]->added;
*/
//if($result[0]->added == substr($starttime,0,13).":00:00")
// get sensor data
$sql = "SELECT * 
          FROM `sensor_data` 
          LEFT JOIN `sensors` ON `sensors`.`id` = `sensor_data`.`sensor_id`
          WHERE `lon` IN (SELECT `lon` 
                          FROM `regions`
                          LEFT JOIN `x_coordinates_regions` ON `x_coordinates_regions`.`region_id` = `regions`.`id`
                          WHERE `regions`.`parent_region_id` = $parent_region_id)
          AND `lat` IN (SELECT `lat` 
                          FROM `regions`
                          LEFT JOIN `x_coordinates_regions` ON `x_coordinates_regions`.`region_id` = `regions`.`id`
                          WHERE `regions`.`parent_region_id` = $parent_region_id)
          AND `sensor_data`.`timestamp` >  '".substr($starttime,0,13).":00:00'
          AND `sensor_data`.`timestamp` <= DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 HOUR)
          AND `sensors`.`type_id` = 1";
$result = db_select($sql);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r96, $sql\n", FILE_APPEND | LOCK_EX);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r97, ".count($result)."\n", FILE_APPEND | LOCK_EX);

//print_r($result);

// calculate city data
$statistics = get_min_max_mid($result);
$result = db_select("SELECT DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 HOUR) AS timestamp");
$statistics["timestamp"] = $result[0]->timestamp;
//echo "<pre>".print_r($statistics,true)."</pre>";
$sql = "INSERT INTO `cities_mean` (`id`, `city_id`, `timestamp`, `P1h`, `P2h`, `P1d`, `P2d`, `P1h_min`, `P1h_max`, `P2h_min`, `P2h_max`, `P1h_50_min`, `P1h_50_max`, `P2h_50_min`, `P2h_50_max`, `P1min_sensor_id`, `P1max_sensor_id`, `P2min_sensor_id`, `P2max_sensor_id`, `num_sensors`, `num_values`)
        VALUES (NULL, '$city_id', '".$statistics["timestamp"]."', '".$statistics["P1"]["mid"]."', '".$statistics["P2"]["mid"]."', 0, 0, '".$statistics["P1"]["min"]."', '".$statistics["P1"]["max"]."', '".$statistics["P2"]["min"]."', '".$statistics["P2"]["max"]."', '".$statistics["P1"]["max_main"]."', '".$statistics["P1"]["min_main"]."', '".$statistics["P2"]["max_main"]."', '".$statistics["P2"]["min_main"]."', '".$statistics["P1"]["min_sensor_id"]."', '".$statistics["P1"]["max_sensor_id"]."', '".$statistics["P2"]["min_sensor_id"]."', '".$statistics["P2"]["max_sensor_id"]."', ".$statistics["num_sensors"].", ".$statistics["num_values"].")";
$res_insert = db_insert($sql);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r116, $sql \n -> $res_insert\n", FILE_APPEND | LOCK_EX);
// daylight saving time
if(!$res_insert){
  $sql = "INSERT INTO `cities_mean` (`id`, `city_id`, `timestamp`, `P1h`, `P2h`, `P1d`, `P2d`, `P1h_min`, `P1h_max`, `P2h_min`, `P2h_max`, `P1h_50_min`, `P1h_50_max`, `P2h_50_min`, `P2h_50_max`, `P1min_sensor_id`, `P1max_sensor_id`, `P2min_sensor_id`, `P2max_sensor_id`, `num_sensors`, `num_values`)
          VALUES (NULL, '$city_id', DATE_ADD('".$statistics["timestamp"]."',INTERVAL 1 HOUR), '".$statistics["P1"]["mid"]."', '".$statistics["P2"]["mid"]."', 0, 0, '".$statistics["P1"]["min"]."', '".$statistics["P1"]["max"]."', '".$statistics["P2"]["min"]."', '".$statistics["P2"]["max"]."', '".$statistics["P1"]["max_main"]."', '".$statistics["P1"]["min_main"]."', '".$statistics["P2"]["max_main"]."', '".$statistics["P2"]["min_main"]."', '".$statistics["P1"]["min_sensor_id"]."', '".$statistics["P1"]["max_sensor_id"]."', '".$statistics["P2"]["min_sensor_id"]."', '".$statistics["P2"]["max_sensor_id"]."', ".$statistics["num_sensors"].", ".$statistics["num_values"].")";
  db_insert($sql);

}
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r122, $sql \n -> $res\n", FILE_APPEND | LOCK_EX);

// add 24h floating
$sql = "SELECT AVG(P1h) AS P1d, AVG(P2h) AS P2d
        FROM `cities_mean` 
        WHERE `city_id` = $city_id
        AND `timestamp` >  DATE_ADD(DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL -1 DAY),INTERVAL 1 HOUR)
        AND `timestamp` <= DATE_ADD('".substr($starttime,0,13).":00:00',INTERVAL 1 HOUR)";
$result = db_select($sql);
if($res_insert)
  $sql = "UPDATE `cities_mean` SET `P1d` = ".$result[0]->P1d.", `P2d` = ".$result[0]->P2d." WHERE `city_id` = $city_id AND `timestamp` = '".$statistics["timestamp"]."'";
else
  $sql = "UPDATE `cities_mean` SET `P1d` = ".$result[0]->P1d.", `P2d` = ".$result[0]->P2d." WHERE `city_id` = $city_id AND `timestamp` = DATE_ADD('".$statistics["timestamp"]."',INTERVAL 1 HOUR)";

$res = db_insert($sql);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r120, $sql \n -> $res\n", FILE_APPEND | LOCK_EX);
if($log) file_put_contents("crawler_db_city_mean.log", date('Y-m-d H:i:s')."	r121, done.\n\n", FILE_APPEND | LOCK_EX);

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
      //echo "<pre>".print_r($dataset,true)."</pre>";
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
