<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';	
require_once __ROOT_DIR.'/core/sqlutil.php';	
require_once __ROOT_DIR.'/core/couchdbclient.php';

require_once __DIR__ . '/sync_maps.php';
require_once __DIR__ . '/sync_base.php';

use \FGTA4\utils\SqlUtility;
use \FGTA4\CouchDbClient;
use \FGTA4\clibase;
use \FGTA4\console;


ini_set('memory_limit', '512M');

class SYNC extends SyncBase {


	const STD_IMAGE_SIZE = 900;

	public function Run() {
		try {
			$region_id = $this->region_id;
			$brand_id = $this->brand_id;
			$batchno = $this->batchno;
	
			if (!is_dir($this->tempImagesLocation)) {
				die("die at " . __LINE__ . "\r\nTemp Directory '". $this->tempImagesLocation  ."' not found!\r\n\r\n");
			}
			
			if (!is_dir($this->imagesLocation)) {
				die("die at " . __LINE__ . "\r\Image Directory '". $this->imagesLocation  ."' not found!\r\n\r\n");
			}
	
	
	
	
	
			$mercharticlelist = $this->Kalista_GetMercharticleList($brand_id, $batchno);
	
			$this->i = 0;
			$this->total = count($mercharticlelist);
			foreach ($mercharticlelist as $mercharticle_id) {
				$this->i++;
	
				$mercharticle = $this->Kalista_getArticleData($mercharticle_id); 
				if ($this->brand_id=='FKP') {
					$this->Kalista_updateImageFKP($mercharticle, function($entry) {  
						if ($entry=="/var/www/html/others/fkpimages/FB20RG1PN/4.jpg") {
							$stop = true; // untuk kepeluan debug, buat break point di sini
						}
						$this->output("processing $this->i of $this->total, $entry");
					});
				}
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_updateImageFKP(array $mercharticle, $fn_callback) : void {

		$mercharticle_id = $mercharticle['mercharticle_id'];
		$mercharticle_art = $mercharticle['mercharticle_art'];
		$merchImageDir = implode('/', [$this->imagesLocation, $mercharticle_art]);

		try {

			// sementara hapus dulu
			// $sql = "delete from mst_merchpic where mercharticle_id = '$mercharticle_id' ";
			// $this->db_kalista->query($sql);

			$sqlCek = "
					select * 
					from mst_merchpic 
					where 
					mercharticle_id=:mercharticle_id and merchpic_col=:merchpic_col and merchpic_name=:merchpic_name";
			$stmt = $this->db_kalista->prepare($sqlCek);


			if (is_dir($merchImageDir)) {


				foreach ($mercharticle['colors'] as $colorvariance) {
				
					if ($handle = opendir($merchImageDir)) {
						while (false !== ($entry = readdir($handle))) {
							if ($entry != "." && $entry != "..") {

								$fn_callback("$merchImageDir $entry");

								$stmt->execute([
									':mercharticle_id' => $mercharticle_id,
									':merchpic_col' => $colorvariance,
									':merchpic_name' => $entry
								]);
								$row = $stmt->fetch();
								if ($row!=null) {
									continue;
								}
								$fileImagePath = implode('/', [$merchImageDir, $entry]);
								$targetImagePath = implode('/', [$this->tempImagesLocation, $entry]);

								if (!is_file($fileImagePath)) {
									continue;
								}


								copy($fileImagePath, $targetImagePath);
								$newImagePath = $this->imageResize($targetImagePath, self::STD_IMAGE_SIZE);
								if (is_file($newImagePath)) {
									
									$doc = new \stdClass;
									$doc->doctype = "mst_merchpic";
									$doc->docid = implode("/", [$this->brand_id, $mercharticle_id, uniqid()]);
									$doc->docmerch = implode("/", [$this->brand_id, $mercharticle_id]);
									$doc->docart = implode("/", [$this->brand_id, $mercharticle_art]);
									$doc->mercharticle_id = $mercharticle_id;
									$doc->mercharticle_art = $mercharticle_art;
									$doc->name = $entry;
									$doc->size = filesize($newImagePath);
									$doc->type = "image/jpeg";
									$doc->width = self::STD_IMAGE_SIZE;
									$doc->height = self::STD_IMAGE_SIZE;
									$doc->batchno = $this->batchno;
						
									// upload to couchdb
									$ret = $this->uploadToCouchDb($mercharticle_id, $colorvariance, $newImagePath, $doc);
									$file_couchdbid = $ret['file_id'];
									$file_rev = $ret['rev'];
									
									$this->Kalista_UpdateImage(
										$mercharticle_id, 
										$colorvariance, 
										$newImagePath, 
										$file_couchdbid, 
										$file_rev
									);

									unlink($newImagePath);
								}

							}
						}
						closedir($handle);
					}
				}


				// update image header
				$data = $this->Kalista_updateMercharticle($mercharticle);
				$this->Kalista_updateItemstock($mercharticle, $colorvariance, $data);

				//die("die at " . __LINE__ . "\r\n\r\n");


			}

			//die("die at " . __LINE__ . "\r\n\r\n");
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_updateItemstock(array $mercharticle, string $colorvariance, array $data) : void {
		
		$mercharticle_id = $mercharticle['mercharticle_id'];

		try {

			// ambil seluruh data image dengan colorvariance
			$sql = "
				select 
				merchpic_couchdbid as couchdbid, merchpic_file as file, merchpic_name as name
				from mst_merchpic 
				where 
				mercharticle_id = :mercharticle_id
				order by name
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([
				':mercharticle_id' => $mercharticle_id
			]);
			$rowsFiles = $stmt->fetchall();



			// update data
			$sql = "
				select 
				merchitem_id 
				from mst_merchitem 
				where mercharticle_id = :mercharticle_id and merchitem_col = :merchitem_col
			";
		
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([
				':mercharticle_id' => $mercharticle_id,
				':merchitem_col' => $colorvariance
			]);

			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$itemstock_id = $row['merchitem_id'];
				$this->Kalista_updateItemstockPicture($itemstock_id, $rowsFiles);
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_updateItemstockPicture(string $itemstock_id, array $rowsFiles) : void {
		try {

			// hapus seluruh data image
			$sql = "delete from mst_itemstockpic where itemstock_id = :itemstock_id";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':itemstock_id'=>$itemstock_id]);

			// masukkan seluruh informasi image
			foreach ($rowsFiles as $row) {
				$obj = new \stdClass;
				$obj->itemstockpic_id = uniqid();	
				$obj->itemstockpic_couchdbid = $row['couchdbid'];	
				$obj->itemstockpic_name = $row['name'];	
				$obj->itemstockpic_descr = '';	
				$obj->itemstockpic_order = 0;	
				$obj->itemstockpic_file = $row['file'];	
				$obj->itemstock_id = $itemstock_id;
				$obj->_createby = $this->kalistauser;
				$obj->_createdate = date('Y-m-d H:i:s');
	
				$cmd = SqlUtility::CreateSQLInsert("mst_itemstockpic", $obj);
				$stmtInsert = $this->db_kalista->prepare($cmd->sql);
				$stmtInsert->execute($cmd->params);				
			}

			// update header itemstock
			if (count($rowsFiles)>0) {
				$row = $rowsFiles[0];

				$obj = new \stdClass;
				$obj->itemstock_id = $itemstock_id;
				$obj->itemstock_couchdbid = $row['couchdbid'];	
				$obj->itemstock_picture = $row['file'];	

				$keys = new \stdClass;
				$keys->itemstock_id = $obj->itemstock_id;

				$cmd = SqlUtility::CreateSQLUpdate("mst_itemstock", $obj, $keys);
				$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
				$stmtUpdate->execute($cmd->params);				
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_updateMercharticle(array $mercharticle) : ?array {
		try {
		
			$data = null;

			$mercharticle_id = $mercharticle['mercharticle_id'];
			$sql = "
				select merchpic_couchdbid, merchpic_file, merchpic_name from mst_merchpic
				WHERE mercharticle_id = :mercharticle_id
				order by merchpic_name limit 1
			";
			$stmt = $this->db_kalista->prepare($sql);
			$stmt->execute([':mercharticle_id'=>$mercharticle_id]);
			$row = $stmt->fetch();
			if ($row!=null) {

				$this->output("update mercarticle images '$mercharticle_id'");
				$obj = new \stdClass;
				$obj->mercharticle_id = $mercharticle_id;
				$obj->mercharticle_couchdbid  = $row['merchpic_couchdbid'];
				$obj->mercharticle_picture = $row['merchpic_file'];

				$keys = new \stdClass;
				$keys->mercharticle_id = $obj->mercharticle_id;

				$cmd = SqlUtility::CreateSQLUpdate("mst_mercharticle", $obj, $keys);
				$stmtUpdate = $this->db_kalista->prepare($cmd->sql);
				$stmtUpdate->execute($cmd->params);				

				$data = [
					'couchdbid' => $obj->mercharticle_couchdbid,
					'file' => $obj->mercharticle_picture
				];

			} 

			return $data;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	public function uploadToCouchDb(string $mercharticle_id, string $colorvariance, string $imagepath, object $doc) : array {
		try {
			
			$file_id = "mst_merchpic/$mercharticle_id/" . uniqid();

			$file_base64data = 'data:' . $doc->type . ';base64,' .  base64_encode(file_get_contents($imagepath));

			$overwrite = true;
			$res = $this->cdb->addAttachment($file_id, $doc, 'filedata', $file_base64data, $overwrite);	
			$rev = $res->asObject()->rev;

			$ret = [
				'file_id' => $file_id,
				'rev' => $rev
			];

			return $ret;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Kalista_UpdateImage(
		string $mercharticle_id, 
		string $colorvariance, 
		string $imagepath, 
		string $file_couchdbid, 
		string $file_rev
	) : void {
		try {
			$path_parts = pathinfo($imagepath);
			$imagebasename = $path_parts['basename'];	
			
			$obj = new \stdClass;
			$obj->mercharticle_id = $mercharticle_id;
			$obj->merchpic_couchdbid = $file_couchdbid;
			$obj->merchpic_col = $colorvariance;
			$obj->merchpic_name = $imagebasename;
			$obj->merchpic_descr = '';
			$obj->merchpic_order = 0;
			$obj->merchpic_file = $file_rev;
			$obj->merchpic_id = uniqid();
			$obj->_createby = $this->kalistauser;
			$obj->_createdate = date('Y-m-d H:i:s');

			$cmd = SqlUtility::CreateSQLInsert("mst_merchpic", $obj);
			$stmtInsert = $this->db_kalista->prepare($cmd->sql);
			$stmtInsert->execute($cmd->params);				

			

		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	public function imageResize(string $imagepath, int $targetsize) : string {
		try {
			
			$path_parts = pathinfo($imagepath);
			$dirname = $path_parts['dirname'];
			$filename = $path_parts['filename'];
			
			$squareimagepath = implode('/', [$dirname, "$filename-square.jpg"]);
			// if ($path_parts['extension']!='jpg' && $path_parts['extension']!='jpeg') {
			// 	throw new \Exception("image $imagepath bukan JPG.");
			// }

			// ambil informasi ukuran image
			$imgsize = getimagesize($imagepath);
			$width = $imgsize[0];
			$height = $imgsize[1];
			$sizemax = $width > $height ? $width : $height;
			
			// buat image bg putih dengan ukuran sizemax
			$imgCanvas = imagecreatetruecolor($sizemax, $sizemax);
			$bgcolor = imagecolorallocate($imgCanvas, 255, 255, 255);
			imagefill($imgCanvas, 0, 0, $bgcolor);

			// untk gabung image, imagepath harus ditaruh di tengah-tengah
			$locx = (int)(($sizemax - $width) / 2);
			$locy = (int)(($sizemax - $height) / 2);


			// gabungkan canvas dengan imagepath
			if ($path_parts['extension']=='jpg' || $path_parts['extension']=='jpeg') {
				$imgSource = imagecreatefromjpeg($imagepath);
				$newimagepath = implode('/', [$dirname, $filename.".jpg"]);

			} else if ($path_parts['extension']=='png') {
				$imgSource = imagecreatefrompng($imagepath);
				$newimagepath = implode('/', [$dirname, $filename.".png"]);

			} else {
				throw new \Exception("image $imagepath bukan PNG/JPG.");
			}
			
			imagecopymerge($imgCanvas, $imgSource, $locx, $locy, 0, 0, $width, $height, 100);
			
			// resise ukuran image ke $targetsize
			$imgStandart =  imagecreatetruecolor($targetsize, $targetsize);
			$bgcolor = imagecolorallocate($imgStandart, 255, 255, 255);
			imagefill($imgStandart, 0, 0, $bgcolor);
			imagecopyresized($imgStandart, $imgCanvas, 0, 0, 0, 0, $targetsize, $targetsize, $sizemax, $sizemax);
			
			imagejpeg($imgStandart, $squareimagepath, 99);
			

			imagedestroy($imgStandart);
			imagedestroy($imgCanvas);
			imagedestroy($imgSource);

			unlink($imagepath);
			rename($squareimagepath, $newimagepath);
			
			return $newimagepath;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function Kalista_getArticleData(string $mercharticle_id) : array {
		try {
			$sql = "
				select 
				A.mercharticle_id, A.mercharticle_art, A.mercharticle_mat,  
				A.mercharticle_isvarcolor, A.mercharticle_isvarsize
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

			$mercharticle = [
				'mercharticle_id' => $row['mercharticle_id'],
				'mercharticle_art' => $row['mercharticle_art'],
				'mercharticle_mat' => $row['mercharticle_mat'],
				'mercharticle_isvarcolor' => $row['mercharticle_isvarcolor'],
				'mercharticle_isvarsize' => $row['mercharticle_isvarsize'],
				'colors' => []
			];


			$sql = "
				select 
				distinct merchitem_col as merchitem_col 
				from mst_merchitem 
				where 
				mercharticle_id = :mercharticle_id
			";
			$stmtcol = $this->db_kalista->prepare($sql);
			$stmtcol->execute([':mercharticle_id'=>$mercharticle_id]);
			$rows = $stmtcol->fetchall();
			foreach ($rows as $row) {
				$mercharticle['colors'][] = $row['merchitem_col'];
			}

			return $mercharticle;
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

		$this->cdb = new CouchDbClient((object)DB_CONFIG[$this->KalistaFs]);

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