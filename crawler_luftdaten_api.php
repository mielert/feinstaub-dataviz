<pre><?php if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; ?>
<?php
include_once("library.php");
/**
 *
 */
function read_data_via_api(){
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
        if($dataset["sensor"]["sensor_type"]["name"]=="SDS011"){
            array_push($data_sds,$dataset);
        }
    }
    return $data_sds;
}
/**
 *
 */
function prepare_data_for_db($data){
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
            $sensor_id = get_sensor_id_by_sensor_name($dataset["sensor"]["id"]);
			// there are – but should'nt be – datasets without location. have to be removed
			if($dataset["location"]["longitude"]!=""&&$dataset["location"]["latitude"]!="")
				array_push($sql,"(NULL, '".intval($sensor_id)."', '".floatval($dataset["location"]["longitude"])."', '".floatval($dataset["location"]["latitude"])."', '".date("Y-m-d H:i:s",$timestamp)."', '".floatval($P1)."', '".floatval($P2)."')");
			// we've got quite long queries so let's split them up
			if(count($sql)>=200){
				data_to_db($sql);
				$sql = array();
			}
		}
		if(count($sql)>0){
			data_to_db($sql);
		}
	}
}
/**
 *
 */
function data_to_db($sql){
	echo count($sql);
	$sql = join(",\n",$sql);
	$sql = "INSERT INTO `sensor_data`
			(`id`, `sensor_id`, `lon`, `lat`, `timestamp`, `P1`, `P2`)
			VALUES ".$sql."
			ON DUPLICATE KEY UPDATE
			`P1` = Values(`P1`), `P2` = Values(`P2`)";
	return debug_query($sql);
}
// read data via api
$data = read_data_via_api();
// sds011 cleanup
$data = remove_sensors_not_sds($data);
// save to db
prepare_data_for_db($data);
?></pre>
