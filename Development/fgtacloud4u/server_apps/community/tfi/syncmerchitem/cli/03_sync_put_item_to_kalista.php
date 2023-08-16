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

			// $username = posix_getpwuid(posix_geteuid())['name'];
			$region_id = $this->region_id;

			//$output_file = __DIR__ . "/output-$region_id.dat";
			$output_file = implode('/', [$this->tempDir, "output-$region_id.dat"]);
			if (!is_file($output_file)) {
				throw new \Exception("file '$output_file' tidak ditemukan. Jalankan '01_sync_get_items_tb' untuk generate file.\r\nCek syn-step.sh\r\n");
			}
			
			$fp = fopen($output_file, "r+");
	
			// total baris
			$line = trim(fgets($fp));
			$total = $line;
	
			// list branch
			$base64_branchlist = trim(fgets($fp));
			$branchlist = $this->getBranchList($base64_branchlist, $region_id);
	
			// list invcls
			$base64_invclslist = trim(fgets($fp));
			// $invclslist = $this->getInvclsList($base64_invclslist, $region_id);
			// print_r($invclslist);
			// die();

			// list season
			$base64_seasonlist = trim(fgets($fp));
			$seasonlist = $this->getSeasonList($base64_seasonlist, $region_id);

			// list kategori
			$base64_invctglist = trim(fgets($fp));
			$invctglist = $this->getInvctgList($base64_invctglist, $region_id);

			$mercharticlelist = [];
	
			$i = 0;
			while (!feof($fp)) {
				$line = trim(fgets($fp));
				if ($line=="") { continue; }
	
				$i++;
	
				$heinvdata = json_decode(base64_decode($line), true);	
				if ($heinvdata==null) { continue; }	
							
				//print_r($heinvdata);
				//die();
	
				$heinv_id = $heinvdata['heinv_id'];
				$season_id = $heinvdata['season_id'];
				$invcls_id = $heinvdata['invcls_id'];
				$heinvctg_id = $heinvdata['heinvctg_id'];
					
				$this->output("Updating $heinv_id ($i of $total)... ");	
	
				if ($heinv_id=='TM20100003800') {
					//echo "pause for debug";
				}
	
				// patchdata
				if (empty($heinvdata['heinv_col'])) {
					$heinvdata['heinv_col'] = '-';
				}
	
				if (empty($heinvdata['heinv_color1'])) {
					$heinvdata['heinv_color1'] = '-';	
				}
	
			
				
				$mercharticledata = $this->Kalista_getMercharticleData($heinvdata);
				// print_r($heinvdata);
				// die('STOP DULU DI BARIS '. __LINE__ ."\r\n\r\n\r\n");
				$mercharticle_id = $mercharticledata['mercharticle_id'];
	
				$heinvdata['MAPS']['mercharticle_id'] = $mercharticle_id;			
				$heinvdata['MAPS']['merchsea_id'] = $seasonlist[$season_id]['merchsea_id'];
				// $heinvdata['MAPS']['itemctg_id'] = $invclslist[$invcls_id]['itemctg_id'];
				// $heinvdata['MAPS']['merchitemctg_id'] = $invclslist[$invcls_id]['merchitemctg_id'];
				// $heinvdata['MAPS']['WPBrand'] = $invclslist[$invcls_id]['WPBrand'];
				// $heinvdata['MAPS']['WPGroup'] = $invclslist[$invcls_id]['WPGroup'];
				// $heinvdata['MAPS']['WPCategory'] = $invclslist[$invcls_id]['WPCategory'];
				// $heinvdata['MAPS']['WPSkip'] = $invclslist[$invcls_id]['WPSkip'];
				$heinvdata['MAPS']['itemctg_id'] = $invctglist[$heinvctg_id]['itemctg_id'];
				$heinvdata['MAPS']['merchitemctg_id'] = $invctglist[$heinvctg_id]['merchitemctg_id'];
				$heinvdata['MAPS']['itemclass_id'] = $invctglist[$heinvctg_id]['itemclass_id'];
				$heinvdata['MAPS']['WPBrand'] = $invctglist[$heinvctg_id]['WPBrand'];
				$heinvdata['MAPS']['WPGroup'] = $invctglist[$heinvctg_id]['WPGroup'];
				$heinvdata['MAPS']['WPCategory'] = $invctglist[$heinvctg_id]['WPCategory'];
				$heinvdata['MAPS']['WPSkip'] = $invctglist[$heinvctg_id]['WPSkip'];
				
				
				$heinvdata['MAPS']['BRANCH'] = &$branchlist; 
	
	
				if (!in_array($mercharticle_id, $mercharticlelist)) {
					$mercharticlelist[] = $mercharticle_id;
				}
	
	
				// pricing
				$grossprice = $heinvdata['heinv_priceori'] + $heinvdata['heinv_priceadj'];
				$currentprice = $heinvdata['heinv_price01'];
				$discpercent =  $heinvdata['heinv_pricedisc01'];
				$discvalue = $currentprice * ($discpercent/100);
				$nettprice = $currentprice - $discvalue;
				$isdiscvalue = ($currentprice < $grossprice) ? 1 : 0; 
	
				$heinvdata['PRICING'] = [
					'grossprice' => $grossprice,
					'currentprice' => $currentprice,
					'discpercent' => $discpercent,
					'discvalue' => $discvalue,
					'nettprice' => $nettprice,
					'isdiscvalue' => $isdiscvalue
				];
	
	
				$heinvitemlist = [];
				foreach ($heinvdata['ITEMSDATA'] as $itemdata) {
					$heinvitem_id = $itemdata['heinvitem_id'];
					$itemstock_id = $heinvitem_id;
					if (!in_array($heinvitem_id, $heinvitemlist)) {
						$heinvitemlist[] = $heinvitem_id;
	
						$objItemStock = $this->createItemStock($heinvdata, $itemdata);				
						$this->Kalista_UpdateMstItemStock($objItemStock);				
	
						$objMerchItem = $this->createMerchItem($heinvdata, $itemdata);	
						$this->Kalista_UpdateMstMerchitem($objMerchItem);
	
						$objqty = new stdClass;
						$objqty->itemstock_id = $itemstock_id;
						$objqty->itemstock_lastqty = 0;
						$objqty->itemstock_lastvalue = 0;
						$objqty->itemstock_lastqtyupdate = null;
						$stockposition = $this->getStokPosition($itemdata['colname'], $heinvdata);
						$this->Kalista_ResetStokPosition($heinvitem_id);
						foreach ($stockposition as $stockdata) {
							$objStokdata = $this->createStokPosition($heinvitem_id, $stockdata);
							$this->Kalista_UpdateStokPosition($objStokdata);
	
							$objqty->itemstock_lastqty += $stockdata['qty'];
							$objqty->itemstock_lastvalue +=  $stockdata['lastcost'] * $stockdata['qty'];
							$objqty->itemstock_lastqtyupdate = $stockdata['dt'];
						}
	
						$this->Kalista_UpdateMstItemStockQty($objqty);
	
	
						$priceweb = (float)$heinvdata['heinv_priceweb'];
						if ($nettprice==$priceweb) {
							$priceweb = null; // kalau sama dengan price standard, hapus saja di properties
						} 
	
						$WebProperties = [
							['itemproptype_id'=>'PRICEWEB', 'value'=>$priceweb],
							['itemproptype_id'=>'WP-TAXONOMY-BRAND', 'value'=>$heinvdata['MAPS']['WPBrand']], 
							['itemproptype_id'=>'WP-TAXONOMY-GRO', 'value'=>$heinvdata['MAPS']['WPGroup']],
							['itemproptype_id'=>'WP-TAXONOMY-CTG', 'value'=>$heinvdata['MAPS']['WPCategory']],
							['itemproptype_id'=>'WP-SKIP','value'=>$heinvdata['MAPS']['WPSkip']==true?1:0],
	
						];
		
						$this->Kalista_UpdateWebProperties($itemstock_id, $WebProperties);
					}
	
					$objItemStockBarcode = $this->createItemStockBarcode($heinvdata, $itemdata, $heinvdata);				
					$this->Kalista_UpdateMstItemBarcode($objItemStockBarcode);
	
				}
				//print_r($heinvdata);
				//die("die at line " . __LINE__ . "\n");
	
			}
			fclose($fp);
	
	
	
			$this->Kalista_UpdateMercharticleBatch($mercharticlelist);
	
			// update variance
			$total = count($mercharticlelist);
			$i = 0;
			foreach ($mercharticlelist as $mercharticle_id) {
				$i++;
				$this->output("Updating $mercharticle_id ($i of $total)... ");	
				$mercharticle = $this->Kalista_getArticleData($mercharticle_id);
	
				$this->Kalista_updateVariance($mercharticle);
				$this->Kalista_updateProperties($mercharticle_id);
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_updateProperties(string $mercharticle_id) : void {
		try {

			$sqlCek = "
				select 
				mercharticleprop_id, itemproptype_id,  mercharticleprop_keys, mercharticleprop_value
				from
				mst_mercharticleprop 
				where
				    mercharticle_id = :mercharticle_id
				and itemproptype_id = :itemproptype_id
				and mercharticleprop_keys = :mercharticleprop_keys 
			";
			$stmtCek = $this->db_kalista->prepare($sqlCek);

			$sql = "
				select 
				B.itemproptype_id, B.itemstockprop_keys , B.itemstockprop_value  
				from mst_merchitem A inner join mst_itemstockprop B on B.itemstock_id = A.merchitem_id 
				where 
				B.itemproptype_id in (
				'WP-TAXONOMY-BRAND',
				'WP-TAXONOMY-GRO',
				'WP-TAXONOMY-CTG'
				) and A.merchitem_id=(
					select merchitem_id from mst_merchitem 
					where 
					mercharticle_id=:mercharticle_id limit 1
				)			
			";

			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$itemproptype_id = $row['itemproptype_id'];
				$mercharticleprop_keys = $row['itemstockprop_keys'];
				$mercharticleprop_value = $row['itemstockprop_value'];

				// cek apakah properties sudah ada
				$stmtCek->execute([
					':mercharticle_id'=> $mercharticle_id,
					':itemproptype_id' => $itemproptype_id,
					':mercharticleprop_keys' => $mercharticleprop_keys
				]);
				$rowCek = $stmtCek->fetch();
				


				if ($rowCek==null) {
					// insert
					$obj = new \stdClass;
					$obj->mercharticle_id = $mercharticle_id;
					$obj->mercharticleprop_id = uniqid();
					$obj->itemproptype_id = $itemproptype_id;
					$obj->mercharticleprop_keys = $mercharticleprop_keys;
					$obj->mercharticleprop_value = $mercharticleprop_value;
					$obj->_createby = $this->kalistauser;
					$obj->_createdate = date('Y-m-d H:i:s');
				
					$cmd = SqlUtility::CreateSQLInsert("mst_mercharticleprop", $obj);
					$stmtInsert = $this->db_kalista->prepare($cmd->sql);
					$stmtInsert->execute($cmd->params);	
	



				} else {
					$mercharticleprop_id = $rowCek['mercharticleprop_id'];

					// update
					if ($mercharticleprop_value!=$rowCek['mercharticleprop_value']) {
						$obj = new \stdClass;
						$obj->mercharticleprop_id = $mercharticleprop_id;
						$obj->mercharticleprop_value = $mercharticleprop_value;
						$obj->_modifyby = $this->kalistauser;
						$obj->_modifydate = date('Y-m-d H:i:s');
	
						$keys = new \stdClass;
						$keys->mercharticleprop_id = $obj->mercharticleprop_id;

						$cmd = SqlUtility::CreateSQLUpdate("mst_mercharticleprop", $obj, $keys);
						$stmt = $this->db_kalista->prepare($cmd->sql);
						$stmt->execute($cmd->params);
					}

				}

			}		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_updateVariance(array $mercharticle) : void {
		try {
			$obj = new \stdClass;
			$obj->mercharticle_id = $mercharticle['mercharticle_id'];
			$obj->mercharticle_isvarcolor = $mercharticle['variance']['color'];
			$obj->mercharticle_isvarsize = $mercharticle['variance']['size'];
			$obj->_modifyby = $this->kalistauser;
			$obj->_modifydate = date('Y-m-d H:i:s');

			$keys = new \stdClass;
			$keys->mercharticle_id = $obj->mercharticle_id;
		
			$cmd = SqlUtility::CreateSQLUpdate("mst_mercharticle", $obj, $keys);
			$stmt = $this->db_kalista->prepare($cmd->sql);
			$stmt->execute($cmd->params);		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_getArticleData(string $mercharticle_id) : array {
		try {
			$sql = "
				select 
				A.mercharticle_id,
				(select count(distinct merchitem_col) as totalcol from mst_merchitem where mercharticle_id=A.mercharticle_id) as totalcolor,
				(select count(distinct merchitem_size) as totalsize from mst_merchitem where mercharticle_id=A.mercharticle_id) as totalsize
				from mst_mercharticle A
				where 
				A.mercharticle_id = :mercharticle_id
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$row = $stmt->fetch();

			if ($row==null) {
				throw new \Exception("article '$mercharticle_id' tidak ditemukan di mst_mercharticle");
			}

			$merharticle = [
				'mercharticle_id' => $row['mercharticle_id'],
				'variance' => [
					'color' => $row['totalcolor']>1 ? true : false,
					'size' =>  $row['totalsize']>1 ? true : false,
				]
			];

			return $merharticle;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_UpdateMercharticleBatch(array $mercharticlelist) : void {
		try {
			foreach ($mercharticlelist as $mercharticle_id) {
				$obj = new \stdClass;
				$obj->mercharticleprop_id = uniqid();
				$obj->itemproptype_id = 'UPLOADBATCH';
				$obj->mercharticleprop_keys = '';
				$obj->mercharticleprop_value = $this->batchno;
				$obj->mercharticle_id = $mercharticle_id;
				$obj->_createby = $this->kalistauser;
				$obj->_createdate = date('Y-m-d H:i:s');

				$keys = new \stdClass;
				$keys->mercharticle_id = $obj->mercharticle_id;
				$keys->mercharticleprop_keys = $obj->mercharticleprop_keys;
				$keys->itemproptype_id = $obj->itemproptype_id;

				$cmd = SqlUtility::CreateSQLDelete("mst_mercharticleprop", $keys);
				$stmt = $this->db_kalista->prepare($cmd->sql);
				$stmt->execute($cmd->params);	

				$cmd = SqlUtility::CreateSQLInsert("mst_mercharticleprop", $obj);
				$stmtInsert = $this->db_kalista->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	

			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_UpdateWebProperties(string $itemstock_id, array $WebProperties) : void {
				
		try {
			foreach ($WebProperties as $prop) {

				$obj = new \stdClass;
				$obj->itemproptype_id = $prop['itemproptype_id'];
				$obj->itemstockprop_keys = '';
				$obj->itemstockprop_value = $prop['value'];
				$obj->itemstock_id = $itemstock_id;

				$keys = new \stdClass;
				$keys->itemstock_id = $obj->itemstock_id;
				$keys->itemproptype_id = $obj->itemproptype_id;
				$keys->itemstockprop_keys = $obj->itemstockprop_keys;

				
				if ($prop['value']==null) {
					// delete
					$cmd = SqlUtility::CreateSQLDelete("mst_itemstockprop", $keys);
					$stmt = $this->db_kalista->prepare($cmd->sql);
					$stmt->execute($cmd->params);	
				} else {
					// insert or update
					$selectFields = SqlUtility::generateSqlSelect($obj);
					$sqlCheck = "
						select $selectFields
						from mst_itemstockprop
						where
						    itemstock_id = :itemstock_id 
						and itemproptype_id = :itemproptype_id 
						and itemstockprop_keys = :itemstockprop_keys
					";
					$stmtCheck = $this->db_kalista->prepare($sqlCheck);
					$stmtCheck->execute([
						':itemstock_id' => $obj->itemstock_id,
						':itemproptype_id' => $obj->itemproptype_id,
						':itemstockprop_keys' => $obj->itemstockprop_keys
					]);
					$row = $stmtCheck->fetch();
					if ($row==null) {

						$obj->itemstockprop_id = uniqid();
						$obj->_createby = $this->kalistauser;
						$obj->_createdate = date('Y-m-d H:i:s');

						$cmd = SqlUtility::CreateSQLInsert("mst_itemstockprop", $obj);
						$stmtInsert = $this->db_kalista->prepare($cmd->sql);
						$stmtInsert->execute($cmd->params);	
					} else {

						$needtoupdate = false;
						foreach ($row as $fieldname=>$currentdbvalue) {
							$newvalue = $obj->{$fieldname};
							if ( $newvalue != $currentdbvalue) {
								$needtoupdate = $needtoupdate || true;
							}
						}
		
						if ($needtoupdate) {
							$obj->_modifyby = $this->kalistauser;
							$obj->_modifydate = date('Y-m-d H:i:s');
						
							$cmd = SqlUtility::CreateSQLUpdate("mst_itemstockprop", $obj, $keys);
							$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
							$stmtUpdate->execute($cmd->params);		
						}

					}
				}
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_UpdateMstItemStockQty(object $obj) : void {
		try {
			$obj->_modifyby = $this->kalistauser;
			$obj->_modifydate = date('Y-m-d H:i:s');

			$keys = new \stdClass;
			$keys->itemstock_id = $obj->itemstock_id;

			$cmd = SqlUtility::CreateSQLUpdate("mst_itemstock", $obj, $keys);
			$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
			$stmtUpdate->execute($cmd->params);	

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function createStokPosition(string $heinvitem_id, array $stockdata) : object {
		$itemstock_id = $heinvitem_id;
		
		$obj = new \stdClass;
		$obj->itemstockposition_date = $stockdata['dt'];
		$obj->unit_id = $stockdata['unit_id'];
		$obj->site_id = $stockdata['site_id'];
		$obj->dept_id = $stockdata['dept_id'];
		$obj->brand_id = $stockdata['brand_id'];
		$obj->unitmeasurement_id = 'PCS';
		$obj->itemstocksaldo_qty = $stockdata['qty'];
		$obj->itemstocksaldo_value = $stockdata['lastcost'] * $stockdata['qty'];
		$obj->itemstock_id = $itemstock_id;
		return $obj;
	}

	public function Kalista_ResetStokPosition(string $heinvitem_id) : void {
		$itemstock_id = $heinvitem_id;
		try {
			$sql = "delete from mst_itemstockposition where itemstock_id = :itemstock_id";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([
				':itemstock_id' => $itemstock_id
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_UpdateStokPosition(object $obj) : void {
		try {
			
			$obj->itemstockposition_id = uniqid();
			$obj->_createby = $this->kalistauser;
			$obj->_createdate = date('Y-m-d H:i:s');

			$cmd = SqlUtility::CreateSQLInsert("mst_itemstockposition", $obj);
			$stmtInsert = $this->db_kalista->prepare($cmd->sql);
			$stmtInsert->execute($cmd->params);	

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function getStokPosition(string $colname, array &$heinvdata) : array {
		try {
			$stockdata = &$heinvdata['STOCKDATA'];

			$stockposition = [];
			foreach ($stockdata as $stok) {
				$branch_id = $stok['branch_id'];
				$site_id = $heinvdata['MAPS']['BRANCH'][$branch_id]['site_id'];
				$dept_id = $heinvdata['MAPS']['BRANCH'][$branch_id]['dept_id'];
				$unit_id = $heinvdata['MAPS']['BRANCH'][$branch_id]['unit_id'];
	
				$stockposition[$branch_id] = [
					'site_id' => $site_id,
					'dept_id' => $dept_id,
					'unit_id' => $unit_id,
					'brand_id' => $this->brand_id,
					'qty' => $stok[$colname],
					'dt' => $stok['dt'],
					'lastcost' => $stok['lastcost']
				];

			}

			return $stockposition;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	
	public function createMerchItem(array $heinvdata, array $itemdata) : object {		
		$obj = new \stdClass;
		$obj->merchitem_id = $itemdata['heinvitem_id'];
		$obj->merchitem_art = $heinvdata['heinv_art'];
		$obj->merchitem_mat = $heinvdata['heinv_mat'];
		$obj->merchitem_col = $heinvdata['heinv_col'];
		$obj->merchitem_size = $itemdata['size'];
		$obj->merchitem_name = $heinvdata['heinv_name'];
		$obj->merchitem_descr = !empty($heinvdata['heinv_webdescr']) ? $heinvdata['heinv_webdescr'] :  $heinvdata['heinv_descr'];
		$obj->merchitem_colnum = $itemdata['colnum'];
		$obj->merchitem_pcpline = $heinvdata['heinv_other2'];
		$obj->merchitem_pcpgroup = $heinvdata['heinv_group1'];
		$obj->merchitem_pcpcategory = $heinvdata['heinv_group2'];
		$obj->merchitem_colorcode = $heinvdata['heinv_col'];
		$obj->merchitem_colordescr = $heinvdata['heinv_color1'];
		$obj->merchitem_gender = $heinvdata['heinv_gender'];
		$obj->merchitem_fit = $heinvdata['heinv_other3'];
		$obj->merchitem_hscodeship = $heinvdata['heinv_hscode_ship'];
		$obj->merchitem_hscodeina = $heinvdata['heinv_hscode_ina'];
		$obj->merchitem_gtype = $heinvdata['heinv_gtype'];
		$obj->merchitem_labelname = $heinvdata['heinv_plbname'];
		$obj->merchitem_labelproduct = $heinvdata['heinv_produk'];
		$obj->merchitem_bahan = $heinvdata['heinv_bahan'];
		$obj->merchitem_pemeliharaan = $heinvdata['heinv_pemeliharaan'];
		$obj->merchitem_logo = $heinvdata['heinv_logo'];
		$obj->merchitem_dibuatdi = $heinvdata['heinv_dibuatdi'];
		$obj->merchitem_width = $heinvdata['heinv_width'];
		$obj->merchitem_length = $heinvdata['heinv_length'];
		$obj->merchitem_height = $heinvdata['heinv_height'];
		$obj->merchitem_weight = $heinvdata['heinv_weight'];
		$obj->merchitemctg_id = $heinvdata['MAPS']['merchitemctg_id'];
		$obj->merchsea_id = $heinvdata['MAPS']['merchsea_id'];
		$obj->brand_id = $this->brand_id;
		$obj->mercharticle_id = $heinvdata['MAPS']['mercharticle_id'];

		return $obj;
	}

	public function createItemStockBarcode(array $heinvdata, array $itemdata) : object {
		$obj = new \stdClass;
		$obj->itemstockbarcode_text =  $itemdata['barcode'];
		$obj->brand_id = $this->brand_id;
		$obj->itemstock_id =  $itemdata['heinvitem_id'];

		return $obj;
	}


	public function createItemStock(array $heinvdata, array $itemdata) : object {
		$obj = new \stdClass;
		$obj->itemstock_id = $itemdata['heinvitem_id'];
		$obj->itemstock_code = $heinvdata['heinv_art'] . " " . $heinvdata['heinv_mat'] . " " . $heinvdata['heinv_col'] . " " . $itemdata['size'];;
		$obj->itemstock_name = $heinvdata['heinv_name'];
		$obj->itemstock_nameshort = $heinvdata['heinv_name'];
		$obj->itemstock_descr = $heinvdata['heinv_descr'];
		// $obj->itemstock_picture = $heinvdata[''];
		$obj->unitmeasurement_id = 'PCS';
		$obj->itemstock_isdisabled = $heinvdata['heinv_isdisabled'];
		$obj->itemstock_ishascompound = 0;
		$obj->itemstock_issellable = 1;
		$obj->itemstock_priceori = $heinvdata['heinv_priceori'];
		$obj->itemstock_priceadj = $heinvdata['heinv_priceadj'];
		$obj->itemstock_priceadjdate = $heinvdata['heinv_priceadjdate'];
		$obj->itemstock_grossprice = $heinvdata['PRICING']['grossprice'];
		$obj->itemstock_isdiscvalue = $heinvdata['PRICING']['isdiscvalue'];

		$obj->itemstock_disc = $heinvdata['PRICING']['discpercent'];
		$obj->itemstock_discval = $heinvdata['PRICING']['discvalue'];
		$obj->itemstock_sellprice = $heinvdata['PRICING']['nettprice'];
		$obj->itemstock_estcost = $heinvdata['heinv_lastcost'];
		$obj->itemstock_weight = $heinvdata['heinv_weight'];
		$obj->itemstock_length = $heinvdata['heinv_length'];
		$obj->itemstock_width = $heinvdata['heinv_width'];
		$obj->itemstock_height = $heinvdata['heinv_height'];
		// $obj->itemstock_lastqty = 0;
		// $obj->itemstock_lastvalue = 0;
		// $obj->itemstock_lastqtyupdate = null;
		$obj->itemstock_lastrecvid = $heinvdata['heinv_lastrvid'];
		$obj->itemstock_lastrecvdate = $heinvdata['heinv_lastrvdate'];
		$obj->itemstock_lastrecvqty = $heinvdata['heinv_lastrvqty'];
		$obj->itemstock_lastcost = $heinvdata['heinv_lastcost'];
		$obj->itemstock_lastcostdate = $heinvdata['heinv_lastcostdate'];
		// $obj->itemgroup_id = $heinvdata[''];
		//$obj->itemctg_id = $heinvdata['invcls_id'];
		//$obj->itemclass_id = $this->itemclass_id;
		$obj->itemctg_id = $heinvdata['MAPS']['itemctg_id'];
		$obj->itemclass_id = $heinvdata['MAPS']['itemclass_id'];
		
		
		$obj->dept_id =  $this->dept_id;
		// $obj->itemstock_ref = $heinvdata[''];
		// $obj->itemstock_refname = $heinvdata[''];
		$obj->itemstock_uploadbatchcode = $this->batchno;

		return $obj;

	}

	public function Kalista_UpdateMstMerchitem(object $obj) : void {
		try {
			$selectFields = SqlUtility::generateSqlSelect($obj);
			$sqlCheck = "
				select $selectFields
				from mst_merchitem
				where
				merchitem_id = :merchitem_id
			";
			$stmtCheck = $this->db_kalista->prepare($sqlCheck);
			$stmtCheck->execute([
				':merchitem_id' => $obj->merchitem_id
			]);
			$row = $stmtCheck->fetch();
			if ($row==null) {
				$obj->_createby = $this->kalistauser;
				$obj->_createdate = date('Y-m-d H:i:s');

				$cmd = SqlUtility::CreateSQLInsert("mst_merchitem", $obj);
				$stmtInsert = $this->db_kalista->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	
			} else {
				// cek apakah ada perubahan
				$needtoupdate = false;
				foreach ($row as $fieldname=>$currentdbvalue) {
					$newvalue = $obj->{$fieldname};
					if ( $newvalue != $currentdbvalue) {
						$needtoupdate = $needtoupdate || true;
					}
				}

				if ($needtoupdate) {
					$obj->_modifyby = $this->kalistauser;
					$obj->_modifydate = date('Y-m-d H:i:s');
	
					$keys = new \stdClass;
					$keys->merchitem_id = $obj->merchitem_id;
	
					$cmd = SqlUtility::CreateSQLUpdate("mst_merchitem", $obj, $keys);
					$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
					$stmtUpdate->execute($cmd->params);		
				}

			}


		} catch (\Exception $ex) {
			throw $ex;
		}	
	}

	public function Kalista_UpdateMstItemBarcode(object $obj) : void {
		try {
			$selectFields = SqlUtility::generateSqlSelect($obj);
			$sqlCheck = "
				select itemstockbarcode_id, $selectFields
				from mst_itemstockbarcode
				where
				itemstockbarcode_text = :itemstockbarcode_text and brand_id = :brand_id
			";
			$stmtCheck = $this->db_kalista->prepare($sqlCheck);
			$stmtCheck->execute([
				':itemstockbarcode_text' => $obj->itemstockbarcode_text,
				':brand_id' => $obj->brand_id
			]);
			$row = $stmtCheck->fetch();
			if ($row==null) {
				$obj->itemstockbarcode_id = uniqid();
				$obj->_createby = $this->kalistauser;
				$obj->_createdate = date('Y-m-d H:i:s');

				$cmd = SqlUtility::CreateSQLInsert("mst_itemstockbarcode", $obj);
				$stmtInsert = $this->db_kalista->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	

			} else {
				$current_itemstock_id = $row['itemstock_id'];
				$new_itemstock_id = $obj->itemstock_id;
				if ($new_itemstock_id!=$current_itemstock_id) {
					throw new \Exception(
						"Barcode '". $obj->itemstockbarcode_text."' di '"
						           . $new_itemstock_id."' konflik, already exist in '" 
								   . $current_itemstock_id. "'"
					);
				}
			}


		} catch (\Exception $ex) {
			throw $ex;
		}			
	}

	public function Kalista_UpdateMstItemStock(object $obj) : void {

		try {
			$selectFields = SqlUtility::generateSqlSelect($obj);
			$sqlCheck = "
				select $selectFields
				from mst_itemstock
				where
				itemstock_id = :itemstock_id
			";
			$stmtCheck = $this->db_kalista->prepare($sqlCheck);
			$stmtCheck->execute([
				':itemstock_id' => $obj->itemstock_id
			]);
			$row = $stmtCheck->fetch();
			if ($row==null) {
				// insert baru
				$obj->_createby = $this->kalistauser;
				$obj->_createdate = date('Y-m-d H:i:s');
				
				$cmd = SqlUtility::CreateSQLInsert("mst_itemstock", $obj);
				$stmtInsert = $this->db_kalista->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	
			} else {
				// cek apakah ada perubahan
				$needtoupdate = false;
				foreach ($row as $fieldname=>$currentdbvalue) {
					if (in_array($fieldname, ['itemstock_uploadbatchcode'])) {
						continue;
					}					
					$newvalue = $obj->{$fieldname};
					if ( $newvalue != $currentdbvalue) {
						$needtoupdate = $needtoupdate || true;
					}
				}

				if ($needtoupdate) {
					$obj->_modifyby = $this->kalistauser;
					$obj->_modifydate = date('Y-m-d H:i:s');
	
					$keys = new \stdClass;
					$keys->itemstock_id = $obj->itemstock_id;
	
					$cmd = SqlUtility::CreateSQLUpdate("mst_itemstock", $obj, $keys);
					$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
					$stmtUpdate->execute($cmd->params);	

				}
			}



			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_getMercharticleData(array $heinvdata) : array {		
		$obj = new \stdClass;
		$obj->mercharticle_art = $heinvdata['heinv_art'];
		$obj->mercharticle_mat = $heinvdata['heinv_mat'];
		$obj->mercharticle_matname = $heinvdata['heinv_bahan'];
		$obj->mercharticle_name = $heinvdata['heinv_name'];
		$obj->mercharticle_descr = $heinvdata['heinv_descr'];
		$obj->brand_id = $this->brand_id;

		
		try {
			$sqlCheck = "
				select * from mst_mercharticle 
				where 
				    brand_id = :brand_id 
				and mercharticle_art = :art 
				and mercharticle_mat = :mat
			";
			$stmtCheck = $this->db_kalista->prepare($sqlCheck);
			$stmtCheck->execute([
				':brand_id' => $this->brand_id,
				':art' => $heinvdata['heinv_art'],
				':mat' => $heinvdata['heinv_mat']
			]);
			$row = $stmtCheck->fetch();
			if ($row==null) {

				// data tidak ada, imput baru
				$obj->mercharticle_id = uniqid();
				$obj->_createby = $this->kalistauser;
				$obj->_createdate = date('Y-m-d H:i:s');
				
				$cmd = SqlUtility::CreateSQLInsert("mst_mercharticle", $obj);
				$stmtInsert = $this->db_kalista->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);	

				
			} else {

				$needtoupdate = false;
				$needtoupdate = $needtoupdate || $heinvdata['heinv_bahan']!=$row['mercharticle_matname'];
				$needtoupdate = $needtoupdate || $heinvdata['heinv_name']!=$row['mercharticle_name'];
				$needtoupdate = $needtoupdate || $heinvdata['heinv_descr']!=$row['mercharticle_descr'];

				$obj->mercharticle_id = $row['mercharticle_id'];
				if ($needtoupdate) {
					$obj->_modifyby = $this->kalistauser;
					$obj->_modifydate = date('Y-m-d H:i:s');

					$keys = new \stdClass;
					$keys->mercharticle_id = $obj->mercharticle_id;

					$cmd = SqlUtility::CreateSQLUpdate("mst_mercharticle", $obj, $keys);
					$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
					$stmtUpdate->execute($cmd->params);	
	
				}
			}

			$sqlGet = "select * from mst_mercharticle where mercharticle_id = :mercharticle_id"; 
			$stmtGet = $this->db_kalista->prepare($sqlGet);
			$stmtGet->execute([
				':mercharticle_id' => $obj->mercharticle_id
			]);
			$row = $stmtGet->fetch();
		
			return $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function getBranchList(string $base64data, string $region_id) : array {
		
		$sql = "select * from mst_sitemapping where sitemapping_mapfrom = :mapfrom";
		$stmt = $this->db_kalista->prepare($sql);
		
		try {
			$jsondata = base64_decode($base64data);
			$data = json_decode($jsondata, true);
			
			$unmapped = [];
			$branchlist = [];
			foreach ($data as $branch) {
				$branch_id = $branch['branch_id'];
				$branch_name = $branch['branch_name'];

				$mapfrom = "$region_id:$branch_id";
				$stmt->execute([':mapfrom'=>$mapfrom]);
				$map = $stmt->fetch();

				if ($map==null) {
					$unmapped[] = "$region_id:$branch_id $this->region_name $branch_name";
				}

				$branch['unit_id'] = $map['unit_id'];
				$branch['dept_id'] = $map['dept_id'];
				$branch['site_id'] = $map['site_id'];

				$branchlist[$branch_id] = $branch;
			}

			if (count($unmapped)>0) {
				$this->output("BRANCH BELUM DI MAPPING:");
				foreach ($unmapped as $um) {
					$this->output($um);
				}
				throw new \Exception('Ada branch yang belum di map.');
			}


			return $branchlist;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function getInvclsList(string $base64data, string $region_id) : array {


		$sqlCekInvitemCtg = "
			select itemctg_id from mst_itemctg where itemctg_id = :itemctg_id
		";
		$stmtCekInvitemCtg = $this->db_kalista->prepare($sqlCekInvitemCtg);


		$sqlCekMerchitemCtg = "
			select merchitemctg_id from mst_merchitemctg where merchitemctg_id = :merchitemctg_id
		";
		$stmtCekMerchitemCtg = $this->db_kalista->prepare($sqlCekMerchitemCtg);


		try {
			$jsondata = base64_decode($base64data);
			$data = json_decode($jsondata, true);

			$unmapped = [];
			$unreferenceditemctg = [];
			$unreferencedmerchctg = [];
			
			$invclslist = [];
			foreach ($data as $invcls) {
				$invcls_id = $invcls['invcls_id'];
				$invcls_name = $invcls['invcls_name'];


				$this->output("Cek mapping $region_id, $invcls_id $invcls_name ...");
				$map = MAPPING::getInvclsDataMap($region_id, $invcls_id);
				if ($map==null) {
					$unmapped[] = "[$invcls_id]  ($region_id $this->region_name)  -  $invcls_name";
				}

				$invcls['WPBrand'] = !array_key_exists('WPBrand', $map) ? 0 : $map['WPBrand'];
				$invcls['WPGroup'] = !array_key_exists('WPGroup', $map) ? 0 : $map['WPGroup'];
				$invcls['WPCategory'] = !array_key_exists('WPCategory', $map) ? 0 : $map['WPCategory'];
				$invcls['WPSkip'] = !array_key_exists('WPSkip', $map) ? false : $map['WPSkip'];
				
				$invcls['itemctg_id'] = !array_key_exists('itemctg_id', $map) ? $invcls_id : $map['itemctg_id'];
				$invcls['merchitemctg_id'] = !array_key_exists('merchitemctg_id', $map) ? 
													$this->brand_id . "-$invcls_id" : 
													$map['merchitemctg_id'];

				$itemctg_id = $invcls['itemctg_id'];
				$merchitemctg_id = $invcls['merchitemctg_id'];


				$stmtCekInvitemCtg->execute([':itemctg_id'=>$itemctg_id]);
				$row = $stmtCekInvitemCtg->fetch();
				if ($row==null) {
					$unreferenceditemctg[] = "[$itemctg_id] $invcls_name";
				}

				$stmtCekMerchitemCtg->execute([':merchitemctg_id'=>$merchitemctg_id]);
				$row = $stmtCekMerchitemCtg->fetch();
				if ($row==null) {
					$unreferencedmerchctg[] = "[$merchitemctg_id] $invcls_name";
				}


				$invclslist[$invcls_id] = $invcls;
			}

			if (count($unreferenceditemctg)) {
				$this->output("ITEMCTG YANG BELUM DIBUAT di mst_itemctg:");
				foreach ($unreferenceditemctg as $um) {
					$this->output($um);
				}
				throw new \Exception('Ada itemctg yang belum dibuat.');
			}

			if (count($unreferencedmerchctg)) {
				$this->output("MERCHITEMCTG YANG BELUM DIBUAT di mst_merchitemctg:");
				foreach ($unreferencedmerchctg as $um) {
					$this->output($um);
				}
				throw new \Exception('Ada merchitemctg yang belum dibuat.');
			}

			if (count($unmapped)>0) {
				$this->output("INVCLS BELUM DIMAPPING:");
				foreach ($unmapped as $um) {
					$this->output($um);
				}
				throw new \Exception('Ada invcls yang belum dimapping.');
			}



			return $invclslist;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function getInvctgList(string $base64data, string $region_id) : array {
		try {
			$map = MAPPING::getRegionDataMap($region_id);
			$brand_id = $map['brand_id'];


			$jsondata = base64_decode($base64data);
			$data_to_cek = json_decode($jsondata, true);

			
			// get map data from db
			$sqlGetMap = "
				select 
				B.merchitemctgref_value as heinvctg_id, 
				A.itemctg_id as itemctg_id , 
				A.merchitemctg_id as merchitemctg_id,
				A.itemclass_id  as itemclass_id  
				from
				mst_merchitemctg A inner join mst_merchitemctgref B on B.merchitemctg_id = A.merchitemctg_id 
				where 
				A.brand_id = :brand_id
				and B.merchitemctgref_code = 'heinvctg_id'			
			";
			$stmt = $this->db_kalista->prepare($sqlGetMap);
			$stmt->execute([':brand_id'=>$brand_id]);
			$rows = $stmt->fetchall();

			$maps = array();
			foreach ($rows as $row) {
				$heinvctg_id = $row['heinvctg_id'];
				$itemctg_id = $row['itemctg_id'];
				$merchitemctg_id = $row['merchitemctg_id'];
				$itemclass_id = $row['itemclass_id'];

				if (!array_key_exists($heinvctg_id, $maps)) {
					$maps[$heinvctg_id] = [];
				}
				$maps[$heinvctg_id]['itemctg_id'] = $itemctg_id;
				$maps[$heinvctg_id]['merchitemctg_id'] = $merchitemctg_id;
				$maps[$heinvctg_id]['itemclass_id'] = $itemclass_id;

				// sementara
				$maps[$heinvctg_id]['WPBrand'] = '';
				$maps[$heinvctg_id]['WPGroup'] = '';
				$maps[$heinvctg_id]['WPCategory'] = '';
				$maps[$heinvctg_id]['WPSkip'] = '';
			}


			// cek maps
			foreach ($data_to_cek as $d) {
				if (!array_key_exists($heinvctg_id, $maps)) {
					throw new \Exception("maps untuk $heinvctg_id $heinvctg_name belum ada");
				}
			}

			return $maps;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function getSeasonList(string $base64data, string $region_id) : array {

		$sqlCek = "
			select merchsea_id from mst_merchsearef where interface_id ='TB' and merchsearef_code = :season_id ;
		";
		$stmt = $this->db_kalista->prepare($sqlCek);


		try {
			$jsondata = base64_decode($base64data);
			$data = json_decode($jsondata, true);

			$unmapped = [];
			$seasonlist = [];
			foreach ($data as $season) {
				$season_id = $season['season_id'];
				$stmt->execute([':season_id'=>$season_id]);
				$map = $stmt->fetch();
				if ($map==null) {
					$unmapped[] = "$season_id";
				}
				$season['merchsea_id'] = $map['merchsea_id'];
				$seasonlist[$season_id] = $season;
			}

			if (count($unmapped)>0) {
				$this->output("SEASON BELUM DIBUAT:");
				foreach ($unmapped as $um) {
					$this->output($um);
				}
				throw new \Exception('Ada season yang belum dibuat.');
			}

			return $seasonlist;
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
