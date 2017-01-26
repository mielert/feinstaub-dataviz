<?php
include("library.php");
$simple_file = $data_root."city_week.tsv";

  $sql = "SELECT *
FROM `sensors_hourly_mean`  
LEFT JOIN `x_coordinates_districts` ON `x_coordinates_districts`.`lon` = `sensors_hourly_mean`.`lon` AND `x_coordinates_districts`.`lat` = `sensors_hourly_mean`.`lat`
WHERE `sensors_hourly_mean`.`timestamp` = "2017-01-26 16:00:00"
AND `x_coordinates_districts`.`district_id` > 0  
ORDER BY `sensors_hourly_mean`.`sensor_id` ASC";
  

?>
