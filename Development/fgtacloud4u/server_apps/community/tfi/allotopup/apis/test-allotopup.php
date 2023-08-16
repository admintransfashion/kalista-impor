<?php
/**
 * testing top up allo
 * 
 */

 
require_once implode('/', [__DIR__ , 'allotopup.lib.php']);


use \transfashion\allo\allotopuplib;


$at = new allotopuplib();

try {

	// Request QRCode topup
	$parameter = (object)[
		'topup_id' => '12345678123456781234567812345678',
		'storeId' => '2222',
		'cashierId' => '3333',
		'value' => 100000,
	];
	
	$result = $at->generate_barcode_topup($parameter);
	print_r($result);



	// Cek status .....
	$parameter->barcode = $result->barcode; 
	$parameter->referenceNo = $result->referenceNo;
	$waiting = true;
	while ($waiting) {
		echo "cek status...\r\n";
		$status = $at->cek_status($parameter);
		print_r($status);
		if ($status->topUpStatus=='00') {
			echo "\r\nTOP UP BERHASIL\r\n\r\n";
			$waiting = false;
		}		
		sleep(2);
	}

} catch (\Exception $ex) {
	$code = $ex->getCode();
	echo "\r\nERROR ($code).\r\n";
	echo $ex->getMessage();
	echo "\r\n\r\n";
}





