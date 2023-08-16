<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';

require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;


$API = new class extends mediaorderBase {

	public function execute($dept_id, $data) {
		$userdata = $this->auth->session_get_user();


		// $var = json_encode($data);
		$this->log($dept_id);
		// $dummydata = '[{"TglTerimaMO":"2021-10-01 00:00:00.000","TglMediaOrder":"2021-10-11 00:00:00.000","MediaOrderId":"1111","MediaOrderRef":"001\/Mindshare\/PO\/X\/2021 Lokal PBB ^","TrafficId":"xxxxx","Status":"MO","Direct":"No","Bundling":"No","TypeMediaOrder":"Blocking","MediaOrderDescr":"Bloking Kabar Malam","AgencyId":"210966","AgencyName":"Wira Pamungkas Pariwara","AdvertiserId":"210919","AdvertiserName":"Ultra Sakti","BrandId":"212819","BrandName":"Susu Coklat","Value":50000000,"JumlahSpot":4},{"TglTerimaMO":"2021-10-05 00:00:00.000","TglMediaOrder":"2021-10-15 00:00:00.000","MediaOrderId":"1143","MediaOrderRef":"011\/Mindshare\/PO\/X\/2021 Lokal PBB ^","TrafficId":"","Status":"Add","Direct":"No","Bundling":"No","TypeMediaOrder":"Retail","MediaOrderDescr":"Bloking Kabar Malam","AgencyId":"210966","AgencyName":"Wira Pamungkas Pariwara","AdvertiserId":"210919","AdvertiserName":"Ultra Sakti","BrandId":"212819","BrandName":"Susu Coklat","Value":10000000,"JumlahSpot":1},{"TglTerimaMO":"2021-10-06 00:00:00.000","TglMediaOrder":"2021-10-15 00:00:00.000","MediaOrderId":"1234","MediaOrderRef":"7591.050.233 - 7591.055.2106 ^","TrafficId":"","Status":"MO","Direct":"No","Bundling":"No","TypeMediaOrder":"Retail","MediaOrderDescr":"DAMAI INDONESIAKU","AgencyId":"210426","AgencyName":"INTER PARIWARA GLOBAL, PT","AdvertiserId":"210378","AdvertiserName":"HANJAYA MANDALA SAMPOERNA TBK","BrandId":"212214","BrandName":"A ULTRAMILD","Value":25000000,"JumlahSpot":5}]';
		// $data = json_decode($dummydata);



		try {
			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();


			$interface_id = 'LTS';

			$sql_cek_orderintype = "
				select A.orderintype_id from 
				mst_orderintype A inner join mst_orderintyperef B on B.orderintype_id = A.orderintype_id
				WHERE 
				B.interface_id = :interface_id
				and B.orderintyperef_code = :orderintyperef_code		
			";

			$sql_cek_partner = "
				select A.partner_id 
				from mst_partner A inner join mst_partnerref B on B.partner_id =A.partner_id 
				WHERE 
				B.interface_id = :interface_id
				and B.partnerref_code  = :partnerref_code
			";

			$sql_cek_brand = "
				select A.brand_id 
				from mst_brand A inner join mst_brandref B on B.brand_id =A.brand_id 
				WHERE 
				B.interface_id = :interface_id
				and B.brandref_code  = :brandref_code
			";


			$stmts = (object)[
				'cek_orderintype' => $this->db->prepare($sql_cek_orderintype),
				'cek_partner' =>  $this->db->prepare($sql_cek_partner),
				'cek_brand' =>  $this->db->prepare($sql_cek_brand),
			];

			try {



				$errors = [];

				// Cek Data dulu
				$i = 0;
				foreach ($data as $itemrow) {
					$i++;
					$obj = new \stdClass;
					// $this->log($itemrow);


					// cek orderin type
					$TypeMediaOrder = $itemrow->TypeMediaOrder;
					$stmt = $stmts->cek_orderintype;
					$stmt->execute([':interface_id'=>$interface_id,  ':orderintyperef_code'=>$TypeMediaOrder ]);
					$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
					if (count($rows)==0) {
						$errors[] = "Baris $i; Referensi interface tipe media order '$TypeMediaOrder' belum di definisikan pada master OrderInType ";
					}
					$orderintype_id = $rows[0]['orderintype_id'];

					// cek agency
					$AgencyId = $itemrow->AgencyId;
					$stmt = $stmts->cek_partner;
					$stmt->execute([':interface_id'=>$interface_id,  ':partnerref_code'=>$AgencyId ]);
					$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
					if (count($rows)==0) {
						$errors[] = "Baris $i; Referensi interface agency '$AgencyId' belum di definisikan pada master Partner ";
					}
					$agency_partner_id = $rows[0]['partner_id'];


					// Cek advertiser	
					$AdvertiserId = $itemrow->AdvertiserId;
					$stmt = $stmts->cek_partner;
					$stmt->execute([':interface_id'=>$interface_id,  ':partnerref_code'=>$AdvertiserId ]);
					$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
					if (count($rows)==0) {
						$errors[] = "Baris $i; Referensi interface advertiser '$AgencyId' belum di definisikan pada master Partner ";
					}
					$advertiser_partner_id = $rows[0]['partner_id'];
					// $this->log($advertiser_partner_id);


					// Cek brand
					$BrandId = $itemrow->BrandId;
					$stmt = $stmts->cek_brand;
					$stmt->execute([':interface_id'=>$interface_id,  ':brandref_code'=>$BrandId ]);
					$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
					if (count($rows)==0) {
						$errors[] = "Baris $i; Referensi interface brand '$AgencyId' belum di definisikan pada master Brand ";
					}
					$brand_id = $rows[0]['brand_id'];
					

					$itemrow->dept_id = $dept_id;
					$itemrow->orderintype_id = $orderintype_id;
					$itemrow->agency_partner_id = $agency_partner_id;
					$itemrow->advertiser_partner_id = $advertiser_partner_id;
					$itemrow->brand_id = $brand_id;

					$this->CreateMediaOrder($itemrow, $userdata);
				}


				$this->db->commit();
				return (object)[
					'success' => count($errors)>0 ? false : true,
					'errormessage' => count($errors)>0 ? 'Ada satu atau lebih data yang belum di mapping' : '',
					'errors' => $errors
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


	function CreateMediaOrder($itemrow, $userdata) {
		try {
			$sql = "select * from mst_orderintype where orderintype_id = :orderintype_id  ";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':orderintype_id'=>$itemrow->orderintype_id]);
			$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
			$orderintype = (object)$rows[0]; 
			// $this->log($itemrow);


			$obj = new \stdClass;
			$obj->mediaorder_id	= $itemrow->MediaOrderId;
			$obj->orderintype_id = $itemrow->orderintype_id;
			$obj->mediaorder_date = $itemrow->TglMediaOrder;
			$obj->mediaorder_descr = $itemrow->MediaOrderDescr;
			$obj->mediaorder_ref = $itemrow->MediaOrderRef;
			$obj->dept_id	= $itemrow->dept_id;
			$obj->agency_partner_id	= $itemrow->agency_partner_id ;
			$obj->advertiser_partner_id	= $itemrow->advertiser_partner_id ;
			$obj->mediaorder_traffic = $itemrow->TrafficId ;
			$obj->mediaorder_status	= $itemrow->Status ;
			$obj->mediaorder_direct	= $itemrow->Direct ;
			$obj->mediaorder_bundling = $itemrow->Bundling ;
			$obj->orderin_totalqty = $itemrow->JumlahSpot;	
			$obj->orderin_totalitem	= $itemrow->JumlahSpot;
			$obj->orderin_salesgross = $itemrow->Value;
			
			
			$obj->orderin_discount	= 0;
			$obj->orderin_subtotal	= $itemrow->Value;
			$obj->orderin_pph	= 0;
			$obj->orderin_nett	=$itemrow->Value;
			$obj->orderin_ppn= 0;	
			$obj->orderin_total	= $itemrow->Value;
			$obj->orderin_totaladdcost= 0;	
			$obj->orderin_payment = $itemrow->Value;	
		
			$obj->arunbill_coa_id = $orderintype->arunbill_coa_id;	
			$obj->ar_coa_id = $orderintype->ar_coa_id;
			$obj->dp_coa_id = $orderintype->dp_coa_id;
			$obj->sales_coa_id = $orderintype->sales_coa_id;
			$obj->salesdisc_coa_id = $orderintype->salesdisc_coa_id;
			$obj->ppn_coa_id = $orderintype->ppn_coa_id;
			$obj->ppnsubsidi_coa_id = $orderintype->ppnsubsidi_coa_id;
			$obj->pph_coa_id = $orderintype->pph_coa_id;
			$obj->trxmodel_id = $orderintype->trxmodel_id;
			$obj->doc_id = 'MEDIAORDER';


			$sql = "select * from trn_mediaorder where mediaorder_id = :mediaorder_id";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':mediaorder_id'=> $obj->mediaorder_id]);
			$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
		
			$tablename = "trn_mediaorder";
			if (count($rows)>0) {
				// update
				$key = new \stdClass;
				$key->mediaorder_id = $obj->mediaorder_id;

				$obj->_modifyby = $userdata->username;
				$obj->_modifydate = date("Y-m-d H:i:s");				
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLUpdate($tablename, $obj, $key);

			} else {
				// insert
				$obj->_createby = $userdata->username;
				$obj->_createdate = date("Y-m-d H:i:s");
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert($tablename, $obj);
			}

			$stmt = $this->db->prepare($cmd->sql);
			$stmt->execute($cmd->params);
	


		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	

	
};


