<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __DIR__ . '/xapi.base.php';
require_once __DIR__ . '/allotopup.lib.php';


use \FGTA4\exceptions\WebException;
use \transfashion\allo\allotopuplib;
// use \FGTA4\utils\Sequencer;


$API = new class extends allotopupBase {


	public function execute(object $param) : object {
		try {
			$at = new allotopuplib();
			
			$parameter = (object)[
				'topup_id' => $param->topup_id,
				'storeId' => $param->storeId,
				'cashierId' => $param->cashierId,
				'value' => $param->value,
				'barcode' => $param->barcode,
				'referenceNo' => $param->referenceNo
			];
			$status = $at->cek_status($parameter);		

			$result = new \stdClass; 
			$result->success = true;
			$result->status = $status; 
			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}

};