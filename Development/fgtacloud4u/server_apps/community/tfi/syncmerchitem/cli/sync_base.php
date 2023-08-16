<?php

require_once __DIR__ . '/sync_maps.php';


class SyncBase {

	const QTY_THRESHOLD = 1;
	const IMAGE_SIZES = ["32", "100", "150", "300", "600"];
	const MAIN_IMAGE_SIZES = ["600"];


	protected $updatetb;
	protected $batchno;
	protected $KalistaDb;
	protected $KalistaFs;
	protected $FrmDb;
	protected $TfiWebDb;
	protected $region_id;
	protected $region_name;
	protected $kalistauser;
	protected $itemclass_id;

	protected $unit_id;
	protected $brand_id;
	protected $dept_id;

	protected $WPBrand;
	protected $web_inv_site_id;
	protected $web_pricing_id;

	protected $tempDir;

	protected $imagesLocation;
	protected $tempImagesLocation;
	protected $targetImagesLocation;

	protected $TfiWebBaseUrl;
	protected $TfiWebPostAuthor;


	public function Setup() {
		$this->tbupdate = getenv('TBUPDATE');
		$this->batchno = getenv('BATCHNO');
		$this->KalistaDb = getenv('KALISTADB');
		$this->KalistaFs = getenv('KALISTAFS');
		$this->FrmDb = getenv('FRMDB');
		$this->TfiWebDb = getenv('TFIWEBDB');
		$this->region_id = getenv('REGIONID');
		$this->kalistauser = getenv('KALISTAUSER');
		$this->web_pricing_id = getenv('WEBPRICING');
		$this->imagesLocation = getenv('IMAGELOCATION');
		$this->tempDir = getenv('TEMPDIR');
		$this->tempImagesLocation = getenv('TEMPIMAGELOCATION');
		$this->targetImagesLocation = getenv('TARGETIMAGELOCATION');
		$this->TfiWebBaseUrl = getenv('TFIWEB_BASE_URL');
		$this->TfiWebPostAuthor = getenv('TFIWEB_POSTAUTHOR');
		

		try {
			$map = MAPPING::getRegionDataMap($this->region_id);

			$this->region_name = $map['region_name'];
			$this->unit_id = $map['unit_id'];
			$this->dept_id = $map['dept_id'];
			$this->brand_id = $map['brand_id'];
			$this->itemclass_id = $map['itemclass_id'];
			$this->WPBrand = $map['WPBrand'];
			$this->web_inv_site_id = $map['web_inv_site_id'];

		} catch (\Exception $ex) {
			throw $ex;
		}	


	}


	public function output($str, ?array $options = null) {
		echo $str;


		$nonewline = false;
		if (is_array($options)) {
			$nonewline =  (array_key_exists('nonewline', $options)) ?  $options['nonewline'] : false;
		};

		if (!$nonewline) {
			echo "\r\n";
		}
	}


	public function ReportError(\Exception $ex) : void {
		
	}


}