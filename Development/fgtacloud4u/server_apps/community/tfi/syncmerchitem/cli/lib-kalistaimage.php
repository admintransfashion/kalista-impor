<?php


class KalistaImage {

	private $cdb;
	private $targetImagesLocation;
	private $tempImagesLocation;
	private $brand_id;


	function __construct($cdb) {
		$this->cdb = $cdb;
	}

	public function saveall(array $images, array $sizes, array $params, Closure $fn_progress) : void {
		$this->targetImagesLocation = $params['targetImagesLocation'];
		$this->tempImagesLocation = $params['tempImagesLocation'];
		$this->brand_id = $params['brand_id'];

		try {

			if (!is_dir($this->targetImagesLocation)) {
				throw new \Exception("direktori '" . $this->targetImagesLocation . "' tidak ditemukan");
			}

			if (!is_dir($this->tempImagesLocation)) {
				throw new \Exception("direktori '" . $this->tempImagesLocation . "' tidak ditemukan");
			}

			foreach ($images as $couchdb_id =>$data) {

				$parentImagePath = null;
				$originalSize = null;
				foreach ($data as $sizekey=>$filedata) {
					$size = (int)$sizekey;
					if ($size>0) {

						$fn_progress([]);

						$imagefiledata = $filedata['imagefiledata'];
						$filedirname = $imagefiledata['filedirname'];
						$filebasename = $imagefiledata['filebasename'];
						$fileimagepath = $imagefiledata['imagepath'];
						$targetFilePath = implode('/', [$this->targetImagesLocation, $fileimagepath]);

						if (!is_file($targetFilePath)) {
							$dirTarget = dirname($targetFilePath);
							if (!is_dir($dirTarget)) {
								mkdir($dirTarget);
							}
	
							if (!is_dir($dirTarget)) {
								throw new \Exception("direktori '" . $dirTarget . "' tidak ditemukan");
							}						
	
							if ($parentImagePath==null) {
								$parentImagePath = $this->downloadImage($couchdb_id, $filebasename);
								$imgsize = getimagesize($parentImagePath);
								$width = (int)$imgsize[0];
								$height = (int)$imgsize[1];
								$originalSize = ['width'=>$width, 'height'=>$height];
							}
	
							$this->copyResize($parentImagePath, $targetFilePath, $size, $originalSize);
						}

					}
				}

			}
		} catch (\Exception $ex) {
			throw $ex;
		}


	}

	public function downloadImage(string $couchdb_id, string $filebasename) : string {
		try {
			$tempfilepathname = implode('/', [$this->tempImagesLocation, $filebasename]);

			$doc = null;
			try { 
				$doc = $this->cdb->getAttachment($couchdb_id, 'filedata'); 
			} catch (\Exception $ex) {
				$doc = null;
			}

			if ($doc!=null) {
				$output_file = $tempfilepathname;
				$base64_attachmentdata = $doc->attachmentdata;
				$type = $doc->type;
				$ifp = fopen( $output_file, 'wb'); 
				$attachmentdata = explode( ',', $base64_attachmentdata);
				$filedata = base64_decode($attachmentdata[1]);
				fwrite( $ifp, $filedata);
				fclose($ifp);

				return $tempfilepathname;
			}

			return null;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function copyResize(string $imagepath, string $targetimagefilepath, int $size, array $originalSize) : void {
		$width = $originalSize['width'];
		$height = $originalSize['height'];
		$sizemax = $width > $height ? $width : $height;
		$targetsize = $size;
		
		try {
			$dirname = dirname($targetimagefilepath);
			if (!is_dir($dirname)) {
				throw new \Exception("direktori '$dirname' does not exists");
			}

			// buat image bg putih dengan ukuran sizemax
			$imgCanvas = imagecreatetruecolor($sizemax, $sizemax);
			$bgcolor = imagecolorallocate($imgCanvas, 255, 255, 255);
			imagefill($imgCanvas, 0, 0, $bgcolor);

			// untk gabung image, imagepath harus ditaruh di tengah-tengah
			$locx = (int)(($sizemax - $width) / 2);
			$locy = (int)(($sizemax - $height) / 2);

			// gabungkan canvas dengan imagepath
			$imgSource = imagecreatefromjpeg($imagepath);
			imagecopymerge($imgCanvas, $imgSource, $locx, $locy, 0, 0, $width, $height, 100);


			// resise ukuran image ke $targetsize
			$imgStandart =  imagecreatetruecolor($targetsize, $targetsize);
			$bgcolor = imagecolorallocate($imgStandart, 255, 255, 255);
			imagefill($imgStandart, 0, 0, $bgcolor);
			imagecopyresized($imgStandart, $imgCanvas, 0, 0, 0, 0, $targetsize, $targetsize, $sizemax, $sizemax);


			imagejpeg($imgStandart, $targetimagefilepath, 99);

			imagedestroy($imgStandart);
			imagedestroy($imgCanvas);
			imagedestroy($imgSource);
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}