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

$sql = "SELECT * 
	FROM `cron_jobs` 
	WHERE (NOW() > DATE_ADD(`last_execution`, INTERVAL `interval` SECOND) 
	OR `last_execution` IS NULL)
	AND activated = 1
	LIMIT 1";
$to_run = debug_query($sql);
if(count($to_run)>0){
	$result = include_once($to_run[0]->script);
	if($result){
		$sql = "UPDATE `cron_jobs` SET `last_execution` = NOW() WHERE `id` = ".$to_run[0]->id;
		$result = debug_query($sql);
	}
	else echo "error";
}


?>
