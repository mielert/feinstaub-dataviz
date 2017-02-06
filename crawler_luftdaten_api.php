<?php
include("library.php");

$data_file = $data_root."data.json";
$simple_file = $data_root."data.tsv";
//$dump_file = $data_root."dump.json";

$test_mode = false;

if(!file_exists($data_file)){
    file_put_contents($data_file,json_encode(array()));
}

if(filemtime($data_file)>time()-60 && !$test_mode) exit; // 1 minute

$fileContent = file_get_contents($data_file);
$cronological_data = json_decode($fileContent,true);
//echo "<pre>".print_r($fileContent,true)."</pre>";

/**
 *
 */
function read_data(){
    // Setup cURL
    /*
     9.1.2017 API-Changes
HTTP 200 OK
Allow: GET, HEAD, OPTIONS
Content-Type: application/json
Vary: Accept

{
    "push-sensor-data": "https://api.luftdaten.info/v1/push-sensor-data/",
    "node": "https://api.luftdaten.info/v1/node/",
    "sensor": "https://api.luftdaten.info/v1/sensor/",
    "data": "https://api.luftdaten.info/v1/lasthour/",
    "statistics": "https://api.luftdaten.info/v1/statistics/",
    "user": "https://api.luftdaten.info/v1/user/",
    "now": "https://api.luftdaten.info/v1/lasthour/",
    "lasthour": "https://api.luftdaten.info/v1/lasthour/"
}

https://api.luftdaten.info/v1/now/
https://api.madavi.de/v1/now/?format=json
    */
    $url = "https://api.luftdaten.info/v1/now/?format=json";
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => FALSE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        )
    ));
    
    // Send the request
    $response = curl_exec($ch);
 
    //echo $response;
    // Check for errors
    if($response === FALSE){
        mail("fritz.mielert@gmx.de","Collector Error","Curl Error: ".curl_error($ch));
        die(curl_error($ch));
        return false;
    }
    else{
        if(strpos($response,"nginx")!==false){
            $response = substr($response,strpos($response,"<title>")+7);
            $response = substr($response,0,strpos($response,"</title>"));
            echo "<h1>Error: $response</h1>";
            echo "Couldn't fetch $url";
            mail("fritz.mielert@gmx.de","Collector Error: $response",$response);
            exit;
        }
        else{
            // Decode the response
            $responseData = json_decode($response, TRUE); 
            return $responseData;
        }
    }
}
/**
 *
 */
function remove_sensors_not_sds($data){
    $data_sds = array();
    foreach($data as $dataset){
        if(substr($dataset["sensor"]["sensor_type"]["name"],0,3)=="SDS"){
            array_push($data_sds,$dataset);
        }
    }
    return $data_sds;
}
/**
 *
 */
function remove_sensors_with_wrong_geocode($data){
    $data_geocode = array();
    $stuttgart = get_city("stuttgart");
    foreach($data as $dataset){
        $result = point_in_districts($dataset["location"]["longitude"],$dataset["location"]["latitude"],$stuttgart);
        if($result!==false){
            $dataset["district"] = $result;
            array_push($data_geocode,$dataset);
        }
    }
    return $data_geocode;
}
/**
 *
 */
