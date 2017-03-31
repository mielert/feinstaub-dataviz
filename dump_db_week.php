<?php if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; ?>
<pre>
<?php
echo "not yet...";
exit;
include("library.php");
$simple_file = $data_root."city_week.tsv";

$city_id = 1;

$sql = "SELECT MAX(`timestamp`) AS timestamp FROM `cities_mean` WHERE `city_id` = $city_id";
$result = debug_query($sql);
if(count($result) > 0 && $result[0]->timestamp > "1000-01-01 00:00:00")
  $start_timestamp = $result[0]->timestamp;
else{
  $sql = "SELECT MIN(`timestamp`) AS timestamp 
          FROM `sensors_hourly_mean` 
          LEFT JOIN `x_coordinates_regions` 
            ON `x_coordinates_regions`.`lon` = `sensors_hourly_mean`.`lon` 
            AND `x_coordinates_regions`.`lat` = `sensors_hourly_mean`.`lat`
          LEFT JOIN `districts` ON `districts`.`id` = `x_coordinates_regions`.`district_id`
          WHERE `districts`.`city_id` = $city_id";
  $result = debug_query($sql);
  $start_timestamp = $result[0]->timestamp;
}
$sql = "SELECT DISTINCT `timestamp`
        FROM `sensors_hourly_mean` 
        LEFT JOIN `x_coordinates_regions` 
          ON `x_coordinates_regions`.`lon` = `sensors_hourly_mean`.`lon` 
          AND `x_coordinates_regions`.`lat` = `sensors_hourly_mean`.`lat`
        LEFT JOIN `districts` ON `districts`.`id` = `x_coordinates_regions`.`district_id`
        WHERE `districts`.`city_id` = $city_id
        ORDER BY `timestamp`";
$results = debug_query($sql);
$stop_timestamp = $result[count($result)-1]->timestamp;

$counter = 0;
foreach($results as $result){
  $timestamp = $result->timestamp;
  $sql = "SELECT *
          FROM `sensors_hourly_mean`  
          LEFT JOIN `x_coordinates_regions` 
            ON `x_coordinates_regions`.`lon` = `sensors_hourly_mean`.`lon` 
            AND `x_coordinates_regions`.`lat` = `sensors_hourly_mean`.`lat`
          WHERE `sensors_hourly_mean`.`timestamp` = '$timestamp'
          LEFT JOIN `districts` ON `districts`.`id` = `x_coordinates_regions`.`district_id`
          WHERE `districts`.`city_id` = $city_id
          ORDER BY `sensors_hourly_mean`.`sensor_id` ASC";
  $result = debug_query($sql);
  $data = get_min_max_mid($result);
  print_r($data);
  save_min_max_mid($data,$timestamp,count($result),$city_id);
  if($counter>10) break;
  $counter++;
}
/**
 *
 */
function save_min_max_mid($data,$timestamp,$num_sensors,$city_id){
  $sql = "INSERT INTO `cities_mean` 
          ( `id`,
            `city_id`,
            `timestamp`,
            `P1h`,
            `P2h`,
            `P1d`,
            `P2d`,
            `P1h_min`,
            `P1h_max`,
            `P2h_min`,
            `P2h_max`,
            `P1h_50_min`,
            `P1h_50_max`,
            `P2h_50_min`,
            `P2h_50_max`,
            `P1min_sensor_id`,
            `P1max_sensor_id`,
            `P2min_sensor_id`,
            `P2max_sensor_id`)
				  VALUES 
          ( NULL,
            $city_id,
            '$timestamp',
            ".$data["P1"]["mid"].",
            ".$data["P2"]["mid"].",
            ".$data["P1"]["d_mid"].",
            ".$data["P2"]["d_mid"].",
            ".$data["P1"]["min"].",
            ".$data["P1"]["max"].",
            ".$data["P2"]["min"].",
            ".$data["P2"]["max"].",
            ".$data["P1"]["min_main"].",
            ".$data["P1"]["max_main"].",
            ".$data["P2"]["min_main"].",
            ".$data["P2"]["max_main"].",
            ".$data["P1"]["min_sensor_id"].",
            ".$data["P1"]["max_sensor_id"].",
            ".$data["P2"]["min_sensor_id"].",
            ".$data["P2"]["max_sensor_id"]."
          )
				  ON DUPLICATE KEY UPDATE 
            `P1` = VALUES(`P1`), 
            `P2` = VALUES(`P2`); ";
  
  ";
}
/**
 * $data["P1"]["d_mid"].",
 *           ".$data["P2"]["d_mid"].",
 * fehlen
 */
function get_min_max_mid($data,$timestamp){
    //echo "get_min_max_mid";
    $statistics = array("P1"=>array("min"=>1000000,
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
