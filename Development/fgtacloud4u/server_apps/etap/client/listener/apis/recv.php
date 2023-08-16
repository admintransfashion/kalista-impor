<?php namespace FGTA4\apis;
if (!defined('FGTA4')) {
        die('Forbiden');
}

require_once __ROOT_DIR.'/core/couchdbclient.php';

use \FGTA4\CouchDbClient;
use \FGTA4\exceptions\WebException;


class Receiver extends WebAPI {
	function __construct() {
			parent::__construct();
	}


	public function execute($data) {

			$success = false;

			try {

					$filename = uniqid() . '.txt';
					$filepath = implode('/', [__LOCALDB_DIR, 'salesdata', $filename]);

					$fp = fopen($filepath, "w");
					fputs($fp, json_encode($data));
					fclose($fp);    


					$success = true;

					if ($success) {
							return (object)[
									'status' => 'ok'
							];
					} else {
							throw new \Exception('Gagal menyimpan data');
					}
			} catch (\Exception $ex) {
					return (object)[
							'status' => 'fail',
							'message' => $ex->getMessage()
					];
			}
	}

}

$API = new Receiver();
