<?php namespace FGTA4;

require_once __ROOT_DIR.'/core/cliworker.php';	

/*
 * Syncronisasi RV dari TB ke kalista
 * 
 */
console::class(new class($args) extends cliworker {
	private array $params;
	
	function __construct($args) {
		parent::__construct($args);

		// get executing parameter
		$this->params = $args->params;
	}

	function execute() {
		$name = $this->params['--name'];
		$pid = $this->params['--pid'];
		$username = $this->params['--username'];

		echo "Execute long process\r\n";

		try {
			$this->registerProcess($name, $pid, $username);
			
			for ($i=0; $i<=10; $i++) {
				$cancel = $this->isRequestingCancel($pid);
				if ($cancel) {
					$this->cancelProcess($pid);
					break;
				}

				sleep(1);







				$progress = 10*$i;
				$taskdescr = "progress sofar $progress%";
				$this->updateProcess($pid, $username, $progress, $taskdescr);
			}

			$this->commitProcess($pid, $username);
		} catch (\Exception $ex) {
			echo "\x1b[31m"."ERROR"."\x1b[0m"."\r\n";
			echo "\x1b[1m".$ex->getMessage()."\x1b[0m"."\r\n";
		}
	}
});