function distinct_sensors($data){
    $ids = array();
    foreach ($data as $dataset){
        //echo $dataset["sensor"]["id"]."<br/>";
        if(!isset($ids[$dataset["sensor"]["id"]])){
            $ids[$dataset["sensor"]["id"]]=array("P1"=>array(),"P2"=>array(),"latitude"=>$dataset["location"]["latitude"],"longitude"=>$dataset["location"]["longitude"],"district"=>$dataset["district"],"timestamp"=>array());
        }
        array_push($ids[$dataset["sensor"]["id"]]["timestamp"],strtotime($dataset["timestamp"]));
        foreach($dataset["sensordatavalues"] as $values){
            if($values["value_type"] == "P1"){
                array_push($ids[$dataset["sensor"]["id"]]["P1"],floatval($values["value"]));
            }
            if($values["value_type"] == "P2"){
                array_push($ids[$dataset["sensor"]["id"]]["P2"],floatval($values["value"]));
            }
        }
    }
    foreach($ids as &$item){
        $item["timestamp"] = array_arithmetic_mean($item["timestamp"]);
        $item["P1"] = array_arithmetic_mean($item["P1"]);
        $item["P2"] = array_arithmetic_mean($item["P2"]);
    }
    //echo "<pre>".print_r($ids,true)."</pre>";
    //exit;
    $data = array();
    foreach($ids as $key => &$item){
        array_push($data,array(
                               "timestamp"=>$item["timestamp"],
                               "sensordatavalues"=>array(
                                                         array(
                                                               "value" => $item["P2"],
                                                               "value_type" => "P2"
                                                               ),
                                                         array(
                                                               "value" => $item["P1"],
                                                               "value_type" => "P1"
                                                               )
                                                         ),
                               "location"=>array(
                                                "latitude" => $item["latitude"],
                                                "longitude" => $item["longitude"],
                                                "district" => $item["district"]
                                                ),
                               "sensor"=>array(
                                                "id" => $key
                                                )
                               )
                   
                   );
    }
    return $data;
}
/**
 *
 */
function cleanup_data($data){
    $data = remove_sensors_with_wrong_geocode($data);
    $num_values = count($data);
    $data = distinct_sensors($data);
    return array("data"=>$data,"num_values"=>$num_values);
}
/**
 *
 */
function get_min_max_mid($data){
    //echo "get_min_max_mid";
    $statistics = array("timestamp"=>0,
                        "num_sensors"=>0,
                        "num_values"=>0,
                        "P1"=>array("min"=>1000000,
                                    "max"=>0,
                                    "mid"=>0,
                                    "231"=>false,
                                    "217"=>false,
                                    "50"=>false,
                                    "max_main"=>"",
                                    "min_main"=>"",
                                    "max_sensor_id"=>"",
                                    "min_sensor_id"=>""
                                    ),
                        "P2"=>array("min"=>1000000,
                                    "max"=>0,
                                    "mid"=>0,
                                    "231"=>false,
                                    "217"=>false,
                                    "50"=>false,
                                    "max_main"=>"",
                                    "min_main"=>"",
                                    "max_sensor_id"=>"",
                                    "min_sensor_id"=>""
                                    )
                        );
    $p1 = array();
    $p2 = array();
    $timestamps = array();
    foreach($data as $dataset){
        array_push($timestamps,$dataset["timestamp"]);
        //echo $dataset["sensor"]["id"]."<br/>";
        // echo "<pre>".print_r($dataset,true)."</pre>";
        if(intval($dataset["sensor"]["id"]) == 231) $s_231 = true; else $s_231 = false;
        if(intval($dataset["sensor"]["id"]) == 217) $s_217 = true; else $s_217 = false;
        if(intval($dataset["sensor"]["id"]) == 50) $s_50 = true; else $s_50 = false;
        foreach($dataset["sensordatavalues"] as $values){
            if($values["value_type"] == "P1"){
                if($values["value"] < $statistics["P1"]["min"]) {
                    $statistics["P1"]["min"] = floatval($values["value"]);
                    $statistics["P1"]["min_sensor_id"] = intval($dataset["sensor"]["id"]);
                }
                if($values["value"] > $statistics["P1"]["max"]) {
                    $statistics["P1"]["max"] = floatval($values["value"]);
                    $statistics["P1"]["max_sensor_id"] = intval($dataset["sensor"]["id"]);
                }
                if($s_231) $statistics["P1"]["231"] = floatval($values["value"]);
                if($s_217) $statistics["P1"]["217"] = floatval($values["value"]);
                if($s_50) $statistics["P1"]["50"] = floatval($values["value"]);
                array_push($p1,floatval($values["value"]));
            }
            if($values["value_type"] == "P2"){
                if($values["value"] < $statistics["P2"]["min"]) {
                    $statistics["P2"]["min"] = floatval($values["value"]);
                    $statistics["P2"]["min_sensor_id"] = intval($dataset["sensor"]["id"]);
                }
                if($values["value"] > $statistics["P2"]["max"]) {
                    $statistics["P2"]["max"] = floatval($values["value"]);
                    $statistics["P2"]["max_sensor_id"] = intval($dataset["sensor"]["id"]);
                }
                if($s_231) $statistics["P2"]["231"] = floatval($values["value"]);
                if($s_217) $statistics["P2"]["217"] = floatval($values["value"]);
                if($s_50) $statistics["P2"]["50"] = floatval($values["value"]);
                array_push($p2,floatval($values["value"]));
            }
        }
    }
    $statistics["timestamp"] = array_median($timestamps);
    $statistics["num_sensors"] = count($data);
    $statistics["P1"]["mid"] = array_median($p1);
    $statistics["P2"]["mid"] = array_median($p2);
    $mainSectorP1 = array_main_sector($p1);
    $statistics["P1"]["max_main"] = $mainSectorP1["max"];
    $statistics["P1"]["min_main"] = $mainSectorP1["min"];
    $mainSectorP2 = array_main_sector($p2);
    $statistics["P2"]["max_main"] = $mainSectorP2["max"];
    $statistics["P2"]["min_main"] = $mainSectorP2["min"];
    return $statistics;
}
/**
 *
 */
