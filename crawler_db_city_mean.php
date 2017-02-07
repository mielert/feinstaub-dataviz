<?php
include_once("library.php");

$city_id = 1;

// get start time
$starttime = false;
$sql = "SELECT MIN(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id";
$result = debug_query($sql);
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
  $result = debug_query($sql);
  $starttime = $result[0]->timestamp;
}
else{
  $sql = "SELECT MIN(`timestamp`) AS timestamp FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id AND `timestamp` > '".$result[0]->timestamp."')";
  $result = debug_query($sql);
  $starttime = $result[0]->timestamp;
}
if(!$starttime){
  echo "no start time because of empty table districts_mean at city_id = $city_id";
  exit;
}
// get district data
$sql = "SELECT * FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id) AND `timestamp` = '".substr($starttime,0,13).":00:00'";
debug_query($sql);
?>
