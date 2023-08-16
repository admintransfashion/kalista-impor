<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
// require_once __ROOT_DIR . "/core/sequencer.php";
require_once __DIR__ . '/xapi.base.php';

if (is_file(__DIR__ .'/data-header-handler.php')) {
	require_once __DIR__ .'/data-header-handler.php';
}


use \FGTA4\exceptions\WebException;
// use \FGTA4\utils\Sequencer;



/**
 * community/tfi/allotopup/apis/save.php
 *
 * ====
 * Save
 * ====
 * Menampilkan satu baris data/record sesuai PrimaryKey,
 * dari tabel header allotopup (trn_allotopup)
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 27/01/2023
 */
$API = new class extends allotopupBase {
	
	public function execute($data, $options) {
		$event = 'on-save';
		$tablename = 'trn_allotopup';
		$primarykey = 'allotopup_id';
		$autoid = $options->autoid;
		$datastate = $data->_state;
		$userdata = $this->auth->session_get_user();

		$handlerclassname = "\\FGTA4\\apis\\allotopup_headerHandler";
		$hnd = null;
		if (class_exists($handlerclassname)) {
			$hnd = new allotopup_headerHandler($options);
			$hnd->caller = &$this;
			$hnd->db = &$this->db;
			$hnd->auth = $this->auth;
			$hnd->reqinfo = $this->reqinfo;
			$hnd->event = $event;
		} else {
			$hnd = new \stdClass;
		}

		try {

			// cek apakah user boleh mengeksekusi API ini
			if (!$this->RequestIsAllowedFor($this->reqinfo, "save", $userdata->groups)) {
				throw new \Exception('your group authority is not allowed to do this action.');
			}

			if (method_exists(get_class($hnd), 'init')) {
				// init(object &$options) : void
				$hnd->init($options);
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
			$obj->allotopup_date = (\DateTime::createFromFormat('d/m/Y',$obj->allotopup_date))->format('Y-m-d');



			if ($obj->allotopup_name=='') { $obj->allotopup_name = '--NULL--'; }
			if ($obj->allotopup_email=='') { $obj->allotopup_email = '--NULL--'; }
			if ($obj->allotopup_phone=='') { $obj->allotopup_phone = '--NULL--'; }
			if ($obj->allotopup_clientref=='') { $obj->allotopup_clientref = '--NULL--'; }
			if ($obj->allotopup_txid=='') { $obj->allotopup_txid = '--NULL--'; }
			if ($obj->allotopup_nonce=='') { $obj->allotopup_nonce = '--NULL--'; }
			if ($obj->allotopup_timestamp=='') { $obj->allotopup_timestamp = '--NULL--'; }
			if ($obj->allotopup_barcode=='') { $obj->allotopup_barcode = '--NULL--'; }
			if ($obj->allotopup_alloref=='') { $obj->allotopup_alloref = '--NULL--'; }
			if ($obj->allotopup_status=='') { $obj->allotopup_status = '--NULL--'; }
			if ($obj->allotopup_message=='') { $obj->allotopup_message = '--NULL--'; }


			unset($obj->allotopup_clientref);
			unset($obj->allotopup_txid);
			unset($obj->allotopup_nonce);
			unset($obj->allotopup_timestamp);
			unset($obj->allotopup_barcode);
			unset($obj->allotopup_alloref);
			unset($obj->allotopup_status);
			unset($obj->allotopup_message);
			unset($obj->allotopup_isdone);
			unset($obj->allotopup_isgen);
			unset($obj->allotopup_genby);
			unset($obj->allotopup_gendate);


			// current user & timestamp	
			if ($datastate=='NEW') {
				$obj->_createby = $userdata->username;
				$obj->_createdate = date("Y-m-d H:i:s");
			} else {
				$obj->_modifyby = $userdata->username;
				$obj->_modifydate = date("Y-m-d H:i:s");	
			}

			//handle data sebelum sebelum save
			if (method_exists(get_class($hnd), 'DataSaving')) {
				// ** DataSaving(object &$obj, object &$key) : void
				$hnd->DataSaving($obj, $key);
			}

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {

				$action = '';
				if ($datastate=='NEW') {
					$action = 'NEW';
					if ($autoid) {
						$obj->{$primarykey} = $this->NewId($hnd, $obj);
					}
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert($tablename, $obj);
				} else {
					$action = 'MODIFY';
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLUpdate($tablename, $obj, $key);
				}
	
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				\FGTA4\utils\SqlUtility::WriteLog($this->db, $this->reqinfo->modulefullname, $tablename, $obj->{$primarykey}, $action, $userdata->username, (object)[]);




				// result
				$options->criteria = [
					"allotopup_id" => $obj->allotopup_id
				];

				$criteriaValues = [
					"allotopup_id" => " allotopup_id = :allotopup_id "
				];
				if (method_exists(get_class($hnd), 'buildOpenCriteriaValues')) {
					// buildOpenCriteriaValues(object $options, array &$criteriaValues) : void
					$hnd->buildOpenCriteriaValues($options, $criteriaValues);
				}

				$where = \FGTA4\utils\SqlUtility::BuildCriteria($options->criteria, $criteriaValues);
				$result = new \stdClass; 
	
				if (method_exists(get_class($hnd), 'prepareOpenData')) {
					// prepareOpenData(object $options, $criteriaValues) : void
					$hnd->prepareOpenData($options, $criteriaValues);
				}

				$sqlFieldList = [
					'allotopup_id' => 'A.`allotopup_id`', 'site_id' => 'A.`site_id`', 'allotopup_date' => 'A.`allotopup_date`', 'allotopup_name' => 'A.`allotopup_name`',
					'allotopup_email' => 'A.`allotopup_email`', 'allotopup_phone' => 'A.`allotopup_phone`', 'allotopup_validr' => 'A.`allotopup_validr`', 'allotopup_clientref' => 'A.`allotopup_clientref`',
					'allotopup_txid' => 'A.`allotopup_txid`', 'allotopup_nonce' => 'A.`allotopup_nonce`', 'allotopup_timestamp' => 'A.`allotopup_timestamp`', 'allotopup_barcode' => 'A.`allotopup_barcode`',
					'allotopup_alloref' => 'A.`allotopup_alloref`', 'allotopup_status' => 'A.`allotopup_status`', 'allotopup_message' => 'A.`allotopup_message`', 'allotopup_isdone' => 'A.`allotopup_isdone`',
					'allotopup_isgen' => 'A.`allotopup_isgen`', 'allotopup_genby' => 'A.`allotopup_genby`', 'allotopup_gendate' => 'A.`allotopup_gendate`', '_createby' => 'A.`_createby`',
					'_createby' => 'A.`_createby`', '_createdate' => 'A.`_createdate`', '_modifyby' => 'A.`_modifyby`', '_modifydate' => 'A.`_modifydate`'
				];
				$sqlFromTable = "trn_allotopup A";
				$sqlWhere = $where->sql;
					
				if (method_exists(get_class($hnd), 'SqlQueryOpenBuilder')) {
					// SqlQueryOpenBuilder(array &$sqlFieldList, string &$sqlFromTable, string &$sqlWhere, array &$params) : void
					$hnd->SqlQueryOpenBuilder($sqlFieldList, $sqlFromTable, $sqlWhere, $where->params);
				}
				$sqlFields = \FGTA4\utils\SqlUtility::generateSqlSelectFieldList($sqlFieldList);
	
			
				$sqlData = "
					select 
					$sqlFields 
					from 
					$sqlFromTable 
					$sqlWhere 
				";
	
				$stmt = $this->db->prepare($sqlData);
				$stmt->execute($where->params);
				$row  = $stmt->fetch(\PDO::FETCH_ASSOC);
	
				$record = [];
				foreach ($row as $key => $value) {
					$record[$key] = $value;
				}

				$dataresponse = array_merge($record, [
					//  untuk lookup atau modify response ditaruh disini
					'site_name' => \FGTA4\utils\SqlUtility::Lookup($record['site_id'], $this->db, 'mst_site', 'site_id', 'site_name'),
					'allotopup_date' => date("d/m/Y", strtotime($row['allotopup_date'])),
					'allotopup_genby' => \FGTA4\utils\SqlUtility::Lookup($record['allotopup_genby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),

					'_createby' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'_modifyby' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				]);
				
				if (method_exists(get_class($hnd), 'DataOpen')) {
					//  DataOpen(array &$record) : void 
					$hnd->DataOpen($dataresponse);
				}

				$result->dataresponse = (object) $dataresponse;
				if (method_exists(get_class($hnd), 'DataSavedSuccess')) {
					$hnd->DataSavedSuccess($result);
				}

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

	public function NewId(object $hnd, object $obj) : string {
		// dipanggil hanya saat $autoid == true;

		$id = null;
		$handled = false;
		if (method_exists(get_class($hnd), 'CreateNewId')) {
			// CreateNewId(object $obj) : string 
			$id = $hnd->CreateNewId($obj);
			$handled = true;
		}

		if (!$handled) {
			$id = uniqid();
		}

		return $id;
	}

};