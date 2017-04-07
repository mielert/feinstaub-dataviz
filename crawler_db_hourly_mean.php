<?php 
//if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit;
/**
 * crawler
 *
 * If you want to delete data, please use this query:
 * DELETE FROM `sensors_hourly_mean` WHERE `sensor_id` NOT IN (643, 644) AND `timestamp` > "2017-xx-xx 00:00:00" AND `timestamp` <= "2017-xx-xx 23:59:59"
 */

include_once("library.php");
$log = false;
if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	start\n", FILE_APPEND | LOCK_EX);

$sql = "SELECT `id` FROM `sensors` WHERE `type_id` <> 2";
$results = db_select($sql);
foreach($results as &$result){
	$result = $result->id;
}
$sensor_ids = join(",",$results);
//echo $sensor_ids;

//SELECT MAX(`timestamp`) FROM `sensors_hourly_mean` WHERE 1
$sql = "SELECT MAX(`timestamp`) AS timestamp
		FROM `sensors_hourly_mean`
		WHERE `sensor_id` IN ($sensor_ids)";
$res_max_timestamp = db_select($sql);
//print_r($res_max_timestamp);
if($res_max_timestamp[0]->timestamp > 1){
	$startdate = strtotime($res_max_timestamp[0]->timestamp)+60*60;
}
// start: 31.8.2016
else $startdate = strtotime("2016-12-27 00:00:00");

// max_timestamp_sensor_data has to be startdate - 1 hour
$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `sensor_data`";
$res_max_timestamp_sensor_data = db_select($sql);
$max_timestamp_sensor_data = strtotime($res_max_timestamp_sensor_data[0]->timestamp);
if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	max_timestamp_sensor_data: $max_timestamp_sensor_data\n", FILE_APPEND | LOCK_EX);
if($max_timestamp_sensor_data < $startdate){
	if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	nothing to do", FILE_APPEND | LOCK_EX);
	echo "nothing to do.";
	exit;
}


if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	startdate: $startdate\n", FILE_APPEND | LOCK_EX);


if(date('Y-m-d H:i:s') < date('Y-m-d H:i:s', $sensorsearchdate)){
	echo "nothing to do";
	if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	nothing to do\n", FILE_APPEND | LOCK_EX);

}
else{
	//$startdate = strtotime("2017-03-20 00:00:00");
	// end: 17.12.2016
	//$stopdate = strtotime("2016-12-17 23:59:59");
	
	// bullshit???
	$sensorsearchdate = $startdate;
	do {
		$sql = "SELECT DISTINCT `sensor_id`,`lon`,`lat`
				FROM `sensor_data`
				WHERE `lon` <> 0
				AND `lat` <> 0
				AND `timestamp` > '".date('Y-m-d H:i:s', $sensorsearchdate-60*60)."'
				AND `timestamp` <='".date('Y-m-d H:i:s', $sensorsearchdate)."'
				AND `sensor_id` IN ($sensor_ids)";
		$results = db_select($sql);
		
		//echo date('Y-m-d H:i:s', $sensorsearchdate).": ".count($results)." Sensoren<br/>";
		$script_result = date('Y-m-d H:i:s', $sensorsearchdate).": ".count($results)." sensors";
		$sensorsearchdate+=60*60;
		break;
	} while (count($results) == 0);
	//print_r($results);
	$startdate = $sensorsearchdate-=60*60;
	if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	r63, sql: $sql -> ".count($results)."\n", FILE_APPEND | LOCK_EX);
	if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	r64, new startdate: $startdate\n", FILE_APPEND | LOCK_EX);
	$counter = 0;
	foreach($results as $result){
		$sql1 = "SELECT avg(P1) AS P1, avg(P2) AS P2
				FROM sensor_data
				WHERE `sensor_id` = ".$result->sensor_id."
				AND `lon` = ".$result->lon."
				AND `lat` = ".$result->lat."
				AND `timestamp` > '".date('Y-m-d H:i:s', $startdate-60*60)."'
				AND `timestamp` <='".date('Y-m-d H:i:s', $startdate)."'";
		//print_r($sql);
		$results2 = db_select($sql1);
		if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	r76, #$counter sql1: $sql1 -> ".count($results)."\n", FILE_APPEND | LOCK_EX);
		//print_r($results2);
		if($results2[0]->P1 != ""){
			$sql = "SELECT `sensors_hourly_mean`.`id`,
					(	SELECT AVG(shm.P1)
						FROM `sensors_hourly_mean` AS shm
						WHERE shm.sensor_id = ".$result->sensor_id."
						AND shm.`timestamp` <= '".date('Y-m-d H:i:s', $startdate)."'
						AND shm.`timestamp` > ('".date('Y-m-d H:i:s', $startdate)."'  - INTERVAL 1 DAY) ) AS P1d_new,
					(	SELECT AVG(shm.P2)
						FROM `sensors_hourly_mean` AS shm
						WHERE shm.sensor_id = ".$result->sensor_id."
						AND shm.`timestamp` <= '".date('Y-m-d H:i:s', $startdate)."'
						AND shm.`timestamp` > ('".date('Y-m-d H:i:s', $startdate)."'  - INTERVAL 1 DAY) ) AS P2d_new
					FROM `sensors_hourly_mean`
					LIMIT 1";
			$results3 = db_select($sql);
			$result3 = $results3[0];
			if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	r94, #$counter sql: $sql\n", FILE_APPEND | LOCK_EX);
			$sql = "INSERT INTO `sensors_hourly_mean` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`, `P1d`, `P2d`)
					VALUES (NULL,".$result->sensor_id.",".$result->lon.",".$result->lat.",'".date('Y-m-d H:i:s', $startdate)."',".$results2[0]->P1.",".$results2[0]->P2.",".$result3->P1d_new.",".$result3->P2d_new.")
					ON DUPLICATE KEY UPDATE `P1` = VALUES(`P1`), `P2` = VALUES(`P2`); ";
			//echo "$sql<br/>";
			db_insert($sql);
			if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	r84, #$counter $sql\n", FILE_APPEND | LOCK_EX);

			//$sql = "UPDATE `sensors_hourly_mean` SET `P1d` = ".$result3->P1d_new.", `P2d` = ".$result3->P2d_new." WHERE `sensor_id` = ".$result->sensor_id." AND `timestamp` = '".date('Y-m-d H:i:s', $startdate)."'";
			//db_insert($sql);
			//file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	r103, #$counter sql: $sql\n", FILE_APPEND | LOCK_EX);

		}
		$counter++;
	}
	echo $script_result;
}
if($log) file_put_contents("crawler_db_hourly_mean.log", date('Y-m-d H:i:s')."	done.\n\n", FILE_APPEND | LOCK_EX);

?>