function generate_24h_floating_values($cronological_data){
    $timestampMin = $cronological_data[0]["timestamp"];
    $timestampStartFloating = 0;
    $timestampMax = 0;
    for($i=0;$i<count($cronological_data);$i++){
        if(!isset($cronological_data[$i]["P1"]["24floating"]) || !isset($cronological_data[$i]["P2"]["24floating"])){
            $values24hP10 = array();
            $values24hP25 = array();
            for($j=$i;$j>=0;$j--){
                if($cronological_data[$j]["timestamp"] < $cronological_data[$i]["timestamp"] - 60*60*24){
                    echo date("Ymd H:i:s",$cronological_data[$j]["timestamp"])." - ".date("Ymd H:i:s",$cronological_data[$i]["timestamp"])."<br/>";
                    $cronological_data[$i]["P1"]["24floating"] = array_arithmetic_mean($values24hP10);
                    $cronological_data[$i]["P2"]["24floating"] = array_arithmetic_mean($values24hP25);
                    echo "P10:". $cronological_data[$i]["P1"]["24floating"]."; P2.5: ".$cronological_data[$i]["P2"]["24floating"]."<br/>";
                    break;
                }
                array_push($values24hP10,$cronological_data[$j]["P1"]["mid"]);
                array_push($values24hP25,$cronological_data[$j]["P2"]["mid"]);
            }
        }
    }
    return $cronological_data;
}
/**
 *
 */
function search_missing_values($cronological_data){
    foreach($cronological_data as &$dataset){
        // if p1max = 50 add id[50][P1] = value
        if($dataset["P1"]["max_sensor_id"] == 50) $dataset["P1"]["50"] = $dataset["P1"]["max"];
        // if p1max = 217 add id[217][P1] = value
        if($dataset["P1"]["max_sensor_id"] == 217) $dataset["P1"]["217"] = $dataset["P1"]["max"];
        // if p1max = 50 add id[50][P1] = value
        if($dataset["P2"]["max_sensor_id"] == 50) $dataset["P2"]["50"] = $dataset["P2"]["max"];
        // if p1max = 217 add id[217][P1] = value
        if($dataset["P2"]["max_sensor_id"] == 217) $dataset["P2"]["217"] = $dataset["P2"]["max"];
    }
    return $cronological_data;
}
/**
 *
 */
