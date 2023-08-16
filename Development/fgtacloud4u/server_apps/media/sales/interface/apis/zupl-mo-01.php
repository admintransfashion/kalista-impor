<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/debug.php';
require_once __ROOT_DIR.'/core/sqlutil.php';


use \FGTA4\exceptions\WebException;
use \FGTA4\debug;


$API = new class extends WebAPI {
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

		$logfilepath = __LOCALDB_DIR . "/output/mediaorder.txt";
		// debug::disable();
		debug::start($logfilepath, "w");
	}
	
	public function execute($data) {

		try {
			
			debug::log($_SERVER);

			$currentdate = date("Y-m-d H:i:s");
			$user_id = $_SERVER['HTTP_FGSERID'];
			$key = $_SERVER['HTTP_FGKEY'];
			$header = $data->header;
			$detils = $data->detil;


			$header->id = uniqid();

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {

				$partner_agency_data = $this->GetPartnerData($header->agency_id, $header->agency_name);
				$partner_advertiser_data = $this->GetPartnerData($header->advertiser_id, $header->advertiser_name);
				$brand_data = $this->GetBrandData($header->brand_id, $header->brand_name);

				/* Save di Sales Order */
				$obj = new \stdClass;
				$obj->salesorder_id =  $header->id;
				$obj->salesorder_descr = $header->description;
				$obj->salesorder_date = $header->date;
				$obj->salesordertype_id = $header->ordertype;	
				$obj->salesorder_iscommit = 1;
				$obj->salesorder_commitby = $user_id;
				$obj->salesorder_commitdate = $currentdate;
				$obj->partner_id = $partner_agency_data->partner_id;
				$obj->doc_id = 'MEDIAORDER'; 				
				$obj->_createby = $user_id;
				$obj->_createdate = $currentdate;
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_salesorder", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				/* Save di Media Order */
				$obj = new \stdClass;
				$obj->mediaorder_id = $header->id;
				$obj->mediaorder_descr = $header->description;
				$obj->mediaorder_date = $header->date;
				$obj->mediaorder_iscommit = 1;
				$obj->mediaorder_commitby = $user_id;
				$obj->mediaorder_commitdate = $currentdate;
				$obj->agency_partner_id = $partner_agency_data->partner_id;
				$obj->advertiser_partner_id = $partner_advertiser_data->partner_id;
				$obj->brand_id = $brand_data->brand_id;
				$obj->salesordertype_id = $header->ordertype;
				$obj->doc_id = 'MEDIAORDER'; 
				$obj->_createby = $user_id;
				$obj->_createdate = $currentdate;
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_mediaorder", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);


				foreach ($detils as $item) {

					$line_id = uniqid();

					/* Save di Sales Order Item*/
					$obj = new \stdClass;
					$obj->salesorder_id = $header->id;
					$obj->salesorderitem_id = $line_id;
					$obj->salesorderitem_descr = $item->description;
					$obj->salesorderitem_price = $item->price;
					$obj->salesorderitem_qty = 1;
					$obj->salesorderitem_pricesubtotal = $item->price;
					$obj->itemclass_id = $item->class;
					$obj->_createby = $user_id;
					$obj->_createdate = $currentdate;	
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_salesorderitem", $obj);
					$stmt = $this->db->prepare($cmd->sql);
					$stmt->execute($cmd->params);
					
					
					/* Save di Media Order Item */
					$obj = new \stdClass;
					$obj->mediaorder_id = $header->id;
					$obj->mediaorderitem_id = $line_id;
					$obj->mediaorderitem_descr = $item->description;
					$obj->mediaorderitem_price = $item->price;
					$obj->mediaordertype_id = $item->type_id;
					$obj->itemclass_id = $item->class;
					$obj->_createby = $user_id;
					$obj->_createdate = $currentdate;	
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_mediaorderitem", $obj);
					$stmt = $this->db->prepare($cmd->sql);
					$stmt->execute($cmd->params);					
					
				}



				// showdate: 'yyyy-mm-dd',	
				// showstart: 'hh:ii:ss',
				// showend: 'hh:ii:ss',
				// adsslot_id: '',
				// programme_id : '',
				// programme_name : '',
				// type_id : '',              /*  contoh:  */
				// type_name: ''              /*  BLOCKING NON GOVERNMENT, BARTER NON MEDIA, BLOCKING GOVERNMENT, LOOSE SPOT PAB, dll  */

				$this->db->commit();
			} catch (\Exception $ex) {
				$this->db->rollBack();
				throw $ex;
			} finally {
				$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
			}

			return (object)[
				"success" => 1,
				"errormessage" => "",
				"ref" => "xxxxxxx"
			];
		} catch (\Exception $ex) {
			return (object)[
				"success" => 0,
				"errormessage" => $ex->getMessage(),
				"ref" => null	
			];
		}
	}


	function GetPartnerData($partnerref_code, $name) {
		try {
			$sql = "select partner_id from mst_partnerref where partnerref_code = :partnerref_code";

			$stmt = $this->db->prepare($sql);
			$stmt->execute([':partnerref_code' => $partnerref_code]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);
			if ($row==null) {
				throw new \Exception ("Kode referensi partner [$partnerref_code] untuk '$name' tidak ditemukan.");
			}

			return (object) [
				'partner_id' => $row['partner_id']
			];
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function GetBrandData($brandref_code, $name) {
		try {
			$sql = "select brand_id from mst_brandref where brandref_code = :brandref_code";

			$stmt = $this->db->prepare($sql);
			$stmt->execute([':brandref_code' => $brandref_code]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);
			if ($row==null) {
				throw new \Exception ("Kode referensi brand [$brandref_code] untuk '$name' tidak ditemukan.");
			}

			return (object) [
				'brand_id' => $row['brand_id']
			];
		} catch (\Exception $ex) {
			throw $ex;
		}
	}




};

