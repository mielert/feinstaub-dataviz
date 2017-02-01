
<html>
	<head>
		<script src="js/jquery.min.js" type="text/javascript"></script>
	</head>
	<body>
		<pre>
<?php
/**
 * chronological data of districts
 * database driven
 * http://fritzmielert.de/feinstaub/chronological_data_of_districts_db.php?starttime=1483052400
 */
include_once("library.php");

echo "1.9.2016 ".strtotime("2016-09-01 00:00:00")."<br/>";
 
if(isset($_GET["starttime"])){
	$starttime = date("Y-m-d H:00:00", intval($_GET["starttime"]));
}
else{
	$sql = "SELECT MAX(timestamp) AS timestamp FROM districts_mean";
	$results = db_select($sql);
	$starttime = $results[0]->timestamp;
	//$starttime = "2016-09-01 00:00:00";
}
echo $starttime."<br/>";

echo "looking for sensors without district<br/>";

$sql = "SELECT DISTINCT `sensors_hourly_mean`.`lon`,
						`sensors_hourly_mean`.`lat`,
						`district_id`
		FROM `sensors_hourly_mean`
		LEFT JOIN `x_coordinates_districts` ON `sensors_hourly_mean`.`lon` = `x_coordinates_districts`.`lon` AND `sensors_hourly_mean`.`lat` = `x_coordinates_districts`.`lat`
		WHERE `timestamp` > ('$starttime' - INTERVAL 1 DAY)
		AND `timestamp` <= \"$starttime\"";

//echo $sql."<br/>";

$results = debug_query($sql);

//print_r($results);

foreach($results as $result){
	if(!$result->district_id){
		$district = point_in_city($result->lon,$result->lat,"Stuttgart");
		if($district){
			$sql = "SELECT `id` FROM `districts` WHERE `name` = '$district'";
			echo $sql;
			$res_district_id = db_select($sql);
			print_r($res_district_id);
			$res_district_id = $res_district_id[0];
			$district_id = $res_district_id->id;
		}
		else $district_id = -1;
		echo "district_id: $district_id";
		$sql = "INSERT INTO `x_coordinates_districts` (`district_id`, `lon`, `lat`)
				VALUES (".$district_id.",".$result->lon.",".$result->lat.")";
		echo $sql;
		db_insert($sql);
	}
}

echo "looking for sensor data missing in districts<br/>";

// prepare hourly mean
$sql = "SELECT 	`sensors_hourly_mean`.`lon`,
				`sensors_hourly_mean`.`lat`,
				`district_id`,
				avg(P1) AS P1h,
				avg(P2) AS P2h
		FROM `sensors_hourly_mean`
		LEFT JOIN `x_coordinates_districts` ON `sensors_hourly_mean`.`lon` = `x_coordinates_districts`.`lon` AND `sensors_hourly_mean`.`lat` = `x_coordinates_districts`.`lat`
		WHERE `timestamp` > ('$starttime' - INTERVAL 1 HOUR)
		AND `timestamp` <= \"$starttime\"
		AND `district_id` > 0
		GROUP BY lon,lat";

//echo $sql."<br/>";

$results = debug_query($sql);

//print_r($results);

$toMakeMedian = array();
foreach($results as $result){
	if($result->district_id > 0){
		if(!isset($toMakeMedian[$result->district_id])) $toMakeMedian[$result->district_id] = array("P1h"=>array(),"P2h"=>array(),"P1d"=>array(),"P2d"=>array());
		array_push($toMakeMedian[$result->district_id]["P1h"], $result->P1h);
		array_push($toMakeMedian[$result->district_id]["P2h"], $result->P2h);
	}
}

// prepare 24 h mean
$sql = "SELECT 	`sensors_hourly_mean`.`lon`,
				`sensors_hourly_mean`.`lat`,
				`district_id`,
				avg(P1) AS P1d,
				avg(P2) AS P2d
		FROM `sensors_hourly_mean`
		LEFT JOIN `x_coordinates_districts` ON `sensors_hourly_mean`.`lon` = `x_coordinates_districts`.`lon` AND `sensors_hourly_mean`.`lat` = `x_coordinates_districts`.`lat`
		WHERE `timestamp` > ('$starttime' - INTERVAL 1 DAY)
		AND `timestamp` <= \"$starttime\"
		AND `district_id` > 0
		GROUP BY lon,lat";

//echo $sql."<br/>";

$results = db_select($sql);

//print_r($results);

foreach($results as $result){
	if($result->district_id > 0){
		if(!isset($toMakeMedian[$result->district_id])) $toMakeMedian[$result->district_id] = array("P1h"=>array(),"P2h"=>array(),"P1d"=>array(),"P2d"=>array());
		array_push($toMakeMedian[$result->district_id]["P1d"], $result->P1d);
		array_push($toMakeMedian[$result->district_id]["P2d"], $result->P2d);
	}
}
if(count($toMakeMedian)>0){
	$rawSql = array();
	foreach($toMakeMedian as $key=>&$item){
		echo "District $key P1h: ".print_r($item["P1h"],true)." -> ".array_median($item["P1h"])."<br/>";
		$item["P1h"] = array_median($item["P1h"]);
		$item["P2h"] = array_median($item["P2h"]);
		$item["P1d"] = array_median($item["P1d"]);
		$item["P2d"] = array_median($item["P2d"]);
		array_push($rawSql,"(NULL,$key,\"$starttime\",".$item["P1h"].",".$item["P2h"].",".$item["P1d"].",".$item["P2d"].")");
	}
	//array_median($array)
	//print_r($toMakeMedian);
	
	foreach($toMakeMedian as $key=>$item){
		
	}
	//print_r($rawSql);
	$sql = "INSERT INTO `districts_mean` (`id`,`district_id`,`timestamp`,`P1h`,`P2h`,`P1d`,`P2d`)
VALUES 
".join(",\n",$rawSql)." ON DUPLICATE KEY UPDATE `P1d` = Values(`P1d`), `P2d` = Values(`P2d`), `P1h` = Values(`P1h`), `P2h` = Values(`P2h`)";
	echo $sql;
	db_insert($sql);
}
//exit;
echo "</pre>";

$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `sensors_hourly_mean`";
$results = db_select($sql);
$stop = $results[0]->timestamp;

if($starttime < $stop){
	// cron error
	if(!isset($_GET["starttime"]))
		echo file_get_contents($project_url."crawler_db_chronological_data_of_districts.php?starttime=".strtotime($starttime." + 1 hours"));
	
	echo "$starttime < $stop";
	echo '		<script>
			$(document).ready(function(){
				window.location.href = "crawler_db_chronological_data_of_districts.php?starttime='.strtotime($starttime." + 1 hours").'";
			});
			</script>';
}
else {
	echo "$starttime >= $stop";
}

?>
	</body>
</html>
