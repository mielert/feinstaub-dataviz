<?php if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR“]) exit; ?>
<?php
include_once("library.php");
$file = $data_root."data_lubw.json";
$simple_file = $data_root."data_lubw.tsv";

if(!file_exists($file)){
    file_put_contents($file,json_encode(array()));
}
if(filemtime($file)>time()-60) exit; // 1 minute

$fileContent = file_get_contents($file);
$cronological_data = json_decode($fileContent,true);

function read_lubw_data($url){
    // Setup cURL
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
    
    // Check for errors
    if($response === FALSE){
        die(curl_error($ch));
    }
    
    // Decode the response
    $responseData = $response;
    return $responseData;
}

function get_timestamp($data){
    $timestamp = false;
    $begin = strpos($data,"id=\"Datum\"");
    if($begin!==false){
        $data = substr($data,$begin);
        $begin = strpos($data,">");
        $data = substr($data,$begin+1);
        $end = strpos($data,"<");
        $data = substr($data,0,$end);
    }
    $data = substr($data,6,4)."-".substr($data,3,2)."-".substr($data,0,2)."T".substr($data,11,2).":".substr($data,14,2).":00";
    $timestamp = strtotime($data);
    //$timestamp = $data;
    return $timestamp;
}

function get_value($data){
    $value = false;
    $begin = strpos($data,"id=\"WerteTabelle\"");
    if($begin!==false){
        $data = substr($data,$begin);
        $begin = strpos($data,"</tr>");
        $data = substr($data,$begin+5);
        $begin = strpos($data,"</tr>");
        $data = substr($data,$begin+5);
        $begin = strpos($data,"</td>");
        $data = substr($data,$begin+5);
        $begin = strpos($data,"</td>");
        $data = substr($data,$begin+5);
        $begin = strpos($data,">");
        $data = substr($data,$begin+1);
        $end = strpos($data,"</td>");
        $data = substr($data,0,$end);
    }
    $value = $data;
    
    return floatval($value);
}
/**
 *
 */
function simplify_data($data){
    echo "<pre>".print_r($data,true)."</pre>";
    $simple = "timestamp	statDEBW013pm10	statDEBW118pm10
";
    foreach($data as $dataset){
        $simple.= date("YmdHis",$dataset["timestamp"])."	". //Y-m-d
                    /*$dataset["sensor"]."	".*/
                    (isset($dataset["statDEBW013pm10"])?number_format($dataset["statDEBW013pm10"], 1, '.', ''):"")."	".
                    (isset($dataset["statDEBW118pm10"])?number_format($dataset["statDEBW118pm10"], 1, '.', ''):"")."
";
    }
    return $simple;
}
$url = 'http://www.mnz.lubw.baden-wuerttemberg.de/messwerte/aktuell/statDEBW013.htm';
$lubwData = read_lubw_data($url);
$timestamp = get_timestamp($lubwData);
$value013p10 = get_value($lubwData);

$dataset = array("sensor_name"=>"DEBW013","sensor_type"=>2,"lon"=>9.230,"lat"=>48.809,"timestamp"=>$timestamp,"P10"=>$value013p10,"P25"=>"NULL");
save_sensor_data_to_database_daily_mean($dataset);

$url = 'http://www.mnz.lubw.baden-wuerttemberg.de/messwerte/aktuell/spotstatDEBW118.htm';
$lubwData = read_lubw_data($url);
$timestamp = get_timestamp($lubwData);
$value118p10 = get_value($lubwData);

$dataset = array("sensor_name"=>"DEBW118","sensor_type"=>2,"lon"=>9.191,"lat"=>48.788,"timestamp"=>$timestamp,"P10"=>$value118p10,"P25"=>"NULL");
save_sensor_data_to_database_daily_mean($dataset);


echo date("Y-m-d H:i:s",$timestamp).": ".$value013p10." / ".$value118p10."<br/>";

$lastTimestamp = $cronological_data[count($cronological_data)-1]["timestamp"];

echo date("Y-m-d H:i:s",$lastTimestamp);
print_r(array("timestamp"=>$timestamp,"statDEBW013pm10"=>$value013p10,"statDEBW118pm10"=>$value118p10));


if($timestamp > $lastTimestamp){
    array_push($cronological_data,array("timestamp"=>$timestamp,"statDEBW013pm10"=>$value013p10,"statDEBW118pm10"=>$value118p10));
    file_put_contents($file,json_encode($cronological_data));

    $simple_cronological_data = simplify_data($cronological_data);
    // just one week
    if(count($simple_cronological_data)>2016){
        array_splice($simple_cronological_data,0,count($simple_cronological_data)-2016);
    }
    file_put_contents($simple_file, $simple_cronological_data);
}

//echo $lubwData;

//id="Datum"

//id="WerteTabelle"

?>
