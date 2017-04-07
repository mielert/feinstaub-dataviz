<?php 
//if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; 
/**
 * chronological data of districts
 * database driven
 */
include_once("library.php");
$log = false;
if($log) file_put_contents("crawler_db_chronological_data_of_districts.log", date('Y-m-d H:i:s')."	start\n", FILE_APPEND | LOCK_EX);


//echo "1.9.2016 ".strtotime("2016-09-01 00:00:00")."<br/>";
//echo "2017-01-29 04:00:00 ".strtotime("2017-01-29 04:00:00")."<br/>";
 
if(isset($_GET["starttime"])){
	$starttime = date("Y-m-d H:00:00", intval($_GET["starttime"]));
}
else{
	$sql = "SELECT MAX(timestamp) AS timestamp FROM regions_mean";
	$results = db_select($sql);
	$starttime1 = $results[0]->timestamp;
	$sql = "SELECT MAX(timestamp) AS timestamp FROM sensors_hourly_mean WHERE sensor_id IN (SELECT id FROM sensors WHERE type_id = 1)";
	$results = db_select($sql);
	$starttime2 = $results[0]->timestamp;
	//echo "starttime1: $starttime1 starttime2: $starttime2 ";
	if($starttime1 < $starttime2) $starttime = $starttime1;
	else $starttime = $starttime2;
	//$starttime = "2017-03-22 11:00:00";
}
if($log) file_put_contents("crawler_db_chronological_data_of_districts.log", date('Y-m-d H:i:s')."	starttime: $starttime\n", FILE_APPEND | LOCK_EX);

echo "$starttime crawled";

//echo "looking for sensors without regions<br/>";

$sql = "SELECT DISTINCT `sensors_hourly_mean`.`lon`,
						`sensors_hourly_mean`.`lat`,
						`x_coordinates_regions`.`region_id`
		FROM `sensors_hourly_mean`
		LEFT JOIN `x_coordinates_regions` ON `sensors_hourly_mean`.`lon` = `x_coordinates_regions`.`lon` AND `sensors_hourly_mean`.`lat` = `x_coordinates_regions`.`lat`
		WHERE `timestamp` > ('$starttime' - INTERVAL 1 DAY)
		AND `timestamp` <= \"$starttime\"";

//echo $sql."<br/>";

$results = db_select($sql);
if($log) file_put_contents("crawler_db_chronological_data_of_districts.log", date('Y-m-d H:i:s')."	$sql.\n".count($results)."\n", FILE_APPEND | LOCK_EX);

//print_r($results);

foreach($results as $result){
	//print_r($result);
	if($result->region_id != -1 && !($result->region_id > 0)){
		$region = point_in_city($result->lon,$result->lat,"Stuttgart");
		if($region){
			$sql = "SELECT `id` FROM `regions` WHERE `name` = '$region'";
			//echo $sql;
			$res_region_id = db_select($sql);
			//print_r($res_region_id);
			$res_region_id = $res_region_id[0];
			$region_id = $res_region_id->id;
		}
		else $region_id = -1;
		//echo "region_id: $region_id";
		$sql = "INSERT INTO `x_coordinates_regions` (`region_id`, `lon`, `lat`)
				VALUES (".$region_id.",".$result->lon.",".$result->lat.")";
		//echo $sql;
		db_insert($sql);
	}
}

//echo "looking for sensor data missing in regions<br/>";

// prepare hourly mean
$sql = "SELECT 	`sensors_hourly_mean`.`lon`,
				`sensors_hourly_mean`.`lat`,
				`region_id`,
				avg(P1) AS P1h,
				avg(P2) AS P2h
		FROM `sensors_hourly_mean`
		LEFT JOIN `x_coordinates_regions` ON `sensors_hourly_mean`.`lon` = `x_coordinates_regions`.`lon` AND `sensors_hourly_mean`.`lat` = `x_coordinates_regions`.`lat`
		WHERE `timestamp` > ('$starttime' - INTERVAL 1 HOUR)
		AND `timestamp` <= \"$starttime\"
		AND `region_id` IN (SELECT `id` FROM `regions` WHERE `parent_region_id` = 24)
		GROUP BY lon,lat";

//echo $sql."<br/>";

$results = db_select($sql);

$toMakeMedian = array();
foreach($results as $result){
	if($result->region_id > 0){
		//if(!isset($toMakeMedian[$result->region_id])) $toMakeMedian[$result->region_id] = array("P1h"=>array(),"P2h"=>array(),"P1d"=>array(),"P2d"=>array());
		if(!isset($toMakeMedian[$result->region_id]["P1h"])) $toMakeMedian[$result->region_id]["P1h"] = array();
		if($result->P1h > 0) array_push($toMakeMedian[$result->region_id]["P1h"], $result->P1h);
		if(!isset($toMakeMedian[$result->region_id]["P2h"])) $toMakeMedian[$result->region_id]["P2h"] = array();
		if($result->P2h > 0) array_push($toMakeMedian[$result->region_id]["P2h"], $result->P2h);
	}
}

