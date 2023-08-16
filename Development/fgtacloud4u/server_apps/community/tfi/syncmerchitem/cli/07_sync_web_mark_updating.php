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
	
			$this->tfiweb_markPostsUpdating($brand_id, $batchno);
			$this->tfiweb_markProductUpdating($brand_id, $batchno);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function tfiweb_markProductUpdating(string $brand_id, string $batchno) : void {
		try {
			$sql = "
				update tfi_wc_product_meta_lookup
				set
				updating=1, batchno=:batchno
				where
				brand_id=:brand_id
			";

			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':batchno' => $batchno,
				':brand_id' => $brand_id
			]);
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function tfiweb_markPostsUpdating(string $brand_id, string $batchno) : void {
		try {
			$sql = "
				update tfi_posts
				set
				updating=1, batchno=:batchno
				where
				brand_id=:brand_id and (post_type in ('product_variation', 'product'))
			";

			$stmt = $this->db_tfiweb->prepare($sql);
			$stmt->execute([
				':batchno' => $batchno,
				':brand_id' => $brand_id
			]);
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