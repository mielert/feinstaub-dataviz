<?php
/**
 * Map the most recent values to districts
 */

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
   echo "Please include this file & you'll get \$coordinates_stuttgart<br/>";
}
//include_once("../library.php");

include_once("stuttgart.php");

// Read most recent values
$file = "dump.json";
$lastdata = json_decode(file_get_contents($file),true);

$pointLocation = new pointLocation();

$coordinates_stuttgart = get_city_geodata();

$timestamp = array();
foreach($coordinates_stuttgart as &$polygon){
    echo $polygon["name"]."<br/>";
    $simplify_polygon = simplify_polygon($polygon["coordinates"]);
    $polygon["P1"] = array();
    $polygon["P2"] = array();
    $polygon["Num_Sensors"] = 0;
    $polygon["Sensor_IDs"] = array();
    foreach($lastdata["data"] as $dataset) {
        $result = $pointLocation->pointInPolygon($dataset["location"]["longitude"]." ".$dataset["location"]["latitude"], $simplify_polygon);
        //echo "<pre>".print_r($dataset,true)."</pre>";
        if($result != "outside"){
            $polygon["Num_Sensors"]++;
            array_push($timestamp,floatval($dataset["timestamp"]));
            array_push($polygon["Sensor_IDs"],$dataset["sensor"]["id"]);
            //echo "Sensor ".$dataset["sensor"]["id"]." (".$dataset["simplified_location"]."): " . $result . "<br/>";
            foreach($dataset["sensordatavalues"] as $values){
                if($values["value_type"] == "P1"){
                    array_push($polygon["P1"],floatval($values["value"]));
                }
                if($values["value_type"] == "P2"){
                    array_push($polygon["P2"],floatval($values["value"]));
                }
            }
        }
    }
    
    
    $polygon["P1-Sensors"] = count($polygon["P1"]);
    $polygon["P2-Sensors"] = count($polygon["P2"]);
    //echo count($polygon["P1"])." P2.5-Sensoren<br/>";
    $polygon["P1"] = array_median($polygon["P1"]);
    $polygon["P2"] = array_median($polygon["P2"]);
    //echo "P1: ".$polygon["P1"]."<br/>";
    //echo "P2: ".$polygon["P2"]."<br/><br/>";
}
$coordinates_stuttgart["timestamp"] = array_arithmetic_mean($timestamp);

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    echo "<pre>".print_r($coordinates_stuttgart,true)."</pre>";
}

?>
