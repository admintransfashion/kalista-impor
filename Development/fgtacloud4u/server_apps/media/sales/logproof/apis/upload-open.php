<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;



/**
 * media/sales/logproof/apis/upload-open.php
 *
 * ==========
 * Detil-Open
 * ==========
 * Menampilkan satu baris data/record sesuai PrimaryKey,
 * dari tabel upload} logproof (trn_medialogproof)
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 29/11/2021
 */
$API = new class extends logproofBase {

	public function execute($options) {
		$tablename = 'trn_medialogproofupload';
		$primarykey = 'medialogproofupload_id';
		$userdata = $this->auth->session_get_user();
		
		try {
			$result = new \stdClass; 
			
			$where = \FGTA4\utils\SqlUtility::BuildCriteria(
				$options->criteria,
				[
					"medialogproofupload_id" => " medialogproofupload_id = :medialogproofupload_id "
				]
			);

			$sql = \FGTA4\utils\SqlUtility::Select('trn_medialogproofupload A', [
				  'medialogproofupload_id', 'nobaris', 'mediaadslot_timestart', 'mediaadslot_timeend', 'mediaadslot_duration', 'mediaadslot_descr'
				, 'mediaadslot_code', 'actual_timestart', 'actual_timeend', 'actual_duration', 'spot_id', 'mediaorder_ref'
				, 'mediaorder_reftype', 'mediaorder_descr', 'mediaorder_id', 'mediaordertype_id', 'agency_code', 'agency_name'
				, 'agency_partner_id', 'advertiser_code', 'advertiser_name', 'advertiser_partner_id', 'brand_code', 'brand_name'
				, 'brand_id', 'programme_code', 'programme_name', 'project_id', 'episode_code', 'episode_name', 'projecttask_id'
				, 'medialogproof_validr', 'medialogproof_ppnidr', 'pph_taxtype_id', 'medialogproof_id'
				, '_createby', '_createdate', '_modifyby', '_modifydate' 
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