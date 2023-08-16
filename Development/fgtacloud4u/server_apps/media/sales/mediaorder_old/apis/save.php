<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR . "/core/sequencer.php";

use \FGTA4\exceptions\WebException;
use \FGTA4\utils\Sequencer;



// /* Enable Debugging */
require_once __ROOT_DIR.'/core/debug.php';
use \FGTA4\debug;


class DataSave extends WebAPI {
	function __construct() {
		$logfilepath = __LOCALDB_DIR . "/output/mediaorder-save.txt";
		// debug::disable();
		// debug::start($logfilepath, "w");

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
	
	public function execute($data, $options) {
		$tablename = 'trn_mediaorder';
		$primarykey = 'mediaorder_id';
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
			$sokey = new \stdClass;

			$obj = new \stdClass;
			$soobj = new \stdClass;

			foreach ($data as $fieldname => $value) {
				if ($fieldname=='_state') { continue; }
				if ($fieldname==$primarykey) {
					$key->{$fieldname} = $value;
				}
				$obj->{$fieldname} = $value;
			}
			


			unset($obj->mediaorder_iscommit);
			unset($obj->mediaorder_commitby);
			unset($obj->mediaorder_commitdate);
			unset($obj->mediaorder_iscompleted);
			unset($obj->mediaorder_completedate);
			unset($obj->mediaorder_isbilled);
			unset($obj->mediaorder_billdate);
			unset($obj->mediaorder_isclosed);
			unset($obj->mediaorder_closeby);
			unset($obj->mediaorder_closedate);

			// apabila ada tanggal, ubah ke format sql sbb:
			// $obj->tanggal = (\DateTime::createFromFormat('d/m/Y',$obj->tanggal))->format('Y-m-d');
			$obj->mediaorder_date = (\DateTime::createFromFormat('d/m/Y',$obj->mediaorder_date))->format('Y-m-d');
			$obj->mediaorder_id = strtoupper($obj->mediaorder_id);
			$obj->mediaorder_descr = strtoupper($obj->mediaorder_descr);
			$obj->mediaordertype_id = strtoupper($obj->mediaordertype_id);
			$obj->brand_id = strtoupper($obj->brand_id);
			$obj->salesordertype_id = strtoupper($obj->salesordertype_id);
			$obj->doc_id = strtoupper($obj->doc_id);


			$soobj->salesorder_descr = $obj->mediaorder_descr;
			$soobj->salesorder_date = $obj->mediaorder_date;
			$soobj->salesordertype_id = $obj->salesordertype_id;	
			$soobj->partner_id = $obj->agency_partner_id;
			$soobj->doc_id = $obj->doc_id; 


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
					
					$soobj->salesorder_id = $obj->{$primarykey};
					$soobj->_createby = $obj->_createby;
					$soobj->_createdate = $obj->_createdate;
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert($tablename, $obj);
					$socmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_salesorder", $soobj);
				} else {
					$action = 'MODIFY';
					$obj->_modifyby = $userdata->username;
					$obj->_modifydate = date("Y-m-d H:i:s");				
					$cmd = \FGTA4\utils\SqlUtility::CreateSQLUpdate($tablename, $obj, $key);

					$soobj->salesorder_id = $key->{$primarykey};
					$sokey->salesorder_id = $key->{$primarykey};
					$socmd = \FGTA4\utils\SqlUtility::CreateSQLUpdate("trn_salesorder", $soobj, $sokey);
				}
	
				//debug::log($socmd->sql);
				$stmt = $this->db->prepare($socmd->sql);
				$stmt->execute($socmd->params);

				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				\FGTA4\utils\SqlUtility::WriteLog($this->db, "finact/sales/order", "trn_salesorder", $obj->{$primarykey}, $action, $userdata->username, (object)[]);
				\FGTA4\utils\SqlUtility::WriteLog($this->db, $this->reqinfo->modulefullname, $tablename, $obj->{$primarykey}, $action, $userdata->username, (object)[]);

				$this->db->commit();
			} catch (\Exception $ex) {
				$this->db->rollBack();
				throw $ex;
			} finally {
				$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
			}


			$where = \FGTA4\utils\SqlUtility::BuildCriteria((object)[$primarykey=>$obj->{$primarykey}], [$primarykey=>"$primarykey=:$primarykey"]);
			$sql = \FGTA4\utils\SqlUtility::Select($tablename , [
				$primarykey, 'mediaorder_id', 'mediaorder_descr', 'mediaorder_date', 'mediaordertype_id', 'agency_partner_id', 'advertiser_partner_id', 'brand_id', 'mediapackage_id', 'salesordertype_id', 'doc_id', '_createby', '_createdate', '_modifyby', '_modifydate', '_createby', '_createdate', '_modifyby', '_modifydate'
			], $where->sql);
			$stmt = $this->db->prepare($sql);
			$stmt->execute($where->params);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);			

			$dataresponse = [];
			foreach ($row as $key => $value) {
				$dataresponse[$key] = $value;
			}
			$result->dataresponse = (object) array_merge($dataresponse, [
				//  untuk lookup atau modify response ditaruh disini
				'mediaorder_date' => date("d/m/Y", strtotime($row['mediaorder_date'])),
				'mediaordertype_name' => \FGTA4\utils\SqlUtility::Lookup($data->mediaordertype_id, $this->db, 'mst_mediaordertype', 'mediaordertype_id', 'mediaordertype_name'),
				'agency_partner_name' => \FGTA4\utils\SqlUtility::Lookup($data->agency_partner_id, $this->db, 'mst_partner', 'partner_id', 'partner_name'),
				'advertiser_partner_name' => \FGTA4\utils\SqlUtility::Lookup($data->advertiser_partner_id, $this->db, 'mst_partner', 'partner_id', 'partner_name'),
				'brand_name' => \FGTA4\utils\SqlUtility::Lookup($data->brand_id, $this->db, 'mst_brand', 'brand_id', 'brand_name'),
				'mediapackage_descr' => \FGTA4\utils\SqlUtility::Lookup($data->mediapackage_id, $this->db, 'mst_mediapackage', 'mediapackage_id', 'mediapackage_descr'),
				'salesordertype_name' => \FGTA4\utils\SqlUtility::Lookup($data->salesordertype_id, $this->db, 'mst_salesordertype', 'salesordertype_id', 'salesordertype_name'),
				'doc_name' => \FGTA4\utils\SqlUtility::Lookup($data->doc_id, $this->db, 'mst_doc', 'doc_id', 'doc_name'),
				
			]);

			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function NewId($param) {
		$seqname = 'MO';
		$dt = new \DateTime();	
		$ye = $dt->format("y");
		$mo = $dt->format("m");
		$seq = new Sequencer($this->db, 'seq_generalmonthly', $seqname, ['ye', 'mo']);
		$raw = $seq->getraw(['ye'=>$ye, 'mo'=> $mo]);
		$id = $seqname . $raw['ye'] . $raw['mo'] . str_pad($raw['lastnum'], 4, '0', STR_PAD_LEFT);
		return $id;	
	}

}

$API = new DataSave();