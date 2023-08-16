<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';

require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;


$API = new class extends logproofBase {

	public function execute($id) {
		$userdata = $this->auth->session_get_user();

		try {
			$currentdata = (object)[
				'header' => $this->get_header_row($id),
				'user' => $userdata
			];

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {

				$sql = "select * from trn_medialogproofupload where medialogproof_id = :id";
				$stmt = $this->db->prepare($sql);
				$stmt->execute([ ":id" => $id ]);
				$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);	
				foreach ($rows as $row) {

					// $this->log($row);

					throw new \Exception("Referensi interface untuk partner '". $row['agency_name'] ."' belum didefinisikan");
					// $mo = $this->CreateMediaOrder($row, $currentdata);
					// $mediaorder_id = $mo['mediaorder_id'];

					// $modet = $this->CreateMediaOrderDetil($mediaorder_id, $row, $currentdata);


				}

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


	function CreateMediaOrder($data, $currentdata) {
		try {
			$sql = "select * from trn_mediaorder where mediaorder_ref = :ref";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([ ":ref" => trim($data['mediaorder_ref']) ]);		
			$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);	
			

			$mediaorder_id = '';
			if (count($rows)>0) {
				// data sudah ada
				$mediaorder_id = $rows[0]['mediaorder_id'];
			} else {
				// data belum ada
				$mediaorder_id = \uniqid();

				$obj = new \stdClass;
				$obj->mediaorder_id = $mediaorder_id;
				$obj->mediaorder_ref = trim($data['mediaorder_ref']);
				$obj->mediaorder_descr = $data['mediaorder_descr'];
				$obj->_createby =  $currentdata->user->username;
				$obj->_createdate =  date("Y-m-d H:i:s");
				
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('trn_mediaorder', $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}


			return [
				'mediaorder_id'=> $mediaorder_id
			];

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function CreateMediaOrderDetil($mediaorder_id, $data, $currentdata) {
		try {
			$sql = "select * from trn_mediaorderitem where mediaorder_id = :mediaorder_id and mediaorderitem_ref = :ref";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([ 
				":mediaorder_id" => $mediaorder_id,
				":ref" => trim($data['spot_id']) 
			]);		
			$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);	
			
			$mediaorderitem_id = '';
			if (count($rows)>0) {
				$mediaorderitem_id = $rows[0]['mediaorderitem_id'];
			} else {
				$mediaorderitem_id = \uniqid();

				$obj = new \stdClass;
				$obj->mediaorderitem_id = $mediaorderitem_id;
				$obj->mediaorderitem_ref = trim($data['spot_id']);
				$obj->mediaorderitem_descr = $data['mediaorder_descr'];
				$obj->curr_id = 'IDR';
				$obj->mediaorderitem_valfrg = $data['medialogproof_validr'];
				$obj->mediaorderitem_valfrgrate = 1;
				$obj->mediaorderitem_validr = $data['medialogproof_validr'];
				$obj->mediaorderitem_totalidr = $data['medialogproof_validr'];
				$obj->mediaorder_id = $mediaorder_id;
				
				$obj->_createby =  $currentdata->user->username;
				$obj->_createdate =  date("Y-m-d H:i:s");
				
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert('trn_mediaorderitem', $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);


			}


		} catch (\Exception $ex) {
			throw $ex;
		}		
	}


	
};


