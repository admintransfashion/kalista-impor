<?php namespace FGTA4\module; if (!defined('FGTA4')) { die('Forbiden'); } 

class Docs extends WebModule {

	public function LoadPage() {
		$this->preloadscripts = [
			'jslibs/pdf.js'
		];
	
	}


}


$MODULE = new Docs();