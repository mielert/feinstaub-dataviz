<pre>
<?php
include("library.php");
$simple_file = $data_root."city_week.tsv";

$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = 1";
$result = debug_query($sql);
if(count($result) > 0)
  $start_timestamp = $result[0]->timestamp;
else{
  $sql = "SELECT MIN(`timestamp`) AS timestamp 
          FROM `sensors_hourly_mean` 
          LEFT JOIN `x_coordinates_districts` 
            ON `x_coordinates_districts`.`lon` = `sensors_hourly_mean`.`lon` 
            AND `x_coordinates_districts`.`lat` = `sensors_hourly_mean`.`lat`
          LEFT JOIN `districts` ON `districts`.`id` = `x_coordinates_districts`.`district_id`
          WHERE `districts`.`city_id` = 1";
  $result = debug_query($sql);
  $start_timestamp = $result[0]->timestamp;
}
$sql = "SELECT MAX(`timestamp`) AS timestamp 
        FROM `sensors_hourly_mean` 
        LEFT JOIN `x_coordinates_districts` 
          ON `x_coordinates_districts`.`lon` = `sensors_hourly_mean`.`lon` 
          AND `x_coordinates_districts`.`lat` = `sensors_hourly_mean`.`lat`
        LEFT JOIN `districts` ON `districts`.`id` = `x_coordinates_districts`.`district_id`
        WHERE `districts`.`city_id` = 1";
$result = debug_query($sql);
$stop_timestamp = $result[0]->timestamp;

  $timestamp = "2017-01-26 16:00:00";
  $sql = "SELECT *
FROM `sensors_hourly_mean`  
LEFT JOIN `x_coordinates_districts` ON `x_coordinates_districts`.`lon` = `sensors_hourly_mean`.`lon` AND `x_coordinates_districts`.`lat` = `sensors_hourly_mean`.`lat`
WHERE `sensors_hourly_mean`.`timestamp` = '$timestamp'
AND `x_coordinates_districts`.`district_id` > 0  
ORDER BY `sensors_hourly_mean`.`sensor_id` ASC";
  
  $result = debug_query($sql);
  $data = get_min_max_mid($result,$timestamp);
  print_r($data);
/**
 *
 */
function get_min_max_mid($data,$timestamp){
    //echo "get_min_max_mid";
    $statistics = array("timestamp"=>$timestamp,
                        "num_sensors"=>0,
                        "P1"=>array("min"=>1000000,
                                    "max"=>0,
                                    "mid"=>0,
                                    "max_main"=>"",
                                    "min_main"=>"",
                                    "max_sensor_id"=>"",
                                    "min_sensor_id"=>""
                                    ),
                        "P2"=>array("min"=>1000000,
                                    "max"=>0,
                                    "mid"=>0,
                                    "max_main"=>"",
                                    "min_main"=>"",
                                    "max_sensor_id"=>"",
                                    "min_sensor_id"=>""
                                    )
                        );
    $p1 = array();
    $p2 = array();
    foreach($data as $dataset){
        //echo $dataset["sensor"]["id"]."<br/>";
        // echo "<pre>".print_r($dataset,true)."</pre>";
        if($dataset->P1 < $statistics["P1"]["min"]) {
            $statistics["P1"]["min"] = floatval($dataset->P1);
            $statistics["P1"]["min_sensor_id"] = intval($dataset->sensor_id);
        }
        if($dataset->P1 > $statistics["P1"]["max"]) {
            $statistics["P1"]["max"] = floatval($dataset->P1);
            $statistics["P1"]["max_sensor_id"] = intval($dataset->sensor_id);
        }
        array_push($p1,floatval($dataset->P1));
        if($dataset->P2 < $statistics["P2"]["min"]) {
            $statistics["P2"]["min"] = floatval($dataset->P2);
            $statistics["P2"]["min_sensor_id"] = intval($dataset->sensor_id);
        }
        if($dataset->P2 > $statistics["P2"]["max"]) {
            $statistics["P2"]["max"] = floatval($dataset->P1);
            $statistics["P2"]["max_sensor_id"] = intval($dataset->sensor_id);
        }
        array_push($p2,floatval($dataset->P2));
    }
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

?>
</pre>
