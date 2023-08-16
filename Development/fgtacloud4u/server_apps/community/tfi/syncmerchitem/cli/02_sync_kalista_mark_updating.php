<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';
require_once __ROOT_DIR.'/core/sqlutil.php';	

require_once __DIR__ . '/sync_maps.php';
require_once __DIR__ . '/sync_base.php';

use \FGTA4\utils\SqlUtility;
use \FGTA4\clibase;
use \FGTA4\console;


/* Tandai semua item yang ada sedang dalam updating */

class SYNC extends SyncBase {

	public function Run() {
		try {
			$region_id = $this->region_id;
			$dept_id = $this->dept_id;
			$brand_id = $this->brand_id;
			$batchno = $this->batchno;
	
			$this->Kalista_UpdateMarkUpdatingItemstock($dept_id, $batchno);
			$this->Kalista_UpdateMarkUpdatingItemstockPosition($brand_id, $batchno);
			$this->Kalista_UpdateMarkUpdatingMerchArticle($brand_id, $batchno);
			$this->Kalista_UpdateMarkUpdatingMerchItem($brand_id, $batchno);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	


	public function Kalista_UpdateMarkUpdatingItemstock(string $dept_id, string $batchno) : void {
		try {
			$obj = new \stdClass;
			$obj->dept_id = $dept_id;
			$obj->itemstock_isupdating = 1;
			$obj->itemstock_updatebatch = $batchno;

			$keys = new \stdClass;
			$keys->dept_id = $obj->dept_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_itemstock", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_UpdateMarkUpdatingItemstockPosition(string $brand_id, string $batchno) : void {
		try {
			$obj = new \stdClass;
			$obj->brand_id = $brand_id;
			$obj->itemstockposition_isupdating = 1;
			$obj->itemstockposition_updatebatch = $batchno;

			$keys = new \stdClass;
			$keys->brand_id = $obj->brand_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_itemstockposition", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_UpdateMarkUpdatingMerchArticle(string $brand_id, string $batchno) : void {
		try {

			$obj = new \stdClass;
			$obj->brand_id = $brand_id;
			$obj->mercharticle_isupdating = 1;
			$obj->mercharticle_updatebatch = $batchno;

			$keys = new \stdClass;
			$keys->brand_id = $obj->brand_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_mercharticle", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		


		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_UpdateMarkUpdatingMerchItem(string $brand_id, string $batchno) : void {
		try {

			$obj = new \stdClass;
			$obj->brand_id = $brand_id;
			$obj->merchitem_isupdating = 1;
			$obj->merchitem_updatebatch = $batchno;

			$keys = new \stdClass;
			$keys->brand_id = $obj->brand_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_merchitem", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		


		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function ConnectDb() {
		$KALISTADB_CONFIG = DB_CONFIG[$this->KalistaDb];
		$this->db_kalista = new \PDO(
					$KALISTADB_CONFIG['DSN'], 
					$KALISTADB_CONFIG['user'], 
					$KALISTADB_CONFIG['pass'], 
					DB_CONFIG_PARAM['mariadb']
		);
	}


}



console::class(new class($args) extends clibase {
	function execute() {
		$sync = new SYNC();
		try {
			$sync->setup();
			$sync->ConnectDb();
			$sync->Run();
		} catch (\Exception $ex) {
			echo "\r\n\r\nERROR.\r\n";
			echo $ex->getMessage();
			echo "\r\n";
			$trace = $ex->getTrace();
			print_r($trace[0]);
			echo "\r\n";
			$sync->ReportError($ex);
			exit(9999);
		} finally {
			echo "\r\n";
		}

	}
});