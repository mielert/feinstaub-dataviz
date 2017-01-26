<?php
include_once("library.php");
echo "cron<br/>";
/*
lubw_collector.php
collector.php
luftdaten_crawler.php
generate_districts_past.php
markersMapped.php
chronological_data_of_districts.php
*/

$sql = "SELECT * FROM `cron_jobs` WHERE NOW() > DATE_ADD(`last_execution`, INTERVAL `interval` SECOND) OR `last_execution` IS NULL LIMIT 1";
$result = debug_query($sql);
if(count($result)>0){
  include_once($result[0]->script);
  $sql = "UPDATE `cron_jobs` SET `last_execution` = NOW() WHERE `id` = ".$result[0]->id;
  $result = debug_query($sql);
}


?>
