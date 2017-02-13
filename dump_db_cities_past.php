<pre><?php
include_once("library.php");

$city_id = 1;
$filename_cronological = $data_root."chronological_city_$city_id.tsv";
$filename_cronological_week = $data_root."chronological_city_".$city_id."_week.tsv";

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
function header_complete(){
	$data = "timestamp	num_sensors	num_values	P1high	P1low	P1mid	P1highmain	P1lowmain	P1highSensorId	P1lowSensorId	P1floating	P2high	P2low	P2mid	P2highmain	P2lowmain	P2highSensorId	P2lowSensorId	P2floating";
	return $data;
}

$data = "";

if(!file_exists($filename_cronological)){
	$data = header_complete();
	// get min dataset from db
	$sql = "SELECT MIN(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id LIMIT 1";
	$start = debug_query($sql);
	$start = strtotime($start[0]->timestamp);
}
else{
	// get max dataset
	// cast as min dataset
	$file = escapeshellarg($filename_cronological); // for the security concious (should be everyone!)
	$line = `tail -n 1 $file`;
	echo $line."\n";
	$line = explode("	",$line);
	$start = $line[0];
	echo $start."\n";
	$start = substr($start,0,4)."-".substr($start,4,2)."-".substr($start,6,2)." ".substr($start,8,2).":".substr($start,10,2).":".substr($start,12,2);
	echo $start."\n";
	$start = strtotime($start." + 1 hours");
	echo $start."\n";
}

$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id";
$stop = debug_query($sql);
$stop = $stop[0]->timestamp;
$stop = strtotime($stop);

$timestamp = $start;

$max = 100;
$j = 0;
while($timestamp <= $stop){
	$data.="\n".date("YmdHis",$timestamp);
	
	$sql = "SELECT * 
		FROM `cities_mean` 
		WHERE `timestamp` = '".date("Y-m-d H:i:s",$timestamp)."' 
		AND `city_id` = $city_id";
	$results = debug_query($sql);
	$data.= "	".$results[0]->num_sensors."	".$results[0]->num_values;
	$data.= "	".$results[0]->P1h_max."	".$results[0]->P1h_min."	".$results[0]->P1h."	".$results[0]->P1h_50_max."	".$results[0]->P1h_50_min."	".get_sensor_name_by_sensor_id($results[0]->P1max_sensor_id)."	".get_sensor_name_by_sensor_id($results[0]->P1min_sensor_id)."	".$results[0]->P1d;
	$data.= "	".$results[0]->P2h_max."	".$results[0]->P2h_min."	".$results[0]->P2h."	".$results[0]->P2h_50_max."	".$results[0]->P2h_50_min."	".get_sensor_name_by_sensor_id($results[0]->P2max_sensor_id)."	".get_sensor_name_by_sensor_id($results[0]->P2min_sensor_id)."	".$results[0]->P2d;

	$timestamp = strtotime(date("Y-m-d H:i:s",$timestamp)." + 1 hours");
	$j++;
	if($j>$max) break;
}

echo $data;

$result = file_put_contents($filename_cronological, $data, FILE_APPEND | LOCK_EX);

print_r($result);

// a smaller file
$data = file_get_contents($filename_cronological);
$data = explode("\n",$data);
$header = $data[0];
$start_count = count($data);
while(count($data)>168){
	array_shift($data);
}
$data = string implode ("\n",$data);
if($start_count>168){
    $data = $header."\n".$data;
}
file_put_contents($filename_cronological_week,$data);

?>
</pre>
