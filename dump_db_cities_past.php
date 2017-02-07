<pre><?php
include_once("library.php");

$city_id = 1;
$filename_cronological = $data_root."chronological_city_$city_id.tsv";

$districts = db_select("SELECT * FROM `districts`");

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
function header_complete($districts){
	$data = "timestamp	num_sensors	num_values	P1high	P1low	P1mid	P1highmain	P1lowmain	P1highSensorId	P1lowSensorId	P1floating	P2high	P2low	P2mid	P2highmain	P2lowmain	P2highSensorId	P2lowSensorId	P2floating";
	return $data;
}

$data = "";

if(!file_exists($filename_cronological)){
	$data_complete = header_complete();
	// get min dataset from db
	$sql = "SELECT MIN(`timestamp`) FROM `cities_mean` WHERE `city_id` = $city_id LIMIT 1";
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

$sql = "SELECT MAX(`timestamp`) FROM `cities_mean` WHERE `city_id` = $city_id";
$stop = debug_query($sql);
$stop = $stop[0]->timestamp;
$stop = strtotime($stop);

$timestamp = $start;

$max = 100;
$j = 0;
while($timestamp <= $stop){
	$data.="\n".date("YmdHis",$timestamp);
	
	$sql = "SELECT * FROM `cities_mean` WHERE `timestamp` = '".date("Y-m-d H:i:s",$timestamp)."' AND `city_id` = $city_id";
	$results = debug_query($sql);

	
	$timestamp = strtotime(date("Y-m-d H:i:s",$timestamp)." + 1 hours");
	$j++;
	if($j>$max) break;
}

echo $data;
	
//file_put_contents($filename_cronological, $data, FILE_APPEND | LOCK_EX);




?>
</pre>
