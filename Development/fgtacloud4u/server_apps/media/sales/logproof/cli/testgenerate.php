<?php

require_once __ROOT_DIR.'/core/webapi.php';	
require_once __ROOT_DIR.'/core/webauth.php';	

class SCENARIO {
	public static $id;
	public static $username;
	public static $param;

	public static function Run() {
		require __DIR__ . '/../apis/xtion-generate.php';
		SCENARIO::$id = '61a5ad8d8f18d';
		
		
		SCENARIO::$username = '5effbb0a0f7d1';  // MANAGER
		// SCENARIO::$username = '5facb8a36127f';  // GM
		// SCENARIO::$username = '5facb8bebf826';  // DIREKTUR
		
		
		// SCENARIO::$param = (object)[
		// 	'approve' => true,
		// 	'approval_note' => 'cek lagi yang bener',
		// 	'bypassauthority' => true
		// ];
		
		$API->auth = new class {
			public function session_get_user() {
				return (object) [
					'username' => SCENARIO::$username
				];
			}			
		};

		$API->useotp = false;
		$API->reqinfo = (object)['modulefullname'=>'media/sales/logproof'];
		$result = $API->execute(SCENARIO::$id);

		print_r($result);

	}
}


console::class(new class($args) extends clibase {
	function execute() {
		SCENARIO::Run();
	}
});

