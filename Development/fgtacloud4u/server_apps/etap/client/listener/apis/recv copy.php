<?php namespace FGTA4\apis;
if (!defined('FGTA4')) {
	die('Forbiden');
}


require_once __ROOT_DIR.'/core/debug.php';

use \FGTA4\exceptions\WebException;
use \FGTA4\debug;

class color {
	public const reset = "\x1b[0m";
	public const red = "\x1b[31m";
	public const green = "\x1b[32m";
	public const yellow = "\x1b[33m";
	public const bright = "\x1b[1m";

}

class Receiver extends WebAPI {
	function __construct() {
		$logfilepath = __LOCALDB_DIR . "/output/etap-client-recv.txt";
		// echo "using log: $logfilepath\r\n";
		debug::start($logfilepath, "w");		
	}


	public function execute($data) {
		debug::log(color::green . "executing..." . color::reset);
		debug::log($data);



		
		fputs($fp, json_encode($data));




		/*
		$descriptorspec = array(
			0 => array("pipe", "r"),  
			1 => array("pipe", "w"), 
			// 2 => array("file", dirname(__FILE__).'/output.txt', "a") 
		);
		 
		 
		$cwd = dirname(__FILE__);
		$env = array('data' => json_encode($data));

		$process = proc_open('python3 streamtokafka.py', $descriptorspec, $pipes, $cwd, $env);

		if (is_resource($process)) {
			fwrite($pipes[0], '<?php print_r($_ENV); ?>');
			fclose($pipes[0]);
			$content = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			$return_value = proc_close($process);

			debug::log( $content);
		}
		*/


		return (object)[
			'status' => 'ok'
		];
	}

}

$API = new Receiver();