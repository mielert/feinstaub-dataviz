<?php if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; ?>
<?php
include_once("library.php");

$sql = "SELECT * ,(NOW()-DATE_ADD(`last_execution`, INTERVAL `interval` SECOND))
	FROM `cron_jobs` 
	WHERE (NOW() > DATE_ADD(`last_execution`, INTERVAL `interval` SECOND) 
	OR `last_execution` IS NULL)
	AND activated = 1
    ORDER BY (NOW()-DATE_ADD(`last_execution`, INTERVAL `interval` SECOND)) DESC";
$to_run = debug_query($sql);
if(count($to_run)>0){
	$sql = "UPDATE `cron_jobs` SET `last_execution` = NOW() WHERE `id` = ".$to_run[0]->id;
	$result = debug_query($sql);
	$result = include_once($to_run[0]->script);
	if($result) $sql = "UPDATE `cron_jobs` SET `last_result` = 1 WHERE `id` = ".$to_run[0]->id;
	else $sql = "UPDATE `cron_jobs` SET `last_result` = -1 WHERE `id` = ".$to_run[0]->id;
	$result = debug_query($sql);
}


?>
