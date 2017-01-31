<?php
$result = include_once("config.php");
if(!$result){echo "<h1>missing config.php</h1><pre>".sample_config_file()."</pre>"; exit;}
if(!test_sql()){echo "<h1>can't connect to database</h1>Please check config.php"; exit;}
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
	$result = $mysqli->query($sql);
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
	echo "<div class='debug_query'>$sql</div>";
	if(strpos($sql,"SELECT")===0)
		$result = db_select($sql);
	else
		$result = db_insert($sql);
	echo "<div class='debug_query'><pre>".print_r($result,true)."</pre></div>";
	echo "</div>";
	return $result;
}
