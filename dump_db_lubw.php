<?php if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; 
include_once("library.php");

$sensor_type_id = 2;
$filename_cronological_complete = $data_root."chronological_data_lubw.tsv";

$sensors = db_select("SELECT * FROM `sensors` WHERE `sensors`.`type_id` = $sensor_type_id ORDER BY `name`");

/**
 *
 */
function cleanup_string($string){
	$string = str_replace(array(" ","ä","ö","ü","ß"),array("_","ae","oe","ue","sz"),$string);
	return $string;
}
/**
 *
 */
function header_complete($sensors){
	$data = "timestamp	";
	foreach($sensors as $dataset){
		$data.=$dataset->name."pm10	";
	}
	return $data;
}

$data_complete = "";

if(!file_exists($filename_cronological_complete)){
	$data_complete = header_complete($sensors);
	// get min dataset from db
	$sql = "SELECT * FROM `sensors_hourly_mean`
			LEFT JOIN `sensors` ON `sensors`.`id` = `sensors_hourly_mean`.`sensor_id`
			WHERE `sensors`.`type_id` = $sensor_type_id
			ORDER BY `sensors_hourly_mean`.`timestamp` ASC
			LIMIT 1";
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

$sql = "SELECT * FROM `sensors_hourly_mean`
		LEFT JOIN `sensors` ON `sensors`.`id` = `sensors_hourly_mean`.`sensor_id`
		WHERE `sensors`.`type_id` = 2
		ORDER BY `sensors_hourly_mean`.`timestamp` DESC
		LIMIT 1";
$stop = db_select($sql);
$stop = $stop[0]->timestamp;
$stop = strtotime($stop);

$timestamp = $start;

//echo "start: $start ";
//echo "stop: $stop ";

$max = 100;
$j = 0;
while($timestamp <= $stop){
	$data_complete.="\n".date("YmdHis",$timestamp);
	
	$sql = "SELECT *
			FROM `sensors_hourly_mean`
			LEFT JOIN `sensors` ON `sensors`.`id` = `sensors_hourly_mean`.`sensor_id`
			WHERE `sensors`.`type_id` = 2
			AND `timestamp` = '".date("Y-m-d H:i:s",$timestamp)."'
			ORDER BY `sensor_id`";
	$results = db_select($sql);
	$values = array();
	foreach($results as $result){
		$values[$result->name] = $result->P1d;
	}
	foreach($sensors as $sensor){
		if(isset($values[$sensor->name])){
			$data_complete.= "	".$values[$sensor->name];
		}
		else{
			$data_complete.= "	";
		}
	}
	$last_timestamp = date("Y-m-d H:i:s",$timestamp);

	$timestamp = strtotime(date("Y-m-d H:i:s",$timestamp)." + 1 hours");
	$j++;
	if($j>$max) break;
}

//echo $data_complete;


file_put_contents($filename_cronological_complete, $data_complete, FILE_APPEND | LOCK_EX);
if($last_timestamp)
	echo "dumped until $last_timestamp";
else
	echo "nothing to dump";

//file_put_contents($filename_cronological_complete, $data_complete);

?>
