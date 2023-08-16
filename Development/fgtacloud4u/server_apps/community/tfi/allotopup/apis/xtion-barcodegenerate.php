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
			];
			$res = $at->generate_barcode_topup($parameter);

			$result = new \stdClass; 
			$result->success = true;
			$result->barcode = $res->barcode; 
			$result->referenceNo = $res->referenceNo;
			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}

};