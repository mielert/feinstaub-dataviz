<?php
if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; 
include_once("library.php");

$filename_cronological_complete = $data_root."chronological_districts_v2_complete.tsv";
$filename_cronological_simple = $data_root."chronological_districts_v2_simple.tsv";
$filename_cronological_simple_week = $data_root."chronological_districts_v2_simple_week.tsv";

$districts = db_select("SELECT * FROM `districts`");

/**
 *
 */
function cleanup_string($string){
	//$string = str_replace(array(" ","ä","ö","ü","ß"),array("_","ae","oe","ue","sz"),$string);
	$string = str_replace(array(" "),array("_"),$string);
	return $string;
}
/**
 *
 */
function header_complete($districts){
	$data = "timestamp";
	foreach($districts as $dataset){
		$data.="	P1h_".cleanup_string($dataset->name)."	P1floating_".cleanup_string($dataset->name)."	P2h_".cleanup_string($dataset->name)."	P2floating_".cleanup_string($dataset->name);
	}
	return $data;
}
/**
 *
 */
function header_simple($districts){
	$data = "timestamp";
	foreach($districts as $dataset){
		$data.="	P1floating_".cleanup_string($dataset->name);
	}
	return $data;
}

$data_complete = "";
$data_simple = "";
$data_simple_week = "";

if(!file_exists($filename_cronological_complete)){
	$data_complete = header_complete($districts);
	// get min dataset from db
	$sql = "SELECT * FROM `regions_mean` ORDER BY `regions_mean`.`timestamp` ASC LIMIT 1";
	$start = db_select($sql);
	$start = strtotime($start[0]->timestamp);
}
else{
	// get max dataset
	// cast as min dataset
	$file = escapeshellarg($filename_cronological_complete); // for the security concious (should be everyone!)
	$line = `tail -n 1 $file`;
	//echo $line."\n";
	$line = explode("	",$line);
	$start = $line[0];
	//echo $start."\n";
	$start = substr($start,0,4)."-".substr($start,4,2)."-".substr($start,6,2)." ".substr($start,8,2).":".substr($start,10,2).":".substr($start,12,2);
	//echo $start."\n";
	$start = strtotime($start." + 1 hours");
	//echo $start."\n";
}

if(!file_exists($filename_cronological_simple))
	$data_simple = header_simple($districts);
if(!file_exists($filename_cronological_simple_week))
	$data_simple_week = header_simple($districts);


$sql = "SELECT * FROM `regions_mean` ORDER BY `regions_mean`.`timestamp` DESC LIMIT 1";
$stop = db_select($sql);
$stop = $stop[0]->timestamp;
$stop = strtotime($stop);

$timestamp = $start;

$max = 100;
$j = 0;
while($timestamp <= $stop){
	$data_complete.="\n".date("YmdHis",$timestamp);
	$data_simple.="\n".date("YmdHis",$timestamp);
	
	$sql = "SELECT * FROM `regions_mean` WHERE `timestamp` = '".date("Y-m-d H:i:s",$timestamp)."' ORDER BY `region_id`";
	$results = db_select($sql);
	$values = array();
	foreach($results as $result){
		$values[$result->region_id] = array("P1h"=>$result->P1h,"P1d"=>$result->P1d,"P2h"=>$result->P2h,"P2d"=>$result->P2d);
	}
	for($i=1;$i<=23;$i++){
		if(isset($values[$i])){
			$data_complete.= "	".$values[$i]["P1h"]."	".$values[$i]["P1d"]."	".$values[$i]["P2h"]."	".$values[$i]["P2d"];
			$data_simple.= "	".$values[$i]["P1d"];
		}
		else{
			$data_complete.= "	-1	-1	-1	-1";
			$data_simple.= "	-1";
		}
	}
	
	$last_timestamp = date("Y-m-d H:i:s",$timestamp);
	$timestamp = strtotime(date("Y-m-d H:i:s",$timestamp)." + 1 hours");
	$j++;
	if($j>$max) break;
}

//echo $data_simple;
if($last_timestamp)
	echo "dumped until $last_timestamp";
else
	echo "nothing to dump";
	
file_put_contents($filename_cronological_complete, $data_complete, FILE_APPEND | LOCK_EX);
//file_put_contents($filename_cronological_complete, $data_complete);
file_put_contents($filename_cronological_simple, $data_simple, FILE_APPEND | LOCK_EX);

// week data
$start = strtotime(date("Y-m-d H:i:s",$stop)." - 168 hours");
$timestamp = $start;
$data_simple_week = header_simple($districts);
while($timestamp <= $stop){
	$data_simple_week.="\n".date("YmdHis",$timestamp);
	
	$sql = "SELECT * FROM `regions_mean` WHERE `timestamp` = '".date("Y-m-d H:i:s",$timestamp)."' ORDER BY `region_id`";
	$results = db_select($sql);
	$values = array();
	foreach($results as $result){
		$values[$result->region_id] = array("P1h"=>$result->P1h,"P1d"=>$result->P1d,"P2h"=>$result->P2h,"P2d"=>$result->P2d);
	}
	for($i=1;$i<=23;$i++){
		if(isset($values[$i])){
			$data_simple_week.= "	".$values[$i]["P1d"];
		}
		else{
			$data_simple_week.= "	-1";
		}
	}
	
	$timestamp = strtotime(date("Y-m-d H:i:s",$timestamp)." + 1 hours");
}

file_put_contents($filename_cronological_simple_week, $data_simple_week);




?>
