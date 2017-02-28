<?php
$result = include_once("config.php");
if(!$result){echo "<h1>missing config.php</h1><pre>".sample_config_file()."</pre>"; exit;}
//if(!test_sql()){echo "<h1>can't connect to database</h1>Please check config.php"; exit;}
test_directories();
include_once("classes/point-in-polygon.php");

/**
 *
 */
function test_directories(){
	global $data_root;
	create_directory($data_root);
}
/**
 *
 */
function create_directory($dir){
	if (!file_exists($dir)) {
		mkdir($dir, 0755, true);
	}
}
/**
 *
 */
function test_sql(){
	$mysqli = db_connect();
	$result = $mysqli->query();
	$mysqli->close();
	return true;
}
/**
 *
 */
function sample_config_file(){
	$result = htmlentities('
<?php
// paths
$root = "/var/www/vhosts/your-url.com/httpdocs/your-dir/";
$data_dir = "data/";
$data_root = $root.$data_dir;
$url = "https://your-url.com/your-dir/";
$data_url = $url.$data_dir;

// time zone
date_default_timezone_set("Europe/Berlin");

// mysql access data
$mysql_user = "db_user";
$mysql_pwd = "db_passwd";
$mysql_db = "db_name";
$mysql_host = "localhost";

?>
');
	return $result;
}
/**
 * @param array one-dimensional array containing floats
 * @return float median
 * @copyright cc by-sa 3.0 Mchl/stackexchange 
 * @see http://codereview.stackexchange.com/questions/220/calculate-a-median
 */
function array_median($array) {
    // perhaps all non numeric values should filtered out of $array here?
    $iCount = count($array);
    if ($iCount == 0) {
        //throw new DomainException('Median of an empty array is undefined');
        return false;
    }
    elseif($iCount == 1){
        return $array[0];
    }
    elseif($iCount == 2){
        return ($array[0]+$array[1])/2;
    }
    else{
        // if we're down here it must mean $array
        // has at least 1 item in the array.
        $middle_index = floor($iCount / 2);
        sort($array, SORT_NUMERIC);
        $median = $array[$middle_index]; // assume an odd # of items
        // Handle the even case by averaging the middle 2 items
        if ($iCount % 2 == 0) {
            $median = ($median + $array[$middle_index - 1]) / 2;
        }
    }
    return $median;
}
/**
 * @param array $array one-dimensional array containing floats
 * @return float arithmetic mean
 */
function array_arithmetic_mean($array){
    $iCount = count($array);
    if ($iCount == 0) {
        return false;
    }
    $sum = 0;
    foreach($array as $value){
        $sum+= $value;
    }
    return $sum/$iCount;
}
/**
 * @param array $array one-dimensional array containing floats
 * @return array min & max values
 */
function array_main_sector($array) {
    // perhaps all non numeric values should filtered out of $array here?
    $iCount = count($array);
    if ($iCount == 0) {
        throw new DomainException('Median of an empty array is undefined');
    }
    // if we're down here it must mean $array
    // has at least 1 item in the array.
    sort($array, SORT_NUMERIC);
    $max_index = floor($iCount/4);
    $min_index = floor($iCount/4*3);
    $max = $array[$max_index]; // assume an odd # of items
    // Handle the even case by averaging the middle 2 items
    if ($iCount % 2 == 0) {
        $max = ($max + $array[$max_index - 1]) / 2;
    }
    $min = $array[$min_index]; // assume an odd # of items
    // Handle the even case by averaging the middle 2 items
    if ($iCount % 2 == 0) {
        $min = ($min + $array[$min_index - 1]) / 2;
    }
    return array("min"=>$min,"max"=>$max);
}
/**
 * @param array $coordinates [[x1,y1],[x2,y2]]
 * @return array ["x1 y1","x2 y2"]
 */
function simplify_polygon($coordinates){
    $newPolygon = array();
    foreach($coordinates as $point){
        //echo "<br/>".$point[0]." ".$point[1]."<br/>";
        array_push($newPolygon, $point[0]." ".$point[1]);
    }
    return $newPolygon;
}
/**
 * @param string $city
 * @return array Coordinates of all districts of a city
 */
function get_city($city){
	global $data_root;
	$cityphp = $data_root.strtolower($city).".php";
	//echo $cityphp;
	require_once($cityphp);
	return get_city_geodata();
}
/**
 * @param float $lon
 * @param float $lat
 * @param string $city
 * @return string or boolean Name of the district
 */
function point_in_city($lon,$lat,$city){
	return point_in_districts($lon,$lat,get_city($city));
}
/**
 * @param float $lon
 * @param float $lat
 * @param array $districts
 */
function point_in_districts($lon,$lat,$districts){
	foreach($districts as $polygon){
		if(!isset($polygon["name"])) $polygon["name"] = "unknown polygon";
        //echo $polygon["name"];
		$result = point_in_poylgon($lon,$lat, $polygon["coordinates"]);
		if($result) return $polygon["name"];
	}
	return false;
}
/**
 * @param float $lon
 * @param float $lat
 * @param array $polygon
 */
function point_in_poylgon($lon,$lat,$polygon){
	$pointLocation = new pointLocation();
    $point = "$lon $lat";
    //echo "<pre>".print_r($polygon,true)."</pre>";
    $simplifiedPolygon = simplify_polygon($polygon);
    //echo "<br/>$point<br/><pre>".print_r($simplifiedPolygon,true)."</pre>";
	$result = $pointLocation->pointInPolygon($point, $simplifiedPolygon);
    //echo "result: $result<br/>";
	if($result != "outside") return true;
	return false;
}

/**
 * @param string $sql
 * @return array Result of the query
 */
function db_select($sql){
	$mysqli = db_connect();
	$mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
    $rows = Array();
    if ($result = $mysqli->query($sql)) {
		while ($row = $result->fetch_object()) {
			array_push($rows, $row);
		}
    }
    $mysqli->close();
    return $rows;
}
/**
 * @param string $sql
 * @return bool Result of the query
 */
function db_insert($sql){
	$mysqli = db_connect();
    $result = $mysqli->query($sql);
    $mysqli->close();
    return $result;
}
/**
 * @return object mysqli-object
 */
function db_connect(){
	global $mysql_host, $mysql_user, $mysql_pwd, $mysql_db;
    $mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pwd, $mysql_db);
    if ($mysqli->connect_error){
		die('Could not connect: ' .$mysqli->connect_error);
    }
    $mysqli->query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	return $mysqli;
}
/**
 * @param string $sql
 * @return bool Result of the query
 */
