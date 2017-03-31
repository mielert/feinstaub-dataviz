<?php
if($_SERVER["REMOTE_ADDR"] !== $_SERVER["SERVER_ADDR"]) exit;

include_once("library.php");

$sql = "SELECT * ,(NOW()-DATE_ADD(`last_execution`, INTERVAL `interval` SECOND))
	FROM `cron_jobs` 
	WHERE (NOW() > DATE_ADD(`last_execution`, INTERVAL `interval` SECOND) 
	OR `last_execution` IS NULL)
	AND activated = 1
    ORDER BY (NOW()-DATE_ADD(`last_execution`, INTERVAL `interval` SECOND)) DESC";
$to_run = debug_query($sql);
if(count($to_run)>0){
	$sql = "UPDATE `cron_jobs` SET `last_execution` = NOW(), `done_at` = NULL, `last_result` = NULL, `message` = 'running (last: ".$to_run[0]->message.")' WHERE `id` = ".$to_run[0]->id;
	$result = debug_query($sql);
	$script_result = "";
	//$result = include_once($to_run[0]->script);
	$result2 = read_data($_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"]."/dataviz/".$to_run[0]->script);
	$sql = "UPDATE `cron_jobs` SET `last_result` = ".$result2["error_no"].", `done_at` = NOW(), `message` = '".$result2["error_info"]." | ".$result2["content"]."' WHERE `id` = ".$to_run[0]->id;
/*
	if($result) {
		$sql = "UPDATE `cron_jobs` SET `last_result` = 1, `done_at` = NOW(), `message` = '".$_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"]."/dataviz/".$to_run[0]->script." ".$script_result."' WHERE `id` = ".$to_run[0]->id;
	}
	else $sql = "UPDATE `cron_jobs` SET `last_result` = -1, `done_at` = NOW(), `message` = '$script_result' WHERE `id` = ".$to_run[0]->id;
	*/
	$result = debug_query($sql);
}

function read_data($url){
	global $script_result;
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
    
	$result = array();
	$result["error_no"] = 0;
	$result["error_info"] = "ok";
	$result["content"] = strip_tags($response);
    // Check for errors
    if($response === FALSE){
		$result["error_no"] = curl_error($ch);
    }
	if(!strpos($result["content"],"504")===false) $result["error_no"] =504;
	$result["error_info"] = get_error_info($result["error_no"]);

    return $result;
}
function get_error_info($code){
	$error_codes = array(
	0 => 'Ok',
	1 => 'CURLE_UNSUPPORTED_PROTOCOL', 
	2 => 'CURLE_FAILED_INIT', 
	3 => 'CURLE_URL_MALFORMAT', 
	4 => 'CURLE_URL_MALFORMAT_USER', 
	5 => 'CURLE_COULDNT_RESOLVE_PROXY', 
	6 => 'CURLE_COULDNT_RESOLVE_HOST', 
	7 => 'CURLE_COULDNT_CONNECT', 
	8 => 'CURLE_FTP_WEIRD_SERVER_REPLY',
	9 => 'CURLE_REMOTE_ACCESS_DENIED',
	11 => 'CURLE_FTP_WEIRD_PASS_REPLY',
	13 => 'CURLE_FTP_WEIRD_PASV_REPLY',
	14 =>'CURLE_FTP_WEIRD_227_FORMAT',
	15 => 'CURLE_FTP_CANT_GET_HOST',
	17 => 'CURLE_FTP_COULDNT_SET_TYPE',
	18 => 'CURLE_PARTIAL_FILE',
	19 => 'CURLE_FTP_COULDNT_RETR_FILE',
	21 => 'CURLE_QUOTE_ERROR',
	22 => 'CURLE_HTTP_RETURNED_ERROR',
	23 => 'CURLE_WRITE_ERROR',
	25 => 'CURLE_UPLOAD_FAILED',
	26 => 'CURLE_READ_ERROR',
	27 => 'CURLE_OUT_OF_MEMORY',
	28 => 'CURLE_OPERATION_TIMEDOUT',
	30 => 'CURLE_FTP_PORT_FAILED',
	31 => 'CURLE_FTP_COULDNT_USE_REST',
	33 => 'CURLE_RANGE_ERROR',
	34 => 'CURLE_HTTP_POST_ERROR',
	35 => 'CURLE_SSL_CONNECT_ERROR',
	36 => 'CURLE_BAD_DOWNLOAD_RESUME',
	37 => 'CURLE_FILE_COULDNT_READ_FILE',
	38 => 'CURLE_LDAP_CANNOT_BIND',
	39 => 'CURLE_LDAP_SEARCH_FAILED',
	41 => 'CURLE_FUNCTION_NOT_FOUND',
	42 => 'CURLE_ABORTED_BY_CALLBACK',
	43 => 'CURLE_BAD_FUNCTION_ARGUMENT',
	45 => 'CURLE_INTERFACE_FAILED',
	47 => 'CURLE_TOO_MANY_REDIRECTS',
	48 => 'CURLE_UNKNOWN_TELNET_OPTION',
	49 => 'CURLE_TELNET_OPTION_SYNTAX',
	51 => 'CURLE_PEER_FAILED_VERIFICATION',
	52 => 'CURLE_GOT_NOTHING',
	53 => 'CURLE_SSL_ENGINE_NOTFOUND',
	54 => 'CURLE_SSL_ENGINE_SETFAILED',
	55 => 'CURLE_SEND_ERROR',
	56 => 'CURLE_RECV_ERROR',
	58 => 'CURLE_SSL_CERTPROBLEM',
	59 => 'CURLE_SSL_CIPHER',
	60 => 'CURLE_SSL_CACERT',
	61 => 'CURLE_BAD_CONTENT_ENCODING',
	62 => 'CURLE_LDAP_INVALID_URL',
	63 => 'CURLE_FILESIZE_EXCEEDED',
	64 => 'CURLE_USE_SSL_FAILED',
	65 => 'CURLE_SEND_FAIL_REWIND',
	66 => 'CURLE_SSL_ENGINE_INITFAILED',
	67 => 'CURLE_LOGIN_DENIED',
	68 => 'CURLE_TFTP_NOTFOUND',
	69 => 'CURLE_TFTP_PERM',
	70 => 'CURLE_REMOTE_DISK_FULL',
	71 => 'CURLE_TFTP_ILLEGAL',
	72 => 'CURLE_TFTP_UNKNOWNID',
	73 => 'CURLE_REMOTE_FILE_EXISTS',
	74 => 'CURLE_TFTP_NOSUCHUSER',
	75 => 'CURLE_CONV_FAILED',
	76 => 'CURLE_CONV_REQD',
	77 => 'CURLE_SSL_CACERT_BADFILE',
	78 => 'CURLE_REMOTE_FILE_NOT_FOUND',
	79 => 'CURLE_SSH',
	80 => 'CURLE_SSL_SHUTDOWN_FAILED',
	81 => 'CURLE_AGAIN',
	82 => 'CURLE_SSL_CRL_BADFILE',
	83 => 'CURLE_SSL_ISSUER_ERROR',
	84 => 'CURLE_FTP_PRET_FAILED',
	84 => 'CURLE_FTP_PRET_FAILED',
	85 => 'CURLE_RTSP_CSEQ_ERROR',
	86 => 'CURLE_RTSP_SESSION_ERROR',
	87 => 'CURLE_FTP_BAD_FILE_LIST',
	88 => 'CURLE_CHUNK_FAILED',
	504 => 'Gateway Timeout');
	return (isset($error_codes[$code]))?$error_codes[$code]:"unknown";
}

?>
