<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';	

require_once __DIR__ . '/sync_maps.php';
require_once __DIR__ . '/sync_base.php';

use \FGTA4\clibase;
use \FGTA4\console;


class SYNC extends SyncBase {

	public function Run() {
		try {

			$region_id = $this->region_id;

			//$output_file = __DIR__ . "/output-$region_id.dat";
			$output_file = implode('/', [$this->tempDir, "/output-$region_id.dat"]);
			$fp = fopen($output_file, "w+");
	
			if ($this->tbupdate==1) {
				$this->TB_UpdateCacheInventory($region_id);
			}
	
			$heinv_list = $this->TB_GetHeinvList($region_id);
			$total = count($heinv_list);
			$i=0;
	
			// total baris
			fputs($fp, $total."\n");
	
			// list branch
			$branchlist = $this->TB_GetBranchList($region_id);
			fputs($fp, base64_encode(json_encode($branchlist))."\n");
	
			// list itemcls
			$invclslist = $this->TB_GetInvclsList($region_id);
			fputs($fp, base64_encode(json_encode($invclslist))."\n");
	
			// season list
			$seasonlist = $this->TB_GetSeasonList($region_id);
			fputs($fp, base64_encode(json_encode($seasonlist))."\n");
	
			// list itemctg
			$invctglist = $this->TB_GetInvctgList($region_id);
			fputs($fp, base64_encode(json_encode($invctglist))."\n");
	
			
			foreach ($heinv_list as $heinv_id) {
				$i++;
				$heinvdata = $this->TB_GetHeinvData($heinv_id);
	
				$this->output("Set data $heinv_id, $i of $total");
	
				$stockdata = $this->TB_GetStockData($heinv_id);
				$heinvdata['STOCKDATA'] =  $stockdata;
	
				$base64line = base64_encode(json_encode($heinvdata));
				fputs($fp, $base64line."\n");
			}
			
			fclose($fp);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	
	public function TB_GetSeasonList(string $region_id) : array {
		try {
			$sql = "
				select 
				distinct
				A.season_id
				from cache_invsum A 
				where 
				A.region_id = :region_id and A.block='BRAND'			
			";

			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':region_id' => $region_id
			]);
			$seasonlist = $stmt->fetchall();

			return $seasonlist;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}

	public function TB_GetBranchList(string $region_id) : array {
		try {
			$sql = "
				select 
				distinct
				A.branch_id,
				(select branch_name from master_branch where branch_id=A.branch_id) as branch_name  
				from cache_invsum A  
				where 
				A.region_id = :region_id and A.block='STORE'			
			";

			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':region_id' => $region_id
			]);
			$branchlist = $stmt->fetchall();

			return $branchlist;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function TB_GetInvclsList(string $region_id) : array {
		try {
			$sql = "
				select 
				distinct
				B.invcls_id,
				(select invcls_name from master_invcls where invcls_id=B.invcls_id) as invcls_name  
				from cache_invsum A inner join master_heinv B on B.heinv_id = A.heinv_id 
				where 
				A.region_id = :region_id and A.block='BRAND'			
			";

			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':region_id' => $region_id
			]);
			$invclslist = $stmt->fetchall();

			return $invclslist;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function TB_GetInvctgList(string $region_id) : array {
		try {
			$sql = "
				select 
				distinct
				B.heinvctg_id,
				(select heinvctg_name from master_heinvctg where heinvctg_id=B.heinvctg_id and region_id=:region_id) as heinvctg_name  
				from cache_invsum A inner join master_heinv B on B.heinv_id = A.heinv_id 
				where 
				A.region_id = :region_id and A.block='BRAND'			
			";

			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':region_id' => $region_id
			]);
			$invctglist = $stmt->fetchall();

			return $invctglist;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}	



	public function TB_GetStockData(string $heinv_id) : array {
		try {
			$sql = "
				SELECT 
				convert(char(10), dt, 120) as dt,
				[END] as qty,
				branch_id,
				lastcost,
				C01, C02, C03, C04, C05, 
				C06, C07, C08, C09, C10,
				C11, C12, C13, C14, C15,
				C16, C17, C18, C19, C20,
				C21, C22, C23, C24, C25
				FROM cache_invsum 
				WHERE 
				heinv_id = :heinv_id AND BLOCK='STORE'
			";

			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':heinv_id' => $heinv_id
			]);
			$stockdata = $stmt->fetchall();

			return $stockdata;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function TB_GetHeinvData(string $heinv_id) : array {
		try {
			$sqlHeinv = "select * from view_master_heinv where heinv_id = :heinv_id";
			$stmtHeinv = $this->db_frm->prepare($sqlHeinv);
			$stmtHeinv->execute([
				':heinv_id' => $heinv_id
			]);
			$heinvdata = $stmtHeinv->fetch();

			
			$heinv_price01 = $heinvdata['heinv_price01'];
			$heinv_pricedisc01 = $heinvdata['heinv_pricedisc01'];
			$heinv_pricenett = $heinv_price01 * ((100-$heinv_pricedisc01)/100);			

			$heinvdata['heinv_priceweb'] = $heinv_pricenett;
			$heinvdata['ITEMSDATA'] = [];


			$sqlItem = "select * from master_heinvitem where heinv_id = :heinv_id";
			$stmtItem = $this->db_frm->prepare($sqlItem);
			$stmtItem->execute([
				':heinv_id' => $heinv_id
			]);
			$items = $stmtItem->fetchall();
			foreach ($items as $item) {
				$heinvdata['ITEMSDATA'][] = [
					'size' => $item['heinvitem_size'],
					'colnum' => $item['heinvitem_colnum'],
					'colname' => 'C' . $item['heinvitem_colnum'],
					'barcode' => $item['heinvitem_barcode'],
					'heinvitem_id' => substr($heinv_id, 0, 11) .  $item['heinvitem_colnum']
				];
			}

			return $heinvdata;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function TB_GetHeinvList(string $region_id) : array {
		try {
			// ambil tanggal terbaru
			$sqlDt = "
				select TOP 1 convert(varchar(10), dt, 120) as dt from cache_invsum 
				where region_id = :region_id and block='STORE' 
				order by dt DESC
			";
			$stmtDt = $this->db_frm->prepare($sqlDt);
			$stmtDt->execute([
				':region_id' => $region_id
			]);
			$row = $stmtDt->fetch();
			$dt = $row['dt'];

			$sql = "select heinv_id from cache_invsum where region_id=:region_id and dt=:dt ";
			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':region_id' => $region_id,
				':dt' => $dt
			]);

			$list = [];
			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$list[] = $row['heinv_id'];
			}
			return $list;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function TB_UpdateCacheInventory(string $region_id) : void {
		try {
			$sql = "
				declare @dt date
				set @dt = getdate();
				exec inv05cron_UpdateMvSum_Region @dt, :region_id 
			";

			$this->output("TB: Update inventory cache '$region_id' ...", ['nonewline'=>true]);
			$stmt = $this->db_frm->prepare($sql);
			$stmt->execute([
				':region_id' => $region_id
			]);
			$this->output("done.");

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function ConnectDb() {
		$FRMDB_CONFIG = DB_CONFIG[$this->FrmDb];
		$this->db_frm = new \PDO(
					$FRMDB_CONFIG['DSN'], 
					$FRMDB_CONFIG['user'], 
					$FRMDB_CONFIG['pass'], 
					DB_CONFIG_PARAM['mssql']
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
			exit(0);
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