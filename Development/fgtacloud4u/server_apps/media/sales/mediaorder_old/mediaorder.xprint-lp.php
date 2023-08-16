<?php namespace FGTA4\module; if (!defined('FGTA4')) { die('Forbiden'); } 


class PrintForm extends WebModule {
	function __construct() {
		// $logfilepath = __LOCALDB_DIR . "/output/logfile-billout.txt";
		// debug::start($logfilepath, "w");
		// debug::log("start debug");

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
	
	
	public function LoadPage() {
		$this->preloadscripts = [
			'jslibs/qrious.js'
		];

		$id = $_GET['id'];


		$tablename = 'trn_billout';
		$primarykey = 'billout_id';

		try {

			// header
			$sql = $this->getSqlHeader();
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':mediaorder_id' => $id]);
			$rows  = $stmt->fetchall(\PDO::FETCH_ASSOC);
			$row = $rows[0];

			$this->mediaorder_id = $row['mediaorder_id'];
			$this->mediaorder_descr = $row['mediaorder_descr'];
			$this->mediaorder_date = $row['mediaorder_date'];
			$this->mediaordertype_name = $row['mediaordertype_name'];
			$this->agency_name = $row['agency_name'];
			$this->advertiser_name = $row['advertiser_name'];
			$this->brand_name = $row['brand_name'];
			$this->empl_name = $row['empl_name'];


			// items
			$sql = $this->getSqlItem();
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':mediaorder_id' => $id]);
			$rows  = $stmt->fetchall(\PDO::FETCH_ASSOC);
			$this->rows = $rows;


			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	function getSqlHeader() {
		return "
			select 
			A.*,
			(select mediaordertype_name from mst_mediaordertype where mediaordertype_id = A.mediaordertype_id) as mediaordertype_name,
			(select partner_name from mst_partner where partner_id = A.agency_partner_id) as agency_name,
			(select partner_name from mst_partner where partner_id = A.advertiser_partner_id) as advertiser_name,
			(select brand_name from mst_brand where brand_id = A.brand_id) as brand_name,
			(select empl_name from mst_empl where empl_id =(select empl_id from mst_empluser where user_id = A._createby )) as empl_name
			from trn_mediaorder A 
			where A.mediaorder_id = :mediaorder_id
		";
	}	


	function getSqlItem() {
		return "
			SELECT
			A.*
			FROM
			trn_medialogproofitem A inner join trn_mediaorderitem B on A.mediaorderitem_id = B.mediaorderitem_id 
			where B.mediaorder_id = :mediaorder_id		
		";
	}


}

$MODULE = new PrintForm();