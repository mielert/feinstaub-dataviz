<?php
include_once("library.php");

$city_id = 1;

$sql = "SELECT MIN(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id";
$result = debug_query($sql);
if($result[0]->timestamp == "") {
  echo "cities_mean for $city_id is empty";
  $sql = "SELECT MIN(`timestamp`) AS timestamp FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id)";
  $result = debug_query($sql);
  $starttime = $result[0]->timestamp;
}
else{
  $sql = "SELECT MIN(`timestamp`) AS timestamp FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id AND `timestamp` > '".$result[0]->timestamp."')";
  $result = debug_query($sql);
  $starttime = $result[0]->timestamp;
}
$sql = "SELECT * FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id)";
//debug_query($sql);
?>