function simplify_data($data){
    //echo "<pre>".print_r($data,true)."</pre>";
    $simple = "timestamp	num_sensors	num_values	P1high	P1low	P1mid	P1highmain	P1lowmain	P1highSensorId	P1lowSensorId	P1floating	P2high	P2low	P2mid	P2highmain	P2lowmain	P2highSensorId	P2lowSensorId	P2floating	P1_231	P2_231	P1_217	P2_217	P1_50	P2_50
";
    foreach($data as $dataset){
        $simple.= date("YmdHis",$dataset["timestamp"])."	". //Y-m-d
                    (isset($dataset["num_sensors"])?$dataset["num_sensors"]:"")."	".
                    (isset($dataset["num_values"])?$dataset["num_values"]:"")."	".
                    number_format($dataset["P1"]["max"], 1, '.', '')."	".
                    number_format($dataset["P1"]["min"], 1, '.', '')."	".
                    number_format($dataset["P1"]["mid"], 1, '.', '')."	".
                    (isset($dataset["P1"]["max_main"])?number_format($dataset["P1"]["max_main"], 1, '.', ''):"")."	".
                    (isset($dataset["P1"]["min_main"])?number_format($dataset["P1"]["min_main"], 1, '.', ''):"")."	".
                    (isset($dataset["P1"]["max_sensor_id"])?$dataset["P1"]["max_sensor_id"]:"")."	".
                    (isset($dataset["P1"]["min_sensor_id"])?$dataset["P1"]["min_sensor_id"]:"")."	".
                    (isset($dataset["P1"]["24floating"])?number_format($dataset["P1"]["24floating"], 1, '.', ''):"")."	".
                    number_format($dataset["P2"]["max"], 1, '.', '')."	".
                    number_format($dataset["P2"]["min"], 1, '.', '')."	".
                    number_format($dataset["P2"]["mid"], 1, '.', '')."	".
                    (isset($dataset["P2"]["max_main"])?number_format($dataset["P2"]["max_main"], 1, '.', ''):"")."	".
                    (isset($dataset["P2"]["min_main"])?number_format($dataset["P2"]["min_main"], 1, '.', ''):"")."	".
                    (isset($dataset["P2"]["max_sensor_id"])?$dataset["P2"]["max_sensor_id"]:"")."	".
                    (isset($dataset["P2"]["min_sensor_id"])?$dataset["P2"]["min_sensor_id"]:"")."	".
                    (isset($dataset["P2"]["24floating"])?number_format($dataset["P2"]["24floating"], 1, '.', ''):"")."	".
                    ((isset($dataset["P1"]["231"])&&$dataset["P1"]["231"]!=0)?number_format($dataset["P1"]["231"], 1, '.', ''):"")."	".
                    ((isset($dataset["P2"]["231"])&&$dataset["P2"]["231"]!=0)?number_format($dataset["P2"]["231"], 1, '.', ''):"")."	".
                    ((isset($dataset["P1"]["217"])&&$dataset["P1"]["217"]!=0)?number_format($dataset["P1"]["217"], 1, '.', ''):"")."	".
                    ((isset($dataset["P2"]["217"])&&$dataset["P2"]["217"]!=0)?number_format($dataset["P2"]["217"], 1, '.', ''):"")."	".
                    ((isset($dataset["P1"]["50"])&&$dataset["P1"]["50"]!=0)?number_format($dataset["P1"]["50"], 1, '.', ''):"")."	".
                    ((isset($dataset["P2"]["50"])&&$dataset["P2"]["50"]!=0)?number_format($dataset["P2"]["50"], 1, '.', ''):"")."
";
    }
    return $simple;
}
/**
 *
 */
