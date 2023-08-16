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
				B.interface_id = ''
				and B.brandref_code  = ''
			";


			$stmts = (object)[
				'cek_orderintype' => $this->db->prepare($sql_cek_orderintype),
				'sql_cekpartner' =>  $this->db->prepare($sql_cek_partner),
				'cek_brand' =>  $this->db->prepare($sql_cek_brand),
			];

			try {



				$errors = [];

				// Cek Data dulu
				$i = 0;
				foreach ($data as $itemrow) {
					$i++;
					$obj = new \stdClass;
					$this->log($itemrow);


					throw new \Exception ("baris $i; Referensi interface partner '".  $itemrow->AgencyName ."' belum didefinisiakn");

					// cek orderin type
					// $TypeMediaOrder = $itemrow['TypeMediaOrder'];
					// $stmt = $stmts->cek_orderintype;
					// $stmt->execute([':interface_id'=>$interface_id,  ':orderintyperef_code'=>$TypeMediaOrder ]);
					// $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
					// if (count($rows)==0) {
					// 	$errors[] = "Baris $i; Referensi interface tipe media order '$TypeMediaOrder' belum di definisikan pada master OrderInType ";
					// }
					// $orderintype_id = $rows[0]['orderintype_id'];

					


					// $this->CreateMediaOrder($itemrow, $userdata, $stmts);
				}
	
				$this->db->commit();
				return (object)[
					'success' => count($errors)>0 ? false : true,
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

	}


	

	
};


