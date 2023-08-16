<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';
require_once __ROOT_DIR.'/core/sqlutil.php';	

require_once __DIR__ . '/sync_maps.php';
require_once __DIR__ . '/sync_base.php';

use \FGTA4\utils\SqlUtility;
use \FGTA4\clibase;
use \FGTA4\console;



class SYNC extends SyncBase {

	public function Run() {
		try {

			$region_id = $this->region_id;
			$dept_id = $this->dept_id;
			$brand_id = $this->brand_id;
			$batchno = $this->batchno;
	
			// update dulu dengan stok terbaru
			$this->Kalista_ItemstockUpdateCommit($brand_id, $batchno);
			$rows = $this->Kalista_getAvailableItem($brand_id);
	
	
			// hanya item yang ada di stok terbaru, yang akan di unmark
			$total = count($rows);
			$i = 0;
			foreach ($rows as $row) {
				$i++;
				$itemstock_id = $row['itemstock_id'];
				$qty = $row['itemstocksaldo_qty'];
				$value = $row['itemstocksaldo_value'];
	
				$this->output("unmarking ($i of $total) $itemstock_id");
				$this->Kalista_updateItemStock($itemstock_id, $qty, $value);
				$this->Kalista_updateMerchitem($itemstock_id, $qty, $value);
	
			}
	
			// sisa dari proses di atas, adalah barang2 yang sudah out of stok
			// disable semua
			$this->Kalista_unmarkItems($dept_id, $brand_id, $batchno);
	
	
	
	
			// Update MerchArticle
			$rows = $this->Kalista_getAvailableMerch($brand_id);
			$total = count($rows);
			$i = 0;
			foreach ($rows as $row) {
				$i++;
				$mercharticle_id = $row['mercharticle_id'];
				$this->output("unmarking ($i of $total) $mercharticle_id");
				$this->Kalista_updateMercharticle($mercharticle_id);
			}
	
			$this->Kalista_unmarkArticle($brand_id, $batchno);
	
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_unmarkArticle(string $brand_id, string $batchno) : void {
		try {
			$sqlArticle = "
				update mst_mercharticle
				set mercharticle_isdisabled=1, mercharticle_isupdating=0, mercharticle_updatebatch=null
				where
				brand_id=:brand_id and mercharticle_isupdating=1 and mercharticle_updatebatch=:batchno 
			";

			$stmtArticle = $this->db_kalista->prepare($sqlArticle);
			$stmtArticle->execute([':brand_id'=>$brand_id, ':batchno'=>$batchno]);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_updateMercharticle($mercharticle_id) : void {
		try {
			$obj = new \stdClass;
			$obj->mercharticle_id = $mercharticle_id;
			$obj->mercharticle_isupdating = 0;
			$obj->mercharticle_updatebatch = null;
			$obj->mercharticle_isdisabled = 0;

			$keys = new \stdClass;
			$keys->mercharticle_id = $obj->mercharticle_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_mercharticle", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_getAvailableMerch(string $brand_id) : array {
		try {
			$sql = "
				select 
				mercharticle_id ,
				sum(merchitem_isdisabled) as merchitem_isdisabled
				from 
				mst_merchitem 
				where 
				brand_id = :brand_id
				group by mercharticle_id
				having sum(merchitem_isdisabled) = 0
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':brand_id'=>$brand_id]);
			$rows = $stmt->fetchall();

			return $rows;
		}  catch (\Exception $ex) {
			throw $ex;
		}
	}




	public function Kalista_unmarkItems(string $dept_id, string $brand_id, string $batchno) : void {
		try {
			$sqlItemstock = "
				update mst_itemstock
				set 
				itemstock_lastqty=0, itemstock_lastvalue=0, itemstock_lastqtyupdate=NOW(),
				itemstock_isdisabled=1, itemstock_isupdating=0, itemstock_updatebatch=null
				where
				dept_id=:dept_id and itemstock_isupdating=1 and itemstock_updatebatch=:batchno 
			";
			$stmtItemstock = $this->db_kalista->prepare($sqlItemstock);

			$sqlMerchitem = "
				update mst_merchitem
				set merchitem_isdisabled=1, merchitem_isupdating=0, merchitem_updatebatch=null
				where
				brand_id=:brand_id and merchitem_isupdating=1 and merchitem_updatebatch=:batchno 
			";
			$stmtMerchitem = $this->db_kalista->prepare($sqlMerchitem);


			$stmtItemstock->execute([':dept_id'=>$dept_id, ':batchno'=>$batchno]);
			$stmtMerchitem->execute([':brand_id'=>$brand_id, ':batchno'=>$batchno]);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_updateMerchitem(string $itemstock_id, float $qty, float $value) : void {
		try {
			$obj = new \stdClass;
			$obj->merchitem_id = $itemstock_id;
			$obj->merchitem_isupdating = 0;
			$obj->merchitem_updatebatch = null;
			if ($qty!=0||$value!=0) {
				$obj->merchitem_isdisabled = 0;
			} else {
				$obj->merchitem_isdisabled = 1;
			}

			$keys = new \stdClass;
			$keys->merchitem_id = $obj->merchitem_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_merchitem", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		


		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_updateItemStock(string $itemstock_id, float $qty, float $value) : void {
		try {
			$obj = new \stdClass;
			$obj->itemstock_id = $itemstock_id;
			$obj->itemstock_lastqty = $qty;
			$obj->itemstock_lastvalue = $value;
			$obj->itemstock_lastqtyupdate = date('Y-m-d H:i:s');
			$obj->itemstock_isupdating = 0;
			$obj->itemstock_updatebatch = null;
			if ($qty!=0||$value!=0) {
				$obj->itemstock_isdisabled = 0;
			} else {
				$obj->itemstock_isdisabled = 1;
			}

			$keys = new \stdClass;
			$keys->itemstock_id = $itemstock_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_itemstock", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_getAvailableItem(string $brand_id) : array {
		try {

			$sql = "
				select 
				A.itemstock_id,
				sum(A.itemstocksaldo_qty) as itemstocksaldo_qty,
				sum(A.itemstocksaldo_value) as itemstocksaldo_value
				from mst_itemstockposition A 
				where A.brand_id = :brand_id
				group by A.itemstock_id
				having 
				sum(A.itemstocksaldo_qty)<>0 or sum(A.itemstocksaldo_value)<>0
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':brand_id'=>$brand_id]);
			$rows = $stmt->fetchall();
			return $rows;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_ItemstockUpdateCommit(string $brand_id, string $batchno) : void {
		try {
			
			$sqlDel = "
				delete from mst_itemstockposition
				where itemstockposition_id = :itemstockposition_id
			";
			$stmtDel = $this->db_kalista->prepare($sqlDel);
			
			$sql = "
				select itemstockposition_id 
				from mst_itemstockposition
				where
				brand_id = :brand_id
				and itemstockposition_isupdating = 1
				and itemstockposition_updatebatch = :batchno
			";

			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':brand_id'=>$brand_id, ':batchno'=>$batchno]);
			$rows = $stmt->fetchall();

			foreach ($rows as $row) {
				$itemstockposition_id = $row['itemstockposition_id'];
				$stmtDel->execute([':itemstockposition_id'=>$itemstockposition_id]); 
			}

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