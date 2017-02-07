<?php
include_once("library.php");

$city_id = 1;
$sql = "SELECT * FROM `districts_mean` WHERE `district_id` IN (SELECT `id` FROM `districts` WHERE `city_id` = $city_id)";
debug_query($sql);
?>
