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

$sql = "SELECT * FROM `cron_jobs` WHERE NOW() > DATE_ADD(`last_execution`, INTERVAL `interval` SECOND)";
debug_query($sql);


?>
