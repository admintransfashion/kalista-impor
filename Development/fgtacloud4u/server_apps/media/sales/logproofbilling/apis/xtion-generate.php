<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';

require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;



$API = new class extends logproofbillingBase {

	public function execute($id, $param) {
		$tablename = 'trn_medialogproofbilling';
		$primarykey = 'medialogproofbilling_id';
		$userdata = $this->auth->session_get_user();

		try {
			$currentdata = (object)[
				'header' => $this->get_header_row($id),
				'user' => $userdata
			];

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {

	
				$this->generate_billing($id, $currentdata);

				$this->db->commit();
				return (object)[
					'success' => true,
				];

				
			} catch (\Exception $ex) {
				$this->db->rollBack();
				throw $ex;
			} finally {
				$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
			}


		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	function generate_billing($id, $currentdata) {
		try {
		
			

		} catch (\Exception $ex) {
			throw $ex;
		}
	}



};


