<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

class SalesOrder {

	function __construct($params) {
		$this->auth = $params->auth;
		$this->reqinfo = $params->reqinfo;
		$this->db = $params->db;
	}


	function SaveToSalesOrder($mediaorder_id) {

	}


}

