<?php
include_once("library.php");

//$vars = $_SERVER['REQUEST_URI'];

//echo "$vars\n";

$sensor_name = intval($_GET["sensor_name"]);

if($sensor_name===0) {
	echo "invalid sensor name (sensor_name)";
	exit;
}

$sensor_id = get_sensor_id_by_sensor_name($sensor_name,1,false);

//echo "name: ".$_GET["sensor_name"]." -> id: ".$sensor_id."\n";

if($sensor_id===false) {
	echo "invalid sensor name (sensor_name)\n";
	exit;
}

$filename_cronological = $data_root."sensors/sensor_$sensor_name.tsv";

$data_complete = "";

$sql = "SELECT *
		FROM `sensors_hourly_mean`
		WHERE `sensor_id` = $sensor_id\n";

if(!file_exists($filename_cronological)){
	$data_complete = "timestamp	P10_60min_mean	P10_24h_mean	P2.5_60min_mean	P2.5_24h_mean";
}
else{
	// get max dataset
	// cast as min dataset
	$file = escapeshellarg($filename_cronological); // for the security concious (should be everyone!)
	$line = `tail -n 1 $file`;
	//echo $line."\n";
	$line = explode("	",$line);
	$start = $line[0];
	//echo $start."\n";
	$start = substr($start,0,4)."-".substr($start,4,2)."-".substr($start,6,2)." ".substr($start,8,2).":".substr($start,10,2).":".substr($start,12,2);
	$start = strtotime($start);
	//echo $start."\n";
	$sql.= "AND timestamp > '".date("Y-m-d H:i:s",$start)."'\n";
}

$sql.= "ORDER BY `timestamp`";

$results = db_select($sql);

foreach($results as $result){
	$data_complete.= "\n".str_replace(array(" ",":","-"),"",$result->timestamp)."	".$result->P1."	".$result->P1d."	".$result->P2."	".$result->P2d;
}
//echo $data_complete;

file_put_contents($filename_cronological, $data_complete, FILE_APPEND | LOCK_EX);

$data = file_get_contents($filename_cronological);

echo $data;
?>
