<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;


$API = new class extends synctbBase {

	public function execute(object $param) : object {
		$userdata = $this->auth->session_get_user();

		try {

			$id = $param->id;
			$doctype = $param->doctype;

			switch ($doctype) {
				case 'RV':
					return $this->sync_rv($id, $userdata->username);
					break;

				default:
					throw new \Exception('doctype belum didefinisikan');
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	private function sync_rv($id, $username) {
		// jalankan perintah di background;
		$name = 'process-tbsyncrv';
		$id = 'RV/05/WH-JKT/2300000001'; // kode dokumen
		$logfile = "/mnt/ramdisk/log-$pid.txt";

		$pid = 1234567890; // pid dummy

		$cmdscript = "/var/www/fgtacloud4u/server_apps/community/tfi/synctb/cli/syncrv.sh";
		$command = "$cmdscript -n $name -s $pid -u $username -i $id  2>&1 | tee -a $logfile 2>/dev/null >/dev/null &";
		$output = shell_exec($command);

		return (object)[
			'name' => $name,
			'pid' => $pid,
			'id' => $id,
			'output' => $output
		];
	}

};