function debug_query($sql){
	echo "<div class='debug_query_div'>";
	echo "<div class='debug_query'>Query:<pre>$sql</pre></div>";
	if(strpos($sql,"SELECT")===0)
		$result = db_select($sql);
	else
		$result = db_insert($sql);
	echo "<div class='debug_query'><pre>Result: ".print_r($result,true)."</pre></div>";
	echo "</div>";
	return $result;
}
/**
 * @param string $sensor_name Name of the sensor
 * @param int $type_id Sensor type id
 * @return int Id of the given sensor
 */
function get_sensor_id_by_sensor_name($sensor_name,$type_id=1){
    $sql = "SELECT * FROM `sensors` WHERE `name` = '".$sensor_name."' LIMIT 1";
    $result = db_select($sql);
    if(isset($result[0]->name) && $result[0]->name == $sensor_name)
        return $result[0]->id;
    else{
        $sql = "INSERT INTO `sensors` (`id`,`name`,`type_id`) VALUES (NULL, '$sensor_name',$type_id)";
        $result = db_insert($sql);
        $sql = "SELECT * FROM `sensors` WHERE `name` = '".$sensor_name."' LIMIT 1";
        $result = db_select($sql);
        return $result[0]->id;
    }
}
/**
 * @param int $sensor_id
 * @return string or bool Name of the sensor or false
 */
function get_sensor_name_by_sensor_id($sensor_id){
    $sql = "SELECT * FROM `sensors` WHERE `id` = ".floatval($sensor_id)." LIMIT 1";
    $result = db_select($sql);
    if(isset($result[0]->id) && $result[0]->id == $sensor_id)
        return $result[0]->name;
    else{
        return false;
    }
}
/**
 * @param array $dataset array("sensor_name"=>"","sensor_type"=>0,"lon"=>0,"lat"=>0,"timestamp"=>"","P10"=>0,"P25"=>0)
 */
function save_sensor_data_to_database($dataset){
    $sensor_id = get_sensor_id_by_sensor_name($dataset["sensor_name"],$dataset["sensor_type"]);
    $sql = "INSERT INTO `sensor_data` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`)
			VALUES (NULL,".$sensor_id.",".$dataset["lon"].",".$dataset["lat"].",'".date('Y-m-d H:i:s', $dataset["timestamp"])."',".$dataset["P10"].",".$dataset["P25"].")
			ON DUPLICATE KEY UPDATE `P1` = VALUES(`P1`), `P2` = VALUES(`P2`); ";
    $result = debug_query($sql);
}
/**
 * @param array $dataset array("sensor_name"=>"","sensor_type"=>0,"lon"=>0,"lat"=>0,"timestamp"=>"","P10"=>0,"P25"=>0)
 */
function save_sensor_data_to_database_daily_mean($dataset){
    $sensor_id = get_sensor_id_by_sensor_name($dataset["sensor_name"],$dataset["sensor_type"]);
    $sql = "INSERT INTO `sensors_hourly_mean` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1d`, `P2d`)
			VALUES (NULL,".$sensor_id.",".$dataset["lon"].",".$dataset["lat"].",'".date('Y-m-d H:i:s', $dataset["timestamp"])."',".$dataset["P10"].",".$dataset["P25"].")
			ON DUPLICATE KEY UPDATE `P1d` = VALUES(`P1d`), `P2d` = VALUES(`P2d`); ";
    $result = debug_query($sql);
}
