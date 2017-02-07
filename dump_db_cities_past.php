<pre><?php
include_once("library.php");

$city_id = 1;
$filename_cronological_simple = $data_root."chronological_city_$city_id.tsv";

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

if(!file_exists($filename_cronological_complete)){
	$data_complete = header_complete($districts);
	// get min dataset from db
	$sql = "SELECT * FROM `districts_mean` ORDER BY `districts_mean`.`timestamp` ASC LIMIT 1";
	$start = db_select($sql);
	$start = strtotime($start[0]->timestamp);
}
else{
	// get max dataset
	// cast as min dataset
	$file = escapeshellarg($filename_cronological_complete); // for the security concious (should be everyone!)
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

if(!file_exists($filename_cronological_simple))
	$data_simple = header_simple($districts);
else{

}

$sql = "SELECT * FROM `districts_mean` ORDER BY `districts_mean`.`timestamp` DESC LIMIT 1";
$stop = db_select($sql);
$stop = $stop[0]->timestamp;
$stop = strtotime($stop);

$timestamp = $start;

$max = 100;
$j = 0;
while($timestamp <= $stop){
	$data_complete.="\n".date("YmdHis",$timestamp);
	$data_simple.="\n".date("YmdHis",$timestamp);
	
	$sql = "SELECT * FROM `districts_mean` WHERE `timestamp` = '".date("Y-m-d H:i:s",$timestamp)."' ORDER BY `district_id`";
	$results = db_select($sql);
	$values = array();
	foreach($results as $result){
		$values[$result->district_id] = array("P1h"=>$result->P1h,"P1d"=>$result->P1d,"P2h"=>$result->P2h,"P2d"=>$result->P2d);
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
	
	$timestamp = strtotime(date("Y-m-d H:i:s",$timestamp)." + 1 hours");
	$j++;
	if($j>$max) break;
}

echo $data_simple;
	
file_put_contents($filename_cronological_complete, $data_complete, FILE_APPEND | LOCK_EX);
//file_put_contents($filename_cronological_complete, $data_complete);
file_put_contents($filename_cronological_simple, $data_simple, FILE_APPEND | LOCK_EX);




?>
</pre>