//print_r($toMakeMedian);

// prepare 24 h mean
$sql = "SELECT 	`sensors_hourly_mean`.`lon`,
				`sensors_hourly_mean`.`lat`,
				`region_id`,
				avg(P1) AS P1d,
				avg(P2) AS P2d
		FROM `sensors_hourly_mean`
		LEFT JOIN `x_coordinates_regions` ON `sensors_hourly_mean`.`lon` = `x_coordinates_regions`.`lon` AND `sensors_hourly_mean`.`lat` = `x_coordinates_regions`.`lat`
		WHERE `timestamp` > ('$starttime' - INTERVAL 1 DAY)
		AND `timestamp` <= \"$starttime\"
		AND `region_id` IN (SELECT `id` FROM `regions` WHERE `parent_region_id` = 24)
		GROUP BY lon,lat";

//echo $sql."<br/>";

$results = db_select($sql);

//print_r($results);

foreach($results as $result){
	if($result->region_id > 0){
		//if(!isset($toMakeMedian[$result->region_id])) $toMakeMedian[$result->region_id] = array("P1h"=>array(),"P2h"=>array(),"P1d"=>array(),"P2d"=>array());
		if(!isset($toMakeMedian[$result->region_id]["P1d"])) $toMakeMedian[$result->region_id]["P1d"] = array();
		if($result->P1d > 0) array_push($toMakeMedian[$result->region_id]["P1d"], $result->P1d);
		if(!isset($toMakeMedian[$result->region_id]["P2d"])) $toMakeMedian[$result->region_id]["P2d"] = array();
		if($result->P2d > 0) array_push($toMakeMedian[$result->region_id]["P2d"], $result->P2d);
	}
}

//print_r($toMakeMedian);


if(count($toMakeMedian)>0){
	$rawSql = array();
	foreach($toMakeMedian as $key=>&$item){
		//echo "Region $key P1h: ".print_r($item["P1h"],true)." -> ".array_median($item["P1h"])."<br/>";
		$item["P1h"] = array_median($item["P1h"]);
		$item["P2h"] = array_median($item["P2h"]);
		$item["P1d"] = array_median($item["P1d"]);
		$item["P2d"] = array_median($item["P2d"]);
		//echo 'item["P1h"] '.gettype($item["P1h"]);
		if(!is_double($item["P1h"]) && !is_string($item["P1h"])) $item["P1h"] = -1;
		if(!is_double($item["P2h"]) && !is_string($item["P2h"])) $item["P2h"] = -1;
		if(!is_double($item["P1d"]) && !is_string($item["P1d"])) $item["P1d"] = -1;
		if(!is_double($item["P2d"]) && !is_string($item["P2d"])) $item["P2d"] = -1;
		
		array_push($rawSql,"(NULL,$key,\"$starttime\",".$item["P1h"].",".$item["P2h"].",".$item["P1d"].",".$item["P2d"].")");
	}
	//array_median($array)
	//print_r($toMakeMedian);
	
	foreach($toMakeMedian as $key=>$item){
		
	}
	//print_r($rawSql);
	$sql = "INSERT INTO `regions_mean` (`id`,`region_id`,`timestamp`,`P1h`,`P2h`,`P1d`,`P2d`)
VALUES 
".join(",\n",$rawSql)." ON DUPLICATE KEY UPDATE `P1d` = Values(`P1d`), `P2d` = Values(`P2d`), `P1h` = Values(`P1h`), `P2h` = Values(`P2h`)";
	//echo $sql;
	db_insert($sql);
}
//exit;
//echo "</pre>";

$sql = "SELECT MAX(timestamp) AS timestamp FROM sensors_hourly_mean WHERE sensor_id IN (SELECT id FROM sensors WHERE type_id = 1)";
$results = db_select($sql);
$stop = $results[0]->timestamp;

if($starttime < $stop){
	if($log) file_put_contents("crawler_db_chronological_data_of_districts.log", date('Y-m-d H:i:s')."	$starttime < $stop running again.\n", FILE_APPEND | LOCK_EX);
	// cron error
	//echo "$starttime < $stop";
	if(!isset($_GET["nocron"]))
		file_get_contents($project_url."crawler_db_chronological_data_of_districts.php?starttime=".strtotime($starttime." + 1 hours").'&nocron=1');
}
else {
	//echo "$starttime >= $stop";
}
if($log) file_put_contents("crawler_db_chronological_data_of_districts.log", date('Y-m-d H:i:s')."	done.\n", FILE_APPEND | LOCK_EX);
