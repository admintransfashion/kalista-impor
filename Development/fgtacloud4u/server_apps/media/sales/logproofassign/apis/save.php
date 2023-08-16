<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
// require_once __ROOT_DIR . "/core/sequencer.php";
require_once __DIR__ . '/xapi.base.php';


use \FGTA4\exceptions\WebException;
// use \FGTA4\utils\Sequencer;



/**
 * media/sales/logproofassign/apis/save.php
 *
 * ====
 * Save
 * ====
 * Menampilkan satu baris data/record sesuai PrimaryKey,
 * dari tabel header logproofassign (trn_medialogproofitem)
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 16/12/2021
 */
$API = new class extends logproofassignBase {
	
	public function execute($data, $options) {
		$tablename = 'trn_medialogproofitem';
		$primarykey = 'medialogproofitem_id';
		$autoid = $options->autoid;
		$datastate = $data->_state;

		$userdata = $this->auth->session_get_user();

		try {

			// cek apakah user boleh mengeksekusi API ini
			if (!$this->RequestIsAllowedFor($this->reqinfo, "save", $userdata->groups)) {
				throw new \Exception('your group authority is not allowed to do this action.');
			}

			$result = new \stdClass; 
			
			$key = new \stdClass;
			$obj = new \stdClass;
			foreach ($data as $fieldname => $value) {
				if ($fieldname=='_state') { continue; }
				if ($fieldname==$primarykey) {
					$key->{$fieldname} = $value;
				}
				$obj->{$fieldname} = $value;
			}

			// apabila ada tanggal, ubah ke format sql sbb:
			// $obj->tanggal = (\DateTime::createFromFormat('d/m/Y',$obj->tanggal))->format('Y-m-d');

			$obj->medialogproofitem_id = strtoupper($obj->medialogproofitem_id);
			$obj->mediaadslot_timestart = strtoupper($obj->mediaadslot_timestart);
			$obj->mediaadslot_timeend = strtoupper($obj->mediaadslot_timeend);
			$obj->actual_timestart = strtoupper($obj->actual_timestart);
			$obj->actual_timeend = strtoupper($obj->actual_timeend);
			$obj->actual_duration = strtoupper($obj->actual_duration);


			if ($obj->mediaordertype_id=='') { $obj->mediaordertype_id = '--NULL--'; }
			if ($obj->agency_partner_id=='') { $obj->agency_partner_id = '--NULL--'; }
			if ($obj->advertiser_partner_id=='') { $obj->advertiser_partner_id = '--NULL--'; }
			if ($obj->brand_id=='') { $obj->brand_id = '--NULL--'; }
			if ($obj->project_id=='') { $obj->project_id = '--NULL--'; }
			if ($obj->projecttask_id=='') { $obj->projecttask_id = '--NULL--'; }


			unset($obj->mediaadslot_timestart);
			unset($obj->mediaadslot_timeend);
			unset($obj->mediaadslot_descr);
			unset($obj->actual_timeend);
			unset($obj->actual_duration);
			unset($obj->spot_id);
			unset($obj->mediaorderitem_validr);
			unset($obj->mediaorderitem_ppnidr);
			unset($obj->pph_taxtype_id);
			unset($obj->mediaorder_id);
			unset($obj->mediaorderitem_id);
			unset($obj->billoutpreprocess_id);
			unset($obj->mediaordertype_id);
			unset($obj->logproof_partnerinfo);
			unset($obj->agency_partner_id);
			unset($obj->advertiser_partner_id);
			unset($obj->brand_id);



			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {

				$action = '';
				if ($datastate=='NEW') {
					$action = 'NEW';
					if ($autoid) {
						$obj->{$primarykey} = $this->NewId([]);
					}
					$obj->_createby = $userdata->username;
					$obj->_createdate = date("Y-m-d H:i:s");
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert($tablename, $obj);
				} else {
					$action = 'MODIFY';
					$obj->_modifyby = $userdata->username;
					$obj->_modifydate = date("Y-m-d H:i:s");				
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLUpdate($tablename, $obj, $key);
				}
	
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				\FGTA4\utils\SqlUtility::WriteLog($this->db, $this->reqinfo->modulefullname, $tablename, $obj->{$primarykey}, $action, $userdata->username, (object)[]);




				// result
				$where = \FGTA4\utils\SqlUtility::BuildCriteria((object)[$primarykey=>$obj->{$primarykey}], [$primarykey=>"$primarykey=:$primarykey"]);
				$sql = \FGTA4\utils\SqlUtility::Select($tablename , [
					$primarykey
					, 'medialogproofitem_id', 'mediaadslot_timestart', 'mediaadslot_timeend', 'mediaadslot_descr', 'actual_timestart', 'actual_timeend', 'actual_duration', 'spot_id', 'mediaorderitem_validr', 'mediaorderitem_ppnidr', 'pph_taxtype_id', 'mediaorder_id', 'mediaorderitem_id', 'projbudget_id', 'projbudgettask_id', 'billoutpreprocess_id', 'mediaordertype_id', 'logproof_partnerinfo', 'agency_partner_id', 'advertiser_partner_id', 'brand_id', 'project_id', 'projecttask_id', 'medialogproof_id', '_createby', '_createdate', '_modifyby', '_modifydate', '_createby', '_createdate', '_modifyby', '_modifydate'
				], $where->sql);
				$stmt = $this->db->prepare($sql);
				$stmt->execute($where->params);
				$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			

				$record = [];
				foreach ($row as $key => $value) {
					$record[$key] = $value;
				}
				$result->dataresponse = (object) array_merge($record, [
						//  untuk lookup atau modify response ditaruh disini
					'pph_taxtype_name' => \FGTA4\utils\SqlUtility::Lookup($record['pph_taxtype_id'], $this->db, 'mst_taxtype', 'taxtype_id', 'taxtype_name'),
					'mediaorder_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorder_id'], $this->db, 'trn_mediaorder', 'mediaorder_id', 'mediaorder_descr'),
					'mediaorderitem_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorderitem_id'], $this->db, 'trn_mediaorderitem', 'mediaorderitem_id', 'mediaorderitem_descr'),
					'projbudget_name' => \FGTA4\utils\SqlUtility::Lookup($record['projbudget_id'], $this->db, 'mst_projbudget', 'projbudget_id', 'projbudget_name'),
					'projbudgettask_name' => \FGTA4\utils\SqlUtility::Lookup($record['projbudgettask_id'], $this->db, 'view_projbudgettask', 'projbudgettask_id', 'projbudgettask_name'),
					'billoutpreprocess_name' => \FGTA4\utils\SqlUtility::Lookup($record['billoutpreprocess_id'], $this->db, 'mst_billoutpreprocess', 'billoutpreprocess_id', 'billoutpreprocess_name'),

					'_createby' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'_modifyby' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				]);



				$this->db->commit();
				return $result;

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

	public function NewId($param) {
					return uniqid();
	}

};