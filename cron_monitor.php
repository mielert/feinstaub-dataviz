<html>
	<head>
		<meta http-equiv="refresh" content="30; URL=<?php echo $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; ?>">
		<style>
			body{
				background-color: rgb(100,100,100);
				/*font-family: sans-serif;*/
			}
			#content{
				max-width: 500px;
				width: 90%;
				margin: 0 auto;
			}
			.logitem{
				padding: 10px;
				background-color: rgb(200,0,0);
				margin-top: 10px;
				margin-bottom: 10px;
				-webkit-border-radius: 4px;
				border-radius: 4px;
				-webkit-box-shadow: 1px 2px 5px 2px rgba(0,0,0,0.4);
				box-shadow: 1px 2px 5px 2px rgba(0,0,0,0.4);
				color: #fff;
			}
			.running{
				background-color: rgb(255,174,0);
				color: #000;
			}
			.ok{
				background-color: rgb(0,200,0);
				color: #000;
			}
			.inactive{
				opacity: 0.5;
			}
			.logitem_headline{
				text-align: center;
				font-weight: 600;
			}
			.logitem_description{
				
			}
			.logitem_config{
				
			}
			.logitem_lastresult{
				
			}
			.logitem_result_no{
				width: 50px;
				float: left;
			}
			.logitem_result_message{
				
			}
		</style>
	</head>
	<body>
		<div id="content">

<?php
include_once("library.php");

$sql = "SELECT * FROM `cron_jobs` ORDER BY `activated` DESC, `last_execution` DESC";

$results = db_select($sql);

foreach($results as $result){
	?>

			<div class="logitem<?php
			if ($result->activated == 0){
				echo " inactive";
			}
			else{
				if ($result->last_result=="")
					echo " running";
				elseif ($result->last_result==="0")
					echo " ok";
			}
			?>">
				<div class="logitem_headline">
					<?php echo $result->script; ?> (<?php echo ($result->activated==1)?"active":"inactive";?>)
				</div>
				<div class="logitem_description">
					<?php echo $result->name; ?>
					<!--<pre><?php print_r($result); ?></pre>-->
				</div>
				<div class="logitem_config">
					Running every <?php echo $result->interval; ?> seconds<?php echo ($result->activated==1)?"":" if active";?>.
				</div>
				<div class="logitem_lastresult">
					<!--<div class="logitem_result_no">
					</div>-->
					<div class="logitem_result_message">
						<!--<?php echo $result->last_result; ?><br/>-->
						Start: <?php echo $result->last_execution; ?>, 
						End: <?php echo $result->done_at; ?><br/>
						Result: <?php echo $result->message; ?>
					</div>
				</div>
			</div>
<?php }

?>
		</div>

	</body>
</html>
