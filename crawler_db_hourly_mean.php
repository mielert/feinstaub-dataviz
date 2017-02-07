<html>
	<head>
		<script src="../js/jquery.min.js" type="text/javascript"></script>
	</head>
	<body>
<?php
/**
 * crawler
 */

include_once("library.php");

//SELECT MAX(`timestamp`) FROM `sensors_hourly_mean` WHERE 1
$res_max_timestamp = db_select("SELECT MAX(`timestamp`) AS timestamp FROM `sensors_hourly_mean`");
//print_r($res_max_timestamp);
if($res_max_timestamp[0]->timestamp > 1){
	$startdate = strtotime($res_max_timestamp[0]->timestamp)+60*60;
}
// start: 31.8.2016
else $startdate = strtotime("2016-12-27 00:00:00");
//$startdate = strtotime("2017-01-11 00:00:00");
// end: 17.12.2016
//$stopdate = strtotime("2016-12-17 23:59:59");

$sensorsearchdate = $startdate;
do {
	$sql = "SELECT DISTINCT `sensor_id`,`lon`,`lat`
			FROM `sensor_data`
			WHERE `lon` <> 0
			AND `lat` <> 0
			AND `timestamp` > '".date('Y-m-d H:i:s', $sensorsearchdate-60*60)."'
			AND `timestamp` <='".date('Y-m-d H:i:s', $sensorsearchdate)."'";
	$results = db_select($sql);
	
	echo date('Y-m-d H:i:s', $sensorsearchdate).": ".count($results)." Sensoren<br/>";
	$sensorsearchdate+=60*60;
	break;
} while (count($results) == 0);
//print_r($results);
$startdate = $sensorsearchdate-=60*60;


foreach($results as $result){
	// hourly
	$sql1 = "SELECT avg(P1) AS P1, avg(P2) AS P2
			FROM sensor_data
			WHERE `sensor_id` = ".$result->sensor_id."
			AND `lon` = ".$result->lon."
			AND `lat` = ".$result->lat."
			AND `timestamp` > '".date('Y-m-d H:i:s', $startdate-60*60)."'
			AND `timestamp` <='".date('Y-m-d H:i:s', $startdate)."'";
	//print_r($sql);
	$results2 = db_select($sql1);
	//print_r($results2);
	if($results2[0]->P1 != ""){
		$sql = "INSERT INTO `sensors_hourly_mean` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`)
				VALUES (NULL,".$result->sensor_id.",".$result->lon.",".$result->lat.",'".date('Y-m-d H:i:s', $startdate)."',".$results2[0]->P1.",".$results2[0]->P2.")
				ON DUPLICATE KEY UPDATE `P1` = VALUES(`P1`), `P2` = VALUES(`P2`); ";
		echo "$sql<br/>";
		db_insert($sql);
	}

	// daily
	/*
	$sql = "SELECT `sensor_id`,`lon`,`lat`, avg(P1)
			FROM sensors
			WHERE `sensor_id` = ".$result->sensor_id."
			AND `lon` = ".$result->lon."
			AND `lat` = ".$result->lat."
			AND `timestamp` > '".date('Y-m-d H:i:s', $startdate-24*60*60)."'
			AND `timestamp` <='".date('Y-m-d H:i:s', $startdate)."'";
	print_r($sql);
	$results2 = db_select($sql);
	print_r($results2);
	*/
	//break;
}

$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `sensor_data`";
$results = db_select($sql);
$stop = $results[0]->timestamp;

if($startdate < strtotime($stop)-60*60)
echo '		<script>
		$(document).ready(function(){
			location.reload();
		});
		</script>';

?>
	</body>
</html>
