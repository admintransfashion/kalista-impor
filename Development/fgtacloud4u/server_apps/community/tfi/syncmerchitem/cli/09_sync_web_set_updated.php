<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';
require_once __ROOT_DIR.'/core/sqlutil.php';	

require_once __DIR__ . '/sync_base.php';


use \FGTA4\utils\SqlUtility;
use \FGTA4\clibase;
use \FGTA4\console;


class SYNC extends SyncBase {

	public function Run() {
		try {

			SqlUtility::setQuote('`', '`');

			$region_id = $this->region_id;
			$dept_id = $this->dept_id;
			$brand_id = $this->brand_id;
			$batchno = $this->batchno;
	
			// set post to trash
			$rows = $this->tfiweb_getProductUpdating($brand_id, $batchno);
			
			$total = count($rows);
			$i = 0;
			foreach ($rows as $row) {
				$i++;
	
				$product_id = $row['product_id'];
				$qty = $row['stock_quantity'];
	
				$this->output("mark updated ($i of $total) $product_id $qty ");
				$this->tfiweb_markUpdatedProduct($product_id);
				$this->tfiweb_markUpdatedPosts($product_id);
	
			}
	
			$this->tfiweb_umarkProduct($brand_id, $batchno); 
			$this->tfiweb_umarkPostlist($brand_id, $batchno);
	
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function tfiweb_umarkPostMeta(string $post_id) : void {
		try {
			$sqlStock = "
				update tfi_postmeta 
				set meta_value='0'
				where post_id=:post_id and meta_key='_stock'
			";
			$stmtStock = $this->db_tfiweb->prepare($sqlStock);


			$sqlStockStatus = "
				update tfi_postmeta 
				set meta_value='outofstock'
				where post_id=:post_id and meta_key='_stock_status'			
			";
			$stmtStockStatus = $this->db_tfiweb->prepare($sqlStockStatus);

			$stmtStock->execute([':post_id'=>$post_id]);
			$stmtStockStatus->execute([':post_id'=>$post_id]);


		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	// unmarking posts (1 of 2) 4374
	// unmarking posts (2 of 2) 4435
	public function tfiweb_umarkPost(string $id) : void {
		try {
			$sql = "
				update tfi_posts
				set 
				post_status='trash',
				updating=0, batchno=null
				where
				ID=:id				
			";
			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([':id'=>$id]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function tfiweb_umarkPostlist(string $brand_id, string $batchno) : void {
		try {
			$sql = "
				select ID from tfi_posts 
				where 
					brand_id=:brand_id and batchno=:batchno 
				and updating=1
			";
			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':brand_id' => $brand_id,
				':batchno' => $batchno
			]);
			$rows = $stmt->fetchall();
			$total = count($rows);
			$i = 0;
			foreach ($rows as $row) {
				$i++;
				$id = $row['ID'];
				$this->output("unmarking posts ($i of $total) $id");

				$this->tfiweb_umarkPostMeta($id);
				$this->tfiweb_umarkPost($id);

			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	public function tfiweb_umarkProduct(string $brand_id, string $batchno) : void {
		try {
			$sql = "
				update tfi_wc_product_meta_lookup
				set 
				stock_quantity=0, stock_status='outofstock',
				batchno=null, updating=0
				where
				brand_id=:brand_id and updating=1 and batchno=:batchno
			";
			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':brand_id' => $brand_id,
				':batchno' => $batchno
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function tfiweb_markUpdatedPosts(string $id) : void {
		try {
			$sql = "
				update tfi_posts
				set 
				batchno=null, updating=0
				where 
				ID=:id 
			";
			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':id' => $id
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function tfiweb_markUpdatedProduct(string $product_id) : void {
		try {
			$sql = "
				update tfi_wc_product_meta_lookup
				set 
				batchno=null, updating=0
				where 
				product_id=:product_id 
			";
			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':product_id' => $product_id
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function tfiweb_getProductUpdating(string $brand_id, string $batchno) : array {
		try {
			$sql = "
				select product_id, stock_quantity
				from
				tfi_wc_product_meta_lookup
				where 
				brand_id=:brand_id and batchno=:batchno and stock_quantity>:qtyThreshold
			";

			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':batchno' => $batchno,
				':brand_id' => $brand_id,
				':qtyThreshold' => 	self::QTY_THRESHOLD
			]);
			
			$rows = $stmt->fetchall();

			return $rows;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}





	public function ConnectDb() {
		$TFIWEBDB_CONFIG = DB_CONFIG[$this->TfiWebDb];
		$this->db_tfiweb = new \PDO(
					$TFIWEBDB_CONFIG['DSN'], 
					$TFIWEBDB_CONFIG['user'], 
					$TFIWEBDB_CONFIG['pass'], 
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