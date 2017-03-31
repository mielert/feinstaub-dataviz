<pre><?php
/**
 * crawler for archive.luftdaten.info
 */

include_once("library.php");

// start: 31.8.2016
$day = "2017-03-28";
$startdate = strtotime("$day 00:00:00");
//$sql = "SELECT timestamp FROM `sensor_data` ORDER BY `timestamp` DESC LIMIT 1";
//$result = db_select($sql);
//$startdate = strtotime($result[0]->timestamp." +1 seconds");
echo "start: ".date("Y-m-d H:i:s", $startdate)."<br/>";


// end: 17.12.2016
$stopdate = strtotime("$day 23:59:59");
//$stopdate = strtotime(date("Y-m-d 23:59:59")." -2 days");
echo "stop: ".date("Y-m-d H:i:s", $stopdate)."<br/>";

// delete existing data
$sql = "DELETE FROM `sensor_data` WHERE `timestamp` > '$startdate' AND `timestamp` <= '$stopdate';";
db_insert($sql);

/* Step 1
 * look for directory
 */
$base = $data_root."archive/";

$date = $startdate;
$stuttgart = get_city("stuttgart");

while($date < $stopdate){
	echo "crawling: ".date('Y-m-d H:i:s', $date)."<br/>";
	$dir = date('Y-m-d', $date)."/";
	$url = $base.$dir;
	echo "opening $url<br/>";
	$list_of_files = read_dir_local($url);
	$sds011files = filter_sds011($list_of_files);
	print_r($sds011files);
	foreach($sds011files as $filename){
		$sensor_data = read_sensor($base.$dir.$filename);
		//print_r($sensor_data);
		single_sensor_to_database($sensor_data);
	}
	//$date = $date + 24*60*60;
	$date = strtotime(date('Y-m-d H:i:s', $date)." + 1 day");
}

//print_r($list_of_files);
/* Step 2
 * look for sds files
 * 2016-11-03_sds011_sensor_140.csv
 */
//$sds011files = filter_sds011($list_of_files);
//print_r($list_of_files);
$sensor_data = read_sensors($sds011files,$stuttgart);
//print_r($sensor_data);
/* Step 3
 * read data
 * check location
 * calculate arithmentic mean
 * add to array
 * save
 */


/**
 * @param 
 * @return array List of sensors 
 */
function read_dir($base){
	$filename = $base;
	$result = file_get_contents($filename);
	$result = substr($result,strpos($result,"<pre>"));
	$result = substr($result,0,strpos($result,"</pre>"));
	$result = explode("\n",$result);
	$filtered_files = array();
	foreach($result as $row){
		$row = substr($row,strpos($row,"<a href=\""));
		$row = substr($row,9,1000);
		$row = substr($row,0,strpos($row,"\">"));
		if(strpos($row,".csv")!==false){
			array_push($filtered_files,$base.$row);
		}
	}
	return $filtered_files;
}
/**
 * @param 
 * @return array List of sensors 
 */
function read_dir_local($base){
	$list = scandir($base);
	$filtered_files = array();
	foreach($list as $item){
		//echo $item;
		if(strlen($item) > 5){
			array_push($filtered_files,$item);
			//echo "push ";
		}
	}
	return $filtered_files;
}

/**
 * @param array $list_of_files
 * @return array Filtered list
 */
function filter_sds011($list_of_files){
	$filtered_files = array();
	foreach($list_of_files as $filename){
		if(strpos($filename,"sds011_sensor")!==false)
			array_push($filtered_files,$filename);
	}
	return $filtered_files;
}
/**
 * @param array $list_of_files
 */
function read_sensors($list_of_files,$city){
	$result = array();
	foreach($list_of_files as $filename){
		$result_single_sensor = read_sensor($filename,$city);
		single_sensor_to_database($result_single_sensor);
		//break;
	}
	return $result;
}
/**
 *
 */
function single_sensor_to_database($single_sensor_data){
	//print_r($single_sensor_data);
	if(count($single_sensor_data)>0){
		$sensor_id = get_sensor_id_by_sensor_name($single_sensor_data[0]["sensor_id"],$single_sensor_data[0]["sensor_type"]);
		$sql = array();
		foreach($single_sensor_data as $dataset){
			$timestamp = strtotime(str_replace("+00:00","+01:00",$dataset["timestamp"]));
			array_push($sql,"(NULL, '".$sensor_id."', '".$dataset["lon"]."', '".$dataset["lat"]."', '".date("Y-m-d H:i:s",$timestamp)."', '".$dataset["P1"]."', '".$dataset["P2"]."')");
		}
		$sql = join(",",$sql);
		$sql = "INSERT INTO `sensor_data` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`) VALUES ".$sql." ON DUPLICATE KEY UPDATE `P1` = Values(`P1`), `P2` = Values(`P2`)";
		echo $sql."<br/>";
		debug_query($sql);
	}
	
}
/**
 * @param string $filename
 * @param array $city Geodata of districts
 *  /var/www/vhosts/fritzmielert.de/httpdocs/feinstaub/archive/2016-08-31/2016-08-31_sds011_sensor_140.csv
    /var/www/vhosts/fritzmielert.de/httpdocs/feinstaub/archive/2016-08-31_sds011_sensor_140.csv

 */
function read_sensor($filename,$city=false){
	//echo "reading $filename<br/>";
	if(file_exists($filename)){
		$result = file_get_contents($filename);
		//echo $result;
		$result = str_replace(";",",",$result);
		$result = explode("\n",$result);
		//print_r($result);
		array_pop($result);
		$array = array_map('str_getcsv', $result);
		$header = array_shift($array);
		array_walk($array, '_combine_array', $header);
		return $array;
	}
}
/**
 *
 */
function _combine_array(&$row, $key, $header) {
	$row = array_combine($header, $row);
}


?>
</pre>
