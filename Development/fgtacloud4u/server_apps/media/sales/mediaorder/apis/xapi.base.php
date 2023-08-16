<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';

// /* Enable Debugging */
// require_once __ROOT_DIR.'/core/debug.php';

use \FGTA4\exceptions\WebException;
// use \FGTA4\debug;




/**
 * media/sales/mediaorder/apis/xapi.base.php
 *
 * mediaorderBase
 * Kelas dasar untuk keperluan-keperluan api
 * kelas ini harus di-inherit untuk semua api pada modul mediaorder
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 15/12/2021
 */
class mediaorderBase extends WebAPI {

	protected $main_tablename = "trn_mediaorder";
	protected $main_primarykey = "mediaorder_id";
	protected $main_field_version = "mediaorder_version";	
	
	protected $field_iscommit = "mediaorder_iscommit";
	protected $field_commitby = "mediaorder_commitby";
	protected $field_commitdate = "mediaorder_commitdate";		
			
	
	protected $fields_isapprovalprogress = "mediaorder_isapprovalprogress";			
	protected $field_isapprove = "mediaorder_isapproved";
	protected $field_approveby = "mediaorder_approveby";
	protected $field_approvedate = "mediaorder_approvedate";
	protected $field_isdecline = "mediaorder_isdeclined";
	protected $field_declineby = "mediaorder_declineby";
	protected $field_declinedate = "mediaorder_declinedate";

	protected $approval_tablename = "trn_mediaorderappr";
	protected $approval_primarykey = "mediaorderappr_id";
	protected $approval_field_approve = "mediaorderappr_isapproved";
	protected $approval_field_approveby = "mediaorderappr_by";
	protected $approval_field_approvedate = "mediaorderappr_date";
	protected $approval_field_decline = "mediaorderappr_isdeclined";
	protected $approval_field_declineby = "mediaorderappr_declinedby";
	protected $approval_field_declinedate = "mediaorderappr_declineddate";
	protected $approval_field_notes = "mediaorderappr_notes";
	protected $approval_field_version = "mediaorder_version";

			



	function __construct() {

		// $logfilepath = __LOCALDB_DIR . "/output//*mediaorder*/.txt";
		// debug::disable();
		// debug::start($logfilepath, "w");

		$DB_CONFIG = DB_CONFIG[$GLOBALS['MAINDB']];
		$DB_CONFIG['param'] = DB_CONFIG_PARAM[$GLOBALS['MAINDBTYPE']];		
		$this->db = new \PDO(
					$DB_CONFIG['DSN'], 
					$DB_CONFIG['user'], 
					$DB_CONFIG['pass'], 
					$DB_CONFIG['param']
		);

		
	}

	function pre_action_check($data, $action) {
		try {
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function get_header_row($id) {
		try {
			$sql = "
				select 
				A.*
				from 
				$this->main_tablename A 
				where 
				A.$this->main_primarykey = :id 
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([":id" => $id]);
			$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
			if (!count($rows)) { throw new \Exception("Data '$id' tidak ditemukan"); }
			return (object)$rows[0];
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}