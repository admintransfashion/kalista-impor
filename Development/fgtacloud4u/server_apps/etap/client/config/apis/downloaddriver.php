<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}


require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/rootdir/phpoffice_phpspreadsheet_1.13.0.0/vendor/autoload.php';


use \FGTA4\exceptions\WebException;
use \FGTA4\debug;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Download extends WebAPI {
	function __construct() {
		$logfilepath = __LOCALDB_DIR . "/output/test-download.txt";
		// debug::disable();
		debug::start($logfilepath, "w");
		$this->debugoutput = false;
		$DB_CONFIG = DB_CONFIG[$GLOBALS['MAINDB']];
		$DB_CONFIG['param'] = DB_CONFIG_PARAM[$GLOBALS['MAINDBTYPE']];
		$this->db = new \PDO(
					$DB_CONFIG['DSN'], 
					$DB_CONFIG['user'], 
					$DB_CONFIG['pass'], 
					$DB_CONFIG['param']
		);	
	}
	
	public function download($drivername) {
		$filename =  $drivername . '.min.js';
		$driverpath = __ROOT_DIR . '/apps/etap/client/config/drivers/'. $filename;
		if (!is_file($driverpath)) {
			$errormessage = "driver $drivername tidak tidak bisa di-download karena tidak ada di server!";
			header("fgta4-errormessage: $errormessage");
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
			throw new \Exceptions($errormessage);
		}
		

		header('Content-Description: File Transfer');
		header('Content-Type: text/javascript');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($driverpath));

		$fp = fopen($driverpath, 'r');
		$output = fread($fp, filesize($driverpath));
		fclose($fp);
		
		return $output;
	}



}

$API = new Download();