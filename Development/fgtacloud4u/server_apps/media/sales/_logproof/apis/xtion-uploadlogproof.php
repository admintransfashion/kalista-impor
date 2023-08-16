<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}


require_once __ROOT_DIR.'/core/debug.php';
require_once __ROOT_DIR.'/core/sqlutil.php';
// require_once __ROOT_DIR . "/core/sequencer.php";


use \FGTA4\debug;
use \FGTA4\exceptions\WebException;
// use \FGTA4\utils\Sequencer;



$API = new class extends WebAPI {
	function __construct() {
		$logfilepath = __LOCALDB_DIR . "/output/logproof-upload.txt";
		// debug::disable();
		debug::start($logfilepath, "w");

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


	public function execute($data) {
		// data
		// No : No,
		// Agency : Agency,
		// Advertiser : Advertiser,
		// Brand : Brand,
		// OrderId : OrderId,
		// OrderName : OrderName,
		// EndDate : EndDate,
		// Aired : Aired,
		// NettInvoice : nettValue,
		// TypePayment : TypePayment,

		$userdata = $this->auth->session_get_user();
		$this->userdata = $userdata;

		try {
			
			$brand_id = $this->getBrandId($data->Brand);
			$agency_id = $this->getAgencyId($data->Agency);
			$advertiser_id = $this->getAdvertiser($data->Advertiser);
			$mediaordertype_id = $this->getMediaorderType($data->TypePayment);
			

			$rel = (object) [
				'brand_id' => $brand_id,
				'agency_id' => $agency_id,
				'advertiser_id' => $advertiser_id,
				'mediaordertype_id' => $mediaordertype_id,
			];


			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {
				$mediaorderitem_id = $this->InsertMediaorderItem($data, $rel);
				$rel->mediaorderitem_id = $mediaorderitem_id;

				$medialogproof_id = $this->InsertLogProof($data, $rel);	
				$rel->medialogproof_id = $medialogproof_id;

				$this->InsertLogProofItem($data, $rel);	


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


	public function getBrandId($brandname) {
		$userdata = $this->userdata;

		try {
			$sql = "select brand_id from mst_brand where brand_name=:brand_name";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':brand_name'=>$brandname]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row!=null) {
				return $row['brand_id'];
			} else {
				debug::log("brand $brandname belum ada");
				$obj = (object)[
					"brand_id" => uniqid(),
					"brand_name" => $brandname,
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("mst_brand", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				return $obj->brand_id;
			}
		} catch (\Exception $ex) {
			throw $ex;
		}			
	}

	public function getAgencyId($agencyname) {
		$userdata = $this->userdata;
		$partnerorg_id = 'PERSH';
		$partnertype_id = 'AGENCY';
		$partner_country = 'ID';

		try {
			$sql = "select partner_id from mst_partner where partner_name=:partner_name";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':partner_name'=>$agencyname]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row!=null) {
				return $row['partner_id'];
			} else {
				debug::log("agency $agencyname belum ada di data partner");
				$obj = (object)[
					"partner_id" => uniqid(),
					"partner_name" => $agencyname,
					"partner_addressline1" => "",
					"partner_addressline2" => "",
					"partner_postcode" => "",
					"partner_city" => "",
					"partner_country" => $partner_country,
					"partner_mobilephone" => "",
					"partner_phone" => "",
					"partner_email" => "",
					"partnertype_id" => $partnertype_id,
					"partnerorg_id" => $partnerorg_id,
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("mst_partner", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				return $obj->partner_id;
			}

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}

	public function getAdvertiser($advertisername) {
		$userdata = $this->userdata;
		$partnerorg_id = 'PERSH';
		$partnertype_id = 'ADVERTISER';
		$partner_country = 'ID';

		try {
			$sql = "select partner_id from mst_partner where partner_name=:partner_name";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':partner_name'=>$advertisername]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row!=null) {
				return $row['partner_id'];
			} else {
				debug::log("advertiser $advertisername belum ada di data partner");
				$obj = (object)[
					"partner_id" => uniqid(),
					"partner_name" => $advertisername,
					"partner_addressline1" => "",
					"partner_addressline2" => "",
					"partner_postcode" => "",
					"partner_city" => "",
					"partner_country" => $partner_country,
					"partner_mobilephone" => "",
					"partner_phone" => "",
					"partner_email" => "",
					"partnertype_id" => $partnertype_id,
					"partnerorg_id" => $partnerorg_id,
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("mst_partner", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				return $obj->partner_id;
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function getMediaorderType($mediaordertype_name) {
		$userdata = $this->userdata;

		try {
			$sql = "select mediaordertype_id from mst_mediaordertype where mediaordertype_name =:mediaordertype_name";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':mediaordertype_name'=>$mediaordertype_name]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row!=null) {
				return $row['mediaordertype_id'];
			} else {
				debug::log("mediaordertype_name $mediaordertype_name belum ada di master type mediaorder");
				throw new \Exception("mediaordertype_name $mediaordertype_name belum ada di master type mediaorder");
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function InsertMediaorderItem($data, $rel) {
		$userdata = $this->userdata;
		$mediaorder_date = (\DateTime::createFromFormat('d/m/Y',$data->EndDate))->format('Y-m-d');

		try {

			$sql = "select salesorder_id from trn_salesorder where salesorder_id = :salesorder_id";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':salesorder_id'=>$data->OrderId]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);
			if ($row==null) {
				debug::log("Insert Sales Order $data->OrderId");
				$obj = (object)[
					"salesorder_id" => $data->OrderId,
					"salesorder_descr" => $data->OrderName,
					"salesorder_date" =>  $mediaorder_date,
					"salesorder_iscommit" => 1,
					"salesorder_commitby" => $userdata->username,
					"salesorder_commitdate" => date("Y-m-d H:i:s"),
					"salesordertype_id" => "MO",
					"partner_id" => $rel->agency_id,
					"doc_id" => 'MEDIAORDER',
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")

				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_salesorder", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}


			$sql = "select mediaorder_id from trn_mediaorder where mediaorder_id = :mediaorder_id";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':mediaorder_id'=>$data->OrderId]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row==null) {

				debug::log("Insert Media Order $data->OrderId");
				$obj = (object)[
					"mediaorder_id" => $data->OrderId,
					"mediaorder_descr" => $data->OrderName,
					"mediaorder_date" => $mediaorder_date,
					"mediaorder_iscommit" => 1,
					"mediaorder_commitby" => $userdata->username,
					"mediaorder_commitdate" => date("Y-m-d H:i:s"),
					"mediaordertype_id" => $rel->mediaordertype_id,
					"agency_partner_id" => $rel->agency_id,
					"advertiser_partner_id" => $rel->advertiser_id,
					"brand_id" => $rel->brand_id,
					"salesordertype_id" => 'MO',
					"doc_id" => 'MEDIAORDER',
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_mediaorder", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			} 




			// insert to item
			$salesorderitem_descr = $data->Brand. " (". $data->Aired .")";
			$sql = "select salesorderitem_id from trn_salesorderitem where salesorder_id = :salesorder_id and salesorderitem_descr = :salesorderitem_descr";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':salesorder_id'=>$data->OrderId, ':salesorderitem_descr' => $salesorderitem_descr]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);		
			if ($row==null) {
				debug::log("Insert Sales Order Item $data->OrderId $salesorderitem_descr");
				$obj = (object)[
					"salesorderitem_id" => uniqid(),
					"salesorder_id" => $data->OrderId,
					"salesorderitem_descr" => $salesorderitem_descr,
					"salesorderitem_price" => $data->NettInvoice, 
					"salesorderitem_qty" => 1,
					"salesorderitem_pricesubtotal" => $data->NettInvoice, 
					"itemclass_id" => 'ADS',
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_salesorderitem", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}



			// insert to item
			$mediaorderitem_descr = $data->Brand. " (". $data->Aired .")";
			$sql = "select mediaorderitem_id from trn_mediaorderitem where mediaorder_id = :mediaorder_id and mediaorderitem_descr = :mediaorderitem_descr";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':mediaorder_id'=>$data->OrderId, ':mediaorderitem_descr' => $mediaorderitem_descr]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);		
			if ($row!=null) {
				return $row['mediaorderitem_id'];
			} else {
				debug::log("Insert Media Order Item $data->OrderId $mediaorderitem_descr");
				$obj = (object)[
					"mediaorderitem_id" => uniqid(),
					"mediaorder_id" => $data->OrderId,
					"mediaorderitem_descr" => $mediaorderitem_descr,
					"mediaorderitem_price" => $data->NettInvoice, 
					"itemclass_id" => 'ADS',
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_mediaorderitem", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				return $obj->mediaorderitem_id;
			}

		} catch (\Exception $ex) {
			throw $ex;
		}

	}


	function InsertLogProof($data, $rel) {
		$userdata = $this->userdata;
		$medialogproof_date = (\DateTime::createFromFormat('d/m/Y',$data->EndDate))->format('Y-m-d');

		try {
			$sql = "select medialogproof_id, medialogproof_iscommit	 from trn_medialogproof where medialogproof_date = :medialogproof_date";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':medialogproof_date'=>$medialogproof_date]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row!=null) {
				$iscommit = $row['medialogproof_iscommit'];
				if ($iscommit) {
					throw ("Logproof tanggal $medialogproof_date sudah di commit.");
				}

				return $row['medialogproof_id'];
			} else {
				debug::log("logproof tanggal $medialogproof_date belum ada di data partner");

				$obj = (object)[
					"medialogproof_id" => uniqid(),
					"medialogproof_date" => $medialogproof_date,
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_medialogproof", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				return $obj->medialogproof_id;
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	function InsertLogProofItem($data, $rel) {
		$userdata = $this->userdata;

		$mediaadslot_descr = $data->Brand. " (". $data->Aired .")";

		try {
			$sql = "select medialogproofitem_id from trn_medialogproofitem where medialogproof_id = :medialogproof_id and mediaorderitem_id = :mediaorderitem_id ";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':medialogproof_id'=>$rel->medialogproof_id, ':mediaorderitem_id' => $rel->mediaorderitem_id]);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			
			if ($row==null) {
				debug::log("Insert Logproof Item $data->OrderId");
				
				$obj = (object)[
					"medialogproofitem_id" => uniqid(),
					"mediaadslot_timestart" => "00:00:00",
					"mediaadslot_timeend" => "00:00:00",
					"mediaadslot_descr" => 	$mediaadslot_descr,
					"actual_timestart" => "00:00:00",
					"actual_timeend" => "00:00:00",
					"actual_duration" => 0,
					"mediaorderitem_id" => $rel->mediaorderitem_id,
					"medialogproof_id" => $rel->medialogproof_id,
					"_createby" => $userdata->username,
					"_createdate" => date("Y-m-d H:i:s")
				];
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_medialogproofitem", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}
		} catch (\Exception $ex) {
			throw $ex;
		}		

	}

};