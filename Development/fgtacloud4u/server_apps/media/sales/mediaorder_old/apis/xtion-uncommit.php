<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';
require_once __ROOT_DIR.'/core/currency.php';

require_once __DIR__.'/xtion.base.php';


use \FGTA4\exceptions\WebException;
use \FGTA4\utils\Currency;
use \FGTA4\debug;


$API = new class extends XtionBase {
	function __construct() {
		//$logfilepath = __LOCALDB_DIR . "/output/ofrecv-post.txt";
		// debug::disable();
		//debug::start($logfilepath, "w");

		$this->debugoutput = true;
		$DB_CONFIG = DB_CONFIG[$GLOBALS['MAINDB']];
		$DB_CONFIG['param'] = DB_CONFIG_PARAM[$GLOBALS['MAINDBTYPE']];
		$this->db = new \PDO(
					$DB_CONFIG['DSN'], 
					$DB_CONFIG['user'], 
					$DB_CONFIG['pass'], 
					$DB_CONFIG['param']
		);	

	}

	public function execute($id) {
		$userdata = $this->auth->session_get_user();
	
		try {
			
			$data = (object)[
				'header' => $this->get_header_row($id),
				'detil'  => $this->get_detil_rows($id),
				'currentuser' => $userdata
			];

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();
			
			try {

				$this->pre_action_check($data);
				$this->uncommit_salesorder($data);
				$this->save_and_set_flag($data);

				$this->db->commit();
				return true;
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


	function pre_action_check($data) {
		try {
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function uncommit_salesorder($data) {
		try {
			$salesorder_id = $data->header->mediaorder_id;
			$this->db->query("delete from trn_salesorderitem where salesorder_id = '$salesorder_id'");
			$this->db->query("update trn_salesorder set  salesorder_iscommit=0, salesorder_commitby=null, salesorder_commitdate=null where salesorder_id = '$salesorder_id'");
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function save_and_set_flag($data) {
		try {

			$sql = " 
				update trn_mediaorder
				set 
				mediaorder_iscommit = 0,
				mediaorder_commitby = :username,
				mediaorder_commitdate = :date
				where
				mediaorder_id = :id
			";

			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				":id" => $data->header->mediaorder_id,
				":username" => null, //$data->currentuser->username,
				":date" => null //date("Y-m-d H:i:s")
			]);

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}


};