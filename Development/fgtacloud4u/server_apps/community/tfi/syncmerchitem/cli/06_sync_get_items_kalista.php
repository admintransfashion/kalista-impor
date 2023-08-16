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
			$brand_id = $this->brand_id;
			$batchno = $this->batchno;
	
			//$output_file = __DIR__ . "/web-$region_id.dat";
			$output_file = implode('/', [$this->tempDir, "/web-$region_id.dat"]);
			$fp = fopen($output_file, "w+");
	
	
			// ambil list merchartikle yang ada gambar
			$mercharticlelist = $this->Kalista_GetMercharticleList($brand_id, $batchno);
			$total = count($mercharticlelist);
	
			// baris 1: total baris items
			fputs($fp, "$total\r\n");
	
			$dummy = base64_encode(json_encode([]));
	
			// baris 2: new arrival
			fputs($fp, $dummy."\r\n");
	
			// baris 3: discount / promo items
			fputs($fp, $dummy."\r\n");
	
			// baris 4: highlighted produc
			fputs($fp, $dummy."\r\n");
	
			// baris 5: favourite product
			fputs($fp, $dummy."\r\n");
	
	
			$i = 0;
			foreach ($mercharticlelist as $mercharticle_id) {
				$i++;
				$this->output("($i of $total) exporting $mercharticle_id");
	
				$mercharticle = $this->Kalista_getArticleData($mercharticle_id); 
	
				
				$mercharticle['brand_id'] = $brand_id;
				$mercharticle['variancolor'] = $this->Kalista_getArticleVarianColor($mercharticle_id);
				$mercharticle['variansize'] = $this->Kalista_getArticleVarianSize($mercharticle_id);
				$mercharticle['props'] = $this->Kalista_getArticleProperties($mercharticle_id);
				$mercharticle['items'] = $this->Kalista_getArticleItemData($mercharticle_id);
				
				
				$first_merchpic_col = $mercharticle['variancolor'][0];
				$mercharticle['images'] = $this->Kalista_getArticleImages($mercharticle_id);
				$mercharticle['imagelist'] = $this->Kalista_getArticleImageList($mercharticle_id, $first_merchpic_col);
	
	
				$minprice = 0;
				$maxprice = 0;
				$totalqty = 0;
				foreach ($mercharticle['items'] as $item) {
					$sellprice = $item['sellprice'];
					$qty = (int)$item['qty'];
	
					if ($minprice==0) { 
						$minprice=$sellprice; 
					} else if ($sellprice<$minprice) {
						$minprice = $sellprice;
					}
	
					if ($sellprice>$maxprice) {
						$maxprice = $sellprice;
					}
	
					$totalqty += $qty;
				}
				
				$mercharticle['minprice'] = $minprice;
				$mercharticle['maxprice'] = $maxprice;
				$mercharticle['totalqty'] = $totalqty;
	
	
				$jsondata = json_encode($mercharticle);
				fputs($fp, base64_encode($jsondata)."\n");
				//die("die at line " . __LINE__ . "\n\n");
			}
	
			fclose($fp);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_getArticleProperties(string $mercharticle_id) : array {
		try {
			$props = [];
			$sql = "
				select itemproptype_id,	mercharticleprop_keys, mercharticleprop_value
				from
				mst_mercharticleprop
				where
				mercharticle_id = :mercharticle_id;
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$rows = $stmt->fetchall();

			foreach ($rows as $row) {
				$itemproptype_id = $row['itemproptype_id'];
				$props[$itemproptype_id] = $row;
			}
			return $props;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	public function Kalista_getArticleVarianColor(string $mercharticle_id) : array {
		try {
			$colors = [];

			$sql = "
				select distinct merchitem_colordescr  
				from mst_merchitem where mercharticle_id = :mercharticle_id;
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$colors[] = $row['merchitem_colordescr']; 
			}

			return $colors;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_getArticleVarianSize(string $mercharticle_id) : array {
		try {
			$sizes = [];

			$sql = "
				select distinct merchitem_size  
				from mst_merchitem where mercharticle_id = :mercharticle_id;
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$sizes[] = $row['merchitem_size']; 
			}

			return $sizes;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_getArticleItemData(string $mercharticle_id) : array {
		try {
			
			// get stock
			$sql = "
				select 
				itemstock_id, itemstocksaldo_qty
				from mst_itemstockposition 
				where 
				itemstock_id = :itemstock_id and site_id = :site_id
			";
			$stmtQty = $this->db_kalista->prepare($sql);
			

			// get itemstock data
			$sql = "
				select 
				A.itemstock_id, A.itemstock_grossprice, 
				coalesce ((
					select itemstockprop_value from mst_itemstockprop 
					where itemstock_id=A.itemstock_id and itemproptype_id='PRICEWEB'
				), A.itemstock_sellprice) as itemstock_sellprice
				from mst_itemstock A where A.itemstock_id=:itemstock_id
			";
			$stmtStock = $this->db_kalista->prepare($sql);

			// get list items
			$sql = "
				select 
				A.merchitem_id, A.merchitem_art, A.merchitem_mat, A.merchitem_col, A.merchitem_size,
				A.merchitem_name, A.merchitem_descr, A.merchitem_colnum, A.merchitem_pcpline, 
				A.merchitem_pcpgroup, A.merchitem_pcpcategory, A.merchitem_colorcode, A.merchitem_colordescr,
				A.merchitem_bahan, A.merchitem_pemeliharaan, A.merchitem_dibuatdi,
				A.merchitem_width, A.merchitem_length, A.merchitem_height, A.merchitem_weight
				from 
				mst_merchitem A 
				where
				A.mercharticle_id = :mercharticle_id
			";
			
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$rows = $stmt->fetchall();

			$items = [];
			foreach ($rows as $row) {
				$itemstock_id = $row['merchitem_id'];

				// ambil data dari mst_itemstock
				$stmtStock->execute([':itemstock_id'=>$itemstock_id]);
				$rowItemStock = $stmtStock->fetch();
				if ($rowItemStock==null) { throw new \Exception("$itemstock_id tidak ada di mst_itemstock"); } 
				
				$grossprice = $rowItemStock['itemstock_grossprice'];
				$sellprice = $rowItemStock['itemstock_sellprice'];

				if ($grossprice<$sellprice) {
					$grossprice = $sellprice;
				}


				$qty = 0;
				$stmtQty->execute([
					':itemstock_id' => $itemstock_id,
					':site_id' => $this->web_inv_site_id
				]);
				$rowqty = $stmtQty->fetch();
				if ($rowqty!=null) {
					$qty = $rowqty['itemstocksaldo_qty'];
				}

				$row['mercharticle_id'] = $mercharticle_id;
				$row['grossprice'] = $grossprice;
				$row['sellprice'] = $sellprice;
				$row['qty'] = $qty;

				$row['imagelist'] = $this->Kalista_getArticleImageList($mercharticle_id, $row['merchitem_col']);

				$items[] = $row;
			}

			return $items;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_getArticleImages(string $mercharticle_id) : array {
		try {
			$sql = "
				select merchpic_couchdbid , merchpic_col , merchpic_name 
				from mst_merchpic where mercharticle_id=:mercharticle_id
			";

			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id' => $mercharticle_id]);

			$rows = $stmt->fetchall();

			$images = [];
			foreach ($rows as $row) {
				$couchdbid = $row['merchpic_couchdbid'];
				if (!array_key_exists($couchdbid, $images)) {
					$images[$couchdbid] = $row;
				}
			}
			return $images;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_getArticleImageList(string $mercharticle_id, ?string $color) : array {
		try {
			$sql = "
				select merchpic_couchdbid , merchpic_col , merchpic_name 
				from mst_merchpic 
				where mercharticle_id=:mercharticle_id and merchpic_col=:merchpic_col 
				order by merchpic_name
			";

			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([
				':mercharticle_id' => $mercharticle_id,
				':merchpic_col' => $color
			]);

			$rows = $stmt->fetchall();

			$imagelist = [];
			foreach ($rows as $row) {
				if (!in_array($row['merchpic_couchdbid'], $imagelist)) {
					$imagelist[] = $row['merchpic_couchdbid'];
				}
			}

			return $imagelist;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}




	public function Kalista_getArticleData(string $mercharticle_id) : array {
		try {
			$sql = "
				select 
				A.mercharticle_id, A.mercharticle_art, A.mercharticle_mat, A.mercharticle_matname,
				A.mercharticle_couchdbid, 
				A.mercharticle_isvarcolor, A.mercharticle_isvarsize
				from mst_mercharticle A
				where 
				A.mercharticle_id = :mercharticle_id
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$row = $stmt->fetch();


			return $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_GetMercharticleList(string $brand_id, string $batchno) : array {
		try {
			$sql = "
				select
				A.mercharticle_id
				from 
				mst_mercharticle A inner join mst_mercharticleprop B on B.mercharticle_id = A.mercharticle_id
				where
				    A.brand_id = :brand_id 
				and B.itemproptype_id = 'UPLOADBATCH' 
				and B.mercharticleprop_value = :mercharticleprop_value
				and A.mercharticle_couchdbid is not null
			";


			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([
				':brand_id' => $brand_id,
				':mercharticleprop_value' => $batchno
			]);

			$mercharticlelist = [];
			while ($row = $stmt->fetch()) {
				$mercharticle_id = $row['mercharticle_id'];
				$mercharticlelist[] = $mercharticle_id;
			}

			return $mercharticlelist;
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