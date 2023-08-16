<?php


class MAPPING {


	public static function getRegionDataMap(string $region_id) : array {
		try {
			$DATA = [
				'03700' => ['region_name'=>'Find Kapoor', 'brand_id'=>'FKP', 'unit_id'=>'FKP', 'dept_id'=>'FKP@HO', 'itemclass_id'=>'6221db2b9bf88', 'WPBrand'=>33, 'web_inv_site_id'=>'FKP-TPB'],
				'03400' => ['region_name'=>'Geox', 'brand_id'=>'GEX', 'unit_id'=>'GEX', 'dept_id'=>'GEX', 'itemclass_id'=>'6221db9ac9fd7', 'WPBrand'=>39, 'web_inv_site_id'=>'GEX-GI'],
				//'02600' => ['region_name'=>'Furla', 'brand_id'=>'FLA', 'unit_id'=>'FLA', 'dept_id'=>'FLA'],
			];

			if (!array_key_exists($region_id, $DATA)) {
				throw new \Exception("region $region_id belum di map");
			}

			return $DATA[$region_id];
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public static function getInvclsDataMap(string $region_id, string $invcls_id) : ?array {
		try {
			$regionmap = self::getRegionDataMap($region_id);
			$WPBrand = $regionmap['WPBrand'];

			$DATA = [
				
				// FINDKAPOOR
				'03700' => [
					// 'A12' => ['itemctg_id'=>'A12', 'merchitemctg_id'=>'FKP-A12', 'name'=>'ACS - STRAPS', 'WPBrand'=>$WPBrand, 'WPGroup'=>366, 'WPCategory'=>368],
					// 'B01' => ['itemctg_id'=>'B01', 'merchitemctg_id'=>'FKP-B01', 'name'=>'BAG - BAGS ACCESSORIES', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>370],
					// 'B04' => ['itemctg_id'=>'B04', 'merchitemctg_id'=>'FKP-B04', 'name'=>'BAG - BUCKET / DRAWSTRING', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>372],
					// 'B05' => ['itemctg_id'=>'B05', 'merchitemctg_id'=>'FKP-B05', 'name'=>'BAG - CLUTCHES', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>374],
					// 'B06' => ['itemctg_id'=>'B06', 'merchitemctg_id'=>'FKP-B06', 'name'=>'BAG - CROSSBD/SLNG/POCTE/SDLE', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>377],
					// 'B14' => ['itemctg_id'=>'B14', 'merchitemctg_id'=>'FKP-B14', 'name'=>'BAG - SHOULDER BAGS', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>378],
					// 'B15' => ['itemctg_id'=>'B15', 'merchitemctg_id'=>'FKP-B15', 'name'=>'BAG - TOP HANDLES', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>380],
					// 'B16' => ['itemctg_id'=>'B16', 'merchitemctg_id'=>'FKP-B16', 'name'=>'BAG - TOTE/SHOPR BAG/CR ALL', 'WPBrand'=>$WPBrand, 'WPGroup'=>367, 'WPCategory'=>382],
					// 'B18' => ['itemctg_id'=>'B18', 'merchitemctg_id'=>'FKP-B18', 'name'=>'BAG - BACKPACKS', 'WPSkip' => true],
					// 'F01' => ['itemctg_id'=>'F01', 'merchitemctg_id'=>'FKP-F01', 'name'=>'SLG - CARD CASE / HOLDER', 'WPSkip' => true],
					// 'L01' => ['itemctg_id'=>'L01', 'merchitemctg_id'=>'FKP-L01', 'name'=>'GWP - ACCESSORIES', 'WPSkip' => true],
					// 'XX9' => ['itemctg_id'=>'XX9', 'merchitemctg_id'=>'FKP-X01', 'name'=>'NON MERCHANDISE', 'WPSkip' => true],
				],

				// GEOX
				'03400' => [
				]

			];

			if (!array_key_exists($region_id, $DATA)) {
				throw new \Exception("region $region_id belum di map di function getWPTaxonomyData");
			}

			if (!array_key_exists($invcls_id, $DATA[$region_id])) {
				return null;
			}

			return $DATA[$region_id][$invcls_id];
		} catch (\Exception $ex) {
			throw $ex;
		}	
	}



	public static function getDbInvctgData($db, $region_id) : array {
		try {
			$sql = "";

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}
