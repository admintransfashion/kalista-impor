<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __DIR__ . '/xapi.base.php';


use \FGTA4\exceptions\WebException;


$API = new class extends WebAPI {

	public $method = 'POST';

	public function download($options) {
		try {
	
			$downloadfilename = "TemplateUploadMediaOrder.xlsx";
			//$downloadfilename = "testscreen.png";
			$filepath = __DIR__ . '/' . $downloadfilename;
			$contenttype = mime_content_type($filepath);
				
			$fp = fopen($filepath, "r");
			$output = fread($fp, filesize($filepath));
			$base64 = "data:/" . $contenttype . ";base64," . base64_encode($output);

			header('Content-Length: ' . strlen($base64));
			header('Content-Disposition: attachment; filename="'.$downloadfilename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
	
			return $base64;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

};




//header('Content-Description: File Transfer');
//header('Content-Type: '.$contenttype);
//header('Content-Transfer-Encoding: binary');
