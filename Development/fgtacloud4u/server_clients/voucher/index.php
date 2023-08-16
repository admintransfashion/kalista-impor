<?php

// echo "get voucher";
$id = array_key_exists('id', $_GET) ? $_GET['id'] : 'yy';
$batch = array_key_exists('batch', $_GET) ? $_GET['batch'] : 'xx';

$voucher_id = base64_decode($id);
$voubatch_id = $batch;

try {

	$location = "/var/www/html/fgta4/kalista/data/voucher";
	$filename = "$voucher_id.jpg";
	$filepath = implode('/', [$location, $voubatch_id, $filename]);

	if (is_file($filepath)) {
		$html_filename = "tfi-$voubatch_id-$voucher_id.html";
		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Disposition: inline; filename="'.$html_filename.'"');
		
		$fp = fopen($filepath, "r");
		$contents = fread($fp, filesize($filepath));
		$img_data = base64_encode($contents);
		fclose($fp);

		$size = getimagesize($filepath);
		$width = $size[0];
		$height = $size[1];

		$img  = "<img src=\"data:image/jpeg;base64,$img_data\" width=\"$width\" height=\"$height\">";
		// $img .= '<div style="padding-top: 30px;"><hr><a href="javascript:window.close()">Close</a></div>';


		$html = "<!DOCTYPE html>
<html>
	<head>
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no\">
		<style>
			html, body {
				padding: 0px;
					margin: 0px;
			}
		</style>
	</head>
	<body>
		<div style=\"text-align: center; margin-top: 20px; margin-bottom: 20px\">
			<a href=\"https://www.transfashionindonesia.com\"><img src=\"tfi-logo.png\" width=\"150px\" height=\"44\"></a>
		</div>
		<div style=\"text-align: center;\">
			<div style=\"display: inline-block; box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.75);
			-webkit-box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.75);
			-moz-box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.75);\">
			$img
			</div>
		</div>	
	</body>
</html>";

		echo $html;	


	} else {
		throw new Exception();
	}

} catch (Exception $ex) {
	echo "Voucher not found";
}


// echo "<hr>";
// echo urlencode(base64_encode('2300100701601'));