function get_worst_values($data){
    //echo "<pre>".print_r($data,true)."</pre>";
    $worst = array("P1"=>array(),"P2"=>array());
    foreach($data as $dataset){
        if(isset($dataset["P1"]["max_sensor_id"]) && $dataset["P1"]["max_sensor_id"]!==0){
            $max_sensor_id = $dataset["P1"]["max_sensor_id"];
            //echo $max_sensor_id."<br/>";
            if(isset($worst["P1"][$max_sensor_id])){
                $worst["P1"][$max_sensor_id]++;
            }
            else{
                $worst["P1"][$max_sensor_id] = 1;
            }
        }
        if(isset($dataset["P2"]["max_sensor_id"]) && $dataset["P2"]["max_sensor_id"]!==0){
            $max_sensor_id = $dataset["P2"]["max_sensor_id"];
            //echo $max_sensor_id."<br/>";
            if(isset($worst["P2"][$max_sensor_id])){
                $worst["P2"][$max_sensor_id]++;
            }
            else{
                $worst["P2"][$max_sensor_id] = 1;
            }
        }
    }
    asort($worst["P1"], SORT_NUMERIC);
    asort($worst["P2"], SORT_NUMERIC);
    return $worst;
}
/**
 *
 */
function update_geodata($geodata,$data){
    //echo "update_geodata<br/>";
    //echo "<pre>".print_r($data[0],true)."</pre>";
    foreach($data as $dataset){
        $geodata[$dataset["sensor"]["id"]] = $dataset["location"];
    }
    //echo "<pre>".print_r($geodata,true)."</pre>";
    return $geodata;
}
/**
 *
 */
function data_to_db($data){
	date_default_timezone_set('Europe/Gibraltar');
	if(count($data)>0){
		$sql = array();
		foreach($data as $dataset){
            $P1 = 0;
            $P2 = 0;
            foreach($dataset["sensordatavalues"] as $values){
                if($values["value_type"] == "P1"){
                    $P1 = floatval($values["value"]);
                }
                if($values["value_type"] == "P2"){
                    $P2 = floatval($values["value"]);
                }
            }
			$timestamp = strtotime(str_replace("+00:00","+01:00",$dataset["timestamp"]));
			array_push($sql,"(NULL, '".$dataset["sensor"]["id"]."', '".$dataset["location"]["longitude"]."', '".$dataset["location"]["latitude"]."', '".date("Y-m-d H:i:s",$timestamp)."', '".$P1."', '".$P2."')");
            // aus csv2db:
            // array_push($sql,"(NULL, '".$dataset["sensor_id"]."', '".$dataset["lon"]."', '".$dataset["lat"]."', '".date("Y-m-d H:i:s",$timestamp)."', '".$dataset["P1"]."', '".$dataset["P2"]."')");

		}
		$sql = join(",",$sql);
		$sql = "INSERT INTO `sensors` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`) VALUES ".$sql." ON DUPLICATE KEY UPDATE `P1` = Values(`P1`), `P2` = Values(`P2`)";
        // aus cvs2db:
        // $sql = "INSERT INTO `sensors` (`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`) VALUES ".$sql." ON DUPLICATE KEY UPDATE";
        //mail("fritz.mielert@gmx.de","SQL Sensor Data",$sql);
		db_insert($sql);
	}
}
//
$data = read_data();
/*
echo "huhu";
print_r($data);
echo "fertig";
exit;
*/
// sds011 cleanup
$data = remove_sensors_not_sds($data);
// save to db
data_to_db($data);
// cleanup
$data = cleanup_data($data);
// save for other actions
//file_put_contents($dump_file,json_encode($data));

//echo count($data)." Datens&auml;tze<br/>";
// update geolocation
$statistics = get_min_max_mid($data["data"]);
$statistics["num_values"] = $data["num_values"];
// Print the date from the response
echo "<pre>".print_r($statistics,true)."</pre>";
//echo date("c",$statistics["timestamp"]);
array_push($cronological_data,$statistics);
$cronological_data = generate_24h_floating_values($cronological_data);
//$cronological_data = search_missing_values($cronological_data);
if(!$test_mode) {
    file_put_contents($data_file,json_encode($cronological_data));
}
// just one week
if(count($cronological_data)>2016){
    array_splice($cronological_data,0,count($cronological_data)-2016);
}
// flattening
$simple_cronological_data = simplify_data($cronological_data);
if(!$test_mode) {
    file_put_contents($simple_file, $simple_cronological_data);
}
?>
