<?php namespace FGTA4\module; if (!defined('FGTA4')) { die('Forbiden'); } 

class allotopup_pageHandler {


	public function LoadPage() : void {
		$this->caller->preloadscripts = [
			'jslibs/jsbarcode.min.js'
		];


	}
}