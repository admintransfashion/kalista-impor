<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';


use \FGTA4\exceptions\WebException;



class DataOpen extends WebAPI {
	function __construct() {
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
	
	public function execute($options) {

		$userdata = $this->auth->session_get_user();

		try {

			// cek apakah user boleh mengeksekusi API ini
			if (!$this->RequestIsAllowedFor($this->reqinfo, "open", $userdata->groups)) {
				throw new \Exception('your group authority is not allowed to do this action.');
			}

			$result = new \stdClass; 
			
			$where = \FGTA4\utils\SqlUtility::BuildCriteria(
				$options->criteria,
				[
					"mediaorder_id" => " mediaorder_id = :mediaorder_id "
				]
			);

			$sql = \FGTA4\utils\SqlUtility::Select('trn_mediaorder', [
				'mediaorder_id', 'mediaorder_descr', 'mediaorder_date', 'mediaordertype_id', 'agency_partner_id', 'advertiser_partner_id', 'brand_id', 'mediapackage_id', 'salesordertype_id', 'doc_id', 
				'mediaorder_iscommit', 'mediaorder_commitby', 'mediaorder_commitdate',
				'mediaorder_iscompleted', 'mediaorder_completedate',
				'mediaorder_isbilled', 'mediaorder_billdate',
				'mediaorder_isclosed', 'mediaorder_closeby', 'mediaorder_closedate',
				'_createby', '_createdate', '_modifyby', '_modifydate' 
			], $where->sql);

			$stmt = $this->db->prepare($sql);
			$stmt->execute($where->params);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);

			$record = [];
			foreach ($row as $key => $value) {
				$record[$key] = $value;
			}

			$result->record = array_merge($record, [
				'mediaorder_date' => date("d/m/Y", strtotime($record['mediaorder_date'])),
				
				// // jikalau ingin menambah atau edit field di result record, dapat dilakukan sesuai contoh sbb: 
				// 'tambahan' => 'dta',
				//'tanggal' => date("d/m/Y", strtotime($record['tanggal'])),
				//'gendername' => $record['gender']
				
				'mediaordertype_name' => \FGTA4\utils\SqlUtility::Lookup($record['mediaordertype_id'], $this->db, 'mst_mediaordertype', 'mediaordertype_id', 'mediaordertype_name'),
				'agency_partner_name' => \FGTA4\utils\SqlUtility::Lookup($record['agency_partner_id'], $this->db, 'mst_partner', 'partner_id', 'partner_name'),
				'advertiser_partner_name' => \FGTA4\utils\SqlUtility::Lookup($record['advertiser_partner_id'], $this->db, 'mst_partner', 'partner_id', 'partner_name'),
				'brand_name' => \FGTA4\utils\SqlUtility::Lookup($record['brand_id'], $this->db, 'mst_brand', 'brand_id', 'brand_name'),
				'mediapackage_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediapackage_id'], $this->db, 'mst_mediapackage', 'mediapackage_id', 'mediapackage_descr'),
				'salesordertype_name' => \FGTA4\utils\SqlUtility::Lookup($record['salesordertype_id'], $this->db, 'mst_salesordertype', 'salesordertype_id', 'salesordertype_name'),
				'doc_name' => \FGTA4\utils\SqlUtility::Lookup($record['doc_id'], $this->db, 'mst_doc', 'doc_id', 'doc_name'),

				'_createby_username' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				'_modifyby_username' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				'mediaorder_commitby_username' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorder_commitby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				'mediaorder_closeby_username' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorder_closeby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),

			]);

			// $date = DateTime::createFromFormat('d/m/Y', "24/04/2012");
			// echo $date->format('Y-m-d');

			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}

$API = new DataOpen();