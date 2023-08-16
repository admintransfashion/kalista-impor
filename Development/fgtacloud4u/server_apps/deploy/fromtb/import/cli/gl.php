<?php

require_once __ROOT_DIR.'/core/debug.php';	
require_once __DIR__.'/gl.import.php';

use \FGTA4\debug;


console::class(new class($args) extends cli {

	function __construct($args) {
		$this->args = $args;

		$logfilepath = __LOCALDB_DIR . "/output/otomasi.txt";
		// debug::disable();
		debug::start($logfilepath, "w");

		$this->debugoutput = true;
		$DB_CONFIG = DB_CONFIG[$GLOBALS['MAINDB']];
		$DB_CONFIG['param'] = DB_CONFIG_PARAM[$GLOBALS['MAINDBTYPE']];
		$this->db = new \PDO(
					$DB_CONFIG['DSN'], 
					$DB_CONFIG['user'], 
					$DB_CONFIG['pass'], 
					$DB_CONFIG['param']
		);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


		debug::printtoscreen();
		debug::log('Import GL to TB');
		debug::log("connecting to FRM ...", ['nonewline'=>true]);
		$this->db_frm2 = new \PDO(
			'dblib:host=172.18.10.254;dbname=E_FRM2_BACKUP', 
			'sa', 
			'rahasia',
			null
		);
		$this->db_frm2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		debug::log(color::green . "Connected" . color::reset);
	}

	function execute() {
		$off = true;
		if ($off) {
			debug::log('matikan gl import, osalnya udah selesai diimport.');
			return;
		}

		try {
			$classarg = new \stdClass;
			$classarg->db = $this->db; 
			$classarg->db_frm2 = $this->db_frm2;
			$classarg->args = $this->args;
			(new GL_Import($classarg))->run();
		} catch (\Exeption $ex) {
			throw $ex;
		}		
	}


});

