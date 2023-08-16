<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __DIR__ . '/xapi.base.php';


use \FGTA4\exceptions\WebException;

/**
 * media/sales/logproofassign/apis/list.php
 *
 * ========
 * DataList
 * ========
 * Menampilkan data-data pada tabel header logproofassign (trn_medialogproofitem)
 * sesuai dengan parameter yang dikirimkan melalui variable $option->criteria
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 16/12/2021
 */
$API = new class extends logproofassignBase {

	public function execute($options) {

		$userdata = $this->auth->session_get_user();

		try {
		
			// cek apakah user boleh mengeksekusi API ini
			if (!$this->RequestIsAllowedFor($this->reqinfo, "list", $userdata->groups)) {
				throw new \Exception('your group authority is not allowed to do this action.');
			}

			// \FGTA4\utils\SqlUtility::setDefaultCriteria($options->criteria, '--fieldscriteria--', '--value--');

			$assigned = $options->criteria->assigned;
			if ($assigned=='all') {
				unset($options->criteria->{"assigned"});
			}

			$periodemo_id = $options->criteria->periodemo_id;
			$periodemo = \FGTA4\utils\SqlUtility::LookupRow($periodemo_id, $this->db, 'mst_periodemo', 'periodemo_id');
			if (!empty($periodemo_id)) {
				$options->criteria->periode = 1;
				$options->criteria->datestart = $periodemo['periodemo_dtstart'];
				$options->criteria->dateend = $periodemo['periodemo_dtend'];
			} 
			unset($options->criteria->{"periodemo_id"});
			



			$where = \FGTA4\utils\SqlUtility::BuildCriteria(
				$options->criteria,
				[
					"search" => " A.medialogproofitem_id LIKE CONCAT('%', :search, '%') ",
					"assigned" => $assigned=='assigned' ? " A.projbudgettask_id is not null " : " A.projbudgettask_id is null ",
					"periode" => " ( A.medialogproof_date >= :datestart and  A.medialogproof_date <= :dateend) ",
					"datestart" => '--',
					"dateend" => '--'
				]
			);

			$result = new \stdClass; 
			$maxrow = 30;
			$offset = (property_exists($options, 'offset')) ? $options->offset : 0;

			$stmt = $this->db->prepare("select count(*) as n from view_medialogproof A" . $where->sql);
			$stmt->execute($where->params);
			$row  = $stmt->fetch(\PDO::FETCH_ASSOC);
			$total = (float) $row['n'];

			$limit = " LIMIT $maxrow OFFSET $offset ";
			$stmt = $this->db->prepare("
				select 
				A.medialogproof_date, A.medialogproofitem_id, A.mediaadslot_timestart, A.mediaadslot_timeend, A.mediaadslot_descr, A.actual_timestart, A.actual_timeend, A.actual_duration, A.spot_id, A.mediaorderitem_validr, A.mediaorderitem_ppnidr, A.pph_taxtype_id, A.mediaorder_id, A.mediaorderitem_id, A.projbudget_id, A.projbudgettask_id, A.billoutpreprocess_id, A.mediaordertype_id, A.logproof_partnerinfo, A.agency_partner_id, A.advertiser_partner_id, A.brand_id, A.project_id, A.projecttask_id, A.medialogproof_id, A._createby, A._createdate, A._modifyby, A._modifydate 
				from view_medialogproof A
			" . $where->sql . $limit);
			$stmt->execute($where->params);
			$rows  = $stmt->fetchall(\PDO::FETCH_ASSOC);

			$records = [];
			foreach ($rows as $row) {
				$record = [];
				foreach ($row as $key => $value) {
					$record[$key] = $value;
				}

				array_push($records, array_merge($record, [
					// // jikalau ingin menambah atau edit field di result record, dapat dilakukan sesuai contoh sbb: 
					//'tanggal' => date("d/m/y", strtotime($record['tanggal'])),
				 	//'tambahan' => 'dta'
					'pph_taxtype_name' => \FGTA4\utils\SqlUtility::Lookup($record['pph_taxtype_id'], $this->db, 'mst_taxtype', 'taxtype_id', 'taxtype_name'),
					'mediaorder_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorder_id'], $this->db, 'trn_mediaorder', 'mediaorder_id', 'mediaorder_descr'),
					'mediaorderitem_descr' => \FGTA4\utils\SqlUtility::Lookup($record['mediaorderitem_id'], $this->db, 'trn_mediaorderitem', 'mediaorderitem_id', 'mediaorderitem_descr'),
					'projbudget_name' => \FGTA4\utils\SqlUtility::Lookup($record['projbudget_id'], $this->db, 'mst_projbudget', 'projbudget_id', 'projbudget_name'),
					'projbudgettask_name' => \FGTA4\utils\SqlUtility::Lookup($record['projbudgettask_id'], $this->db, 'view_projbudgettask', 'projbudgettask_id', 'projbudgettask_name'),
					'billoutpreprocess_name' => \FGTA4\utils\SqlUtility::Lookup($record['billoutpreprocess_id'], $this->db, 'mst_billoutpreprocess', 'billoutpreprocess_id', 'billoutpreprocess_name'),
					'mediaordertype_name' => \FGTA4\utils\SqlUtility::Lookup($record['mediaordertype_id'], $this->db, 'mst_mediaordertype', 'mediaordertype_id', 'mediaordertype_name'),
					'partner_name' => \FGTA4\utils\SqlUtility::Lookup($record['agency_partner_id'], $this->db, 'mst_partner', 'partner_id', 'partner_name'),
					'partner_name' => \FGTA4\utils\SqlUtility::Lookup($record['advertiser_partner_id'], $this->db, 'mst_partner', 'partner_id', 'partner_name'),
					'brand_name' => \FGTA4\utils\SqlUtility::Lookup($record['brand_id'], $this->db, 'mst_brand', 'brand_id', 'brand_name'),
					'project_name' => \FGTA4\utils\SqlUtility::Lookup($record['project_id'], $this->db, 'mst_project', 'project_id', 'project_name'),
					'projecttask_name' => \FGTA4\utils\SqlUtility::Lookup($record['projecttask_id'], $this->db, 'mst_projecttask', 'projecttask_id', 'projecttask_name'),
					 
				]));
			}

			// kembalikan hasilnya
			$result->total = $total;
			$result->offset = $offset + $maxrow;
			$result->maxrow = $maxrow;
			$result->records = $records;
			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

};