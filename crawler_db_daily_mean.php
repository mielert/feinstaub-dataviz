<?php
//if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit;
/**
 * crawler
 */

include_once("library.php");

// daily
$sql = "SELECT `sensors_hourly_mean`.`id`, `sensors_hourly_mean`.`timestamp`,
		(	SELECT AVG(shm.P1)
			FROM `sensors_hourly_mean` AS shm
			WHERE shm.sensor_id = `sensors_hourly_mean`.`sensor_id`
			AND shm.`timestamp` <= `sensors_hourly_mean`.`timestamp`
			AND shm.`timestamp` > (`sensors_hourly_mean`.`timestamp`  - INTERVAL 1 DAY) ) AS P1d_new,
		(	SELECT AVG(shm.P2)
			FROM `sensors_hourly_mean` AS shm
			WHERE shm.sensor_id = `sensors_hourly_mean`.`sensor_id`
			AND shm.`timestamp` <= `sensors_hourly_mean`.`timestamp`
			AND shm.`timestamp` > (`sensors_hourly_mean`.`timestamp`  - INTERVAL 1 DAY) ) AS P2d_new
		FROM `sensors_hourly_mean`
		WHERE (`sensors_hourly_mean`.`P1d` IS NULL
		OR `sensors_hourly_mean`.`P2d` IS NULL
		OR `sensors_hourly_mean`.`P1d` = 0
		OR `sensors_hourly_mean`.`P2d` = 0)
		AND (`sensors_hourly_mean`.`P1` IS NOT NULL
		OR `sensors_hourly_mean`.`P2` IS NOT NULL)
		ORDER BY `sensors_hourly_mean`.`timestamp`
		LIMIT 500";
$results = debug_query($sql);
foreach($results as $result){
	$sql = "UPDATE `sensors_hourly_mean` SET `P1d` = ".$result->P1d_new.", `P2d` = ".$result->P2d_new." WHERE `id` = ".$result->id;
	debug_query($sql);
}
?>
