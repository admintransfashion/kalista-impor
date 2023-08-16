<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;



/**
 * media/sales/logproof/apis/item-open.php
 *
 * ==========
 * Detil-Open
 * ==========
 * Menampilkan satu baris data/record sesuai PrimaryKey,
 * dari tabel item} logproof (trn_medialogproof)
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 16/12/2021
 */
$API = new class extends logproofBase {

	public function execute($options) {
		$tablename = 'trn_medialogproofitem';
		$primarykey = 'medialogproofitem_id';
		$userdata = $this->auth->session_get_user();
		
		try {
			$result = new \stdClass; 
			
			$where = \FGTA4\utils\SqlUtility::BuildCriteria(
				$options->criteria,
				[
					"medialogproofitem_id" => " medialogproofitem_id = :medialogproofitem_id "
				]
			);

			$sql = \FGTA4\utils\SqlUtility::Select('trn_medialogproofitem A', [
				'medialogproofitem_id', 'mediaadslot_timestart', 'mediaadslot_timeend', 'mediaadslot_descr', 'actual_timestart', 'actual_timeend', 'actual_duration', 'spot_id', 'mediaorderitem_validr', 'mediaorderitem_ppnidr', 'pph_taxtype_id', 'mediaorder_id', 'mediaorderitem_id', 'projbudget_id', 'projbudgettask_id', 'billoutpreprocess_id', 'mediaordertype_id', 'logproof_partnerinfo', 'agency_partner_id', 'advertiser_partner_id', 'brand_id', 'project_id', 'projecttask_id', 'medialogproof_id', '_createby', '_createdate', '_modifyby', '_modifydate' 
			], $where->sql);

			$stmt = $this->db->prepare($sql);
			$stmt->execute($where->params);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);

			$record = [];
			foreach ($row as $key => $value) {
				$record[$key] = $value;
			}

			$result->record = array_merge($record, [
					
				// // jikalau ingin menambah atau edit field di result record, dapat dilakukan sesuai contoh sbb: 
				// 'tambahan' => 'dta',
				//'tanggal' => date("d/m/Y", strtotime($record['tanggal'])),
				//'gendername' => $record['gender']

				'pph_taxtype_name' => \FGTA4\utils\SqlUtility::Lookup($record['pph_taxtype_id'], $this->db, 'mst_taxtype', 'taxtype_id', 'taxtype_name'),
				'mediaorder_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorder_id'], $this->db, 'trn_mediaorder', 'mediaorder_id', 'mediaorder_descr'),
				'mediaorderitem_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorderitem_id'], $this->db, 'trn_mediaorderitem', 'mediaorderitem_id', 'mediaorderitem_descr'),
				'projbudget_name' => \FGTA4\utils\SqlUtility::Lookup($record['projbudget_id'], $this->db, 'mst_projbudget', 'projbudget_id', 'projbudget_name'),
				'projbudgettask_name' => \FGTA4\utils\SqlUtility::Lookup($record['projbudgettask_id'], $this->db, 'view_projbudgettask', 'projbudgettask_id', 'projbudgettask_name'),
				'billoutpreprocess_name' => \FGTA4\utils\SqlUtility::Lookup($record['billoutpreprocess_id'], $this->db, 'mst_billoutpreprocess', 'billoutpreprocess_id', 'billoutpreprocess_name'),
				
				'_createby' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				'_modifyby' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
			]);

			// $date = DateTime::createFromFormat('d/m/Y', "24/04/2012");
			// echo $date->format('Y-m-d');

	

			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

};