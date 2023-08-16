<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __DIR__ . '/xapi.base.php';


use \FGTA4\exceptions\WebException;


$API = new class extends WebAPI {

	public function download($options) {
		try {
	
			$downloadfilename = "TemplateUploadMediaOrder.xlsx";
			$filepath = __DIR__ . '/' . $downloadfilename;

			header('Content-Description: File Transfer');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="'.$downloadfilename.'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
						
			$fp = fopen($filepath, "r");
			$output = fread($fp, filesize($filepath));

			return $output;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

};