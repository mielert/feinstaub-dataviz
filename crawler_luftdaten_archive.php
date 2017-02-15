<?php if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit; ?>
<?php
$startdate = strtotime(date("Y-m-d 00:00:00")." -3 days");
// start: 31.8.2016
// $startdate = strtotime("2016-12-10 00:00:00");
$stopdate = strtotime(date("Y-m-d 23:59:59")." -1 days");
// end: 17.12.2016
// $stopdate = strtotime("2016-12-30 23:59:59");

include_once("library.php");

$base = "http://archive.luftdaten.info/";
//$base = "http://archive.madavi.de/";
$localbase = $data_root."archive/";

$date = $startdate;
while($date < $stopdate){
	echo "crawling: ".date('Y-m-d H:i:s', $date)."<br/>";
	$dir = date('Y-m-d', $date)."/";
	$url = $base.$dir;
	echo "opening $url<br/>";
	$list_of_files = read_dir($url);
	$sds011files = filter_sds011($list_of_files);
	print_r($list_of_files);
	print_r($sds011files);
	foreach($sds011files as $filename){
		if(!is_dir($localbase.$dir)){
			echo "creating ".$localbase.$dir."<br/>";
			mkdir($localbase.$dir, 0700);
		}
		if(!file_exists(str_replace($base,$localbase,$filename))){
			if($content = file_get_contents($filename)){
				echo "saving ".str_replace($base,$localbase,$filename)."<br/>";
				file_put_contents(str_replace($base,$localbase,$filename),$content);
			}
			else echo "error reading $filename<br/>";
		}
		else echo "file ".str_replace($base,$localbase,$filename)." exists already<br/>";
	}
	//$date = $date + 24*60*60;
	$date = strtotime(date('Y-m-d H:i:s', $date)." + 1 day");
}


/**
 * @param 
 * @return array List of sensors 
 */
function read_dir($base){
	$filename = $base;
	$result = file_get_contents($filename);
	/* luftdaten old
	$result = substr($result,strpos($result,"<pre>"));
	$result = substr($result,0,strpos($result,"</pre>"));
	*/
	$result = explode("\n",$result);
	$filtered_files = array();
	foreach($result as $row){
		$row = substr($row,strpos($row,"<a href=\""));
		$row = substr($row,9,1000);
		$row = substr($row,0,strpos($row,"\">"));
		if(strpos($row,".csv")!==false){
			array_push($filtered_files,$base.$row);
		}
	}
	return $filtered_files;
}
/**
 * @param array $list_of_files
 * @return array Filtered list
 */
function filter_sds011($list_of_files){
	$filtered_files = array();
	foreach($list_of_files as $filename){
		if(strpos($filename,"sds011_sensor")!==false)
			array_push($filtered_files,$filename);
	}
	return $filtered_files;
}
?>
