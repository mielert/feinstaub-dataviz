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

print_r(get_min_max_mid($result));
  
  
/**
 *
 */
function get_min_max_mid($data){
    //echo "get_min_max_mid";
    $statistics = array("timestamp"=>0,
                        "num_sensors"=>0,
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
    }
    $statistics["timestamp"] = array_median($timestamps);
    $statistics["num_sensors"] = count($data);
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
/**
 *
 */
function generate_24h_floating_values($cronological_data){
    $timestampMin = $cronological_data[0]["timestamp"];
    $timestampStartFloating = 0;
    $timestampMax = 0;
    for($i=0;$i<count($cronological_data);$i++){
        if(!isset($cronological_data[$i]["P1"]["24floating"]) || !isset($cronological_data[$i]["P2"]["24floating"])){
            $values24hP10 = array();
            $values24hP25 = array();
            for($j=$i;$j>=0;$j--){
                if($cronological_data[$j]["timestamp"] < $cronological_data[$i]["timestamp"] - 60*60*24){
                    echo date("Ymd H:i:s",$cronological_data[$j]["timestamp"])." - ".date("Ymd H:i:s",$cronological_data[$i]["timestamp"])."<br/>";
                    $cronological_data[$i]["P1"]["24floating"] = array_arithmetic_mean($values24hP10);
                    $cronological_data[$i]["P2"]["24floating"] = array_arithmetic_mean($values24hP25);
                    echo "P10:". $cronological_data[$i]["P1"]["24floating"]."; P2.5: ".$cronological_data[$i]["P2"]["24floating"]."<br/>";
                    break;
                }
                array_push($values24hP10,$cronological_data[$j]["P1"]["mid"]);
                array_push($values24hP25,$cronological_data[$j]["P2"]["mid"]);
            }
        }
    }
    return $cronological_data;
}
?>
