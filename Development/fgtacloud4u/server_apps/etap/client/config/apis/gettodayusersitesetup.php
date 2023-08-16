<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';



use \FGTA4\exceptions\WebException;


class DataList extends WebAPI {
	function __construct() {
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

	public function execute($options) {

		$userdata = $this->auth->session_get_user();

		try {
		
			// cek apakah user boleh mengeksekusi API ini
			if (!$this->RequestIsAllowedFor($this->reqinfo, "list", $userdata->groups)) {
				throw new \Exception('your group authority is not allowed to do this action.');
			}
			// $options->criteria;
			$sql = "
				SELECT 
					X.site_id,
					X.site_name,
					X.land_id,
					(select land_name from mst_land where land_id = X.land_id) as land_name,
					X.config_id,
					X.taxtype_id
				from
				mst_site X inner join (
					SELECT 
					distinct B.site_id
					FROM
					trn_supvisit A inner join trn_supvisitsite B on B.supvisit_id = A.supvisit_id 
								inner join trn_supvisituser C on C.supvisit_id =A.supvisit_id 
					AND A.supvisit_datestart <= NOW() and A.supvisit_dateend >= NOW()
					AND A.supvisit_iscommit = 1
					AND C.user_id = :username
				) Y ON Y.site_id = X.site_id
				WHERE
				X.site_isdisabled = 0
			";
			

			$stmt = $this->db->prepare($sql);
			$stmt->execute([':username' => $userdata->username]);
			$rows  = $stmt->fetchall(\PDO::FETCH_ASSOC);
			$records = [];
			foreach ($rows as $row) {
				$record = [];
				foreach ($row as $key => $value) {
					$record[$key] = $value;
				}


				$config = \FGTA4\utils\SqlUtility::LookupRow($record['config_id'], $this->db, 'mst_config', 'config_id');
				$configdata = $this->getConfigData($config['config_dir'], $config['config_filename']);

				$taxtype = \FGTA4\utils\SqlUtility::LookupRow($record['taxtype_id'], $this->db, 'mst_taxtype', 'taxtype_id');

				array_push($records, array_merge($record, [
					'land_name' => \FGTA4\utils\SqlUtility::Lookup($record['land_id'], $this->db, 'mst_land', 'land_id', 'land_name'),
					'config_name' => $config['config_name'],
					'dbtype' => $configdata->dbtype,
					'taxtype_value' => $taxtype['taxtype_value'],
					'taxtype_isinclude' => $taxtype['taxtype_include']
				]));				
			}

			$total = count($rows);
			$maxrow = $total;
			$offset = 0;

			// kembalikan hasilnya
			$result = new \stdClass; 
			$result->total = $total;
			$result->offset = $offset + $maxrow;
			$result->maxrow = $maxrow;
			$result->records = $records;
			return $result;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function getConfigData($config_dir, $config_filename) {
		$data = new \stdClass;
		$data->dbtype = $config_filename;
		

		return $data;

	// switch ($config_filename) {

	// 		default:
	// 			return 'drv-mysql-test';
	// 	}

	}

}

$API = new DataList();