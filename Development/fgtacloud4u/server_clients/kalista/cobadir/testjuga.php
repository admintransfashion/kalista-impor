<?php


if (array_key_exists('HTTP_REFERER', $_SERVER)) {
	$urlproxy = $_SERVER['HTTP_REFERER'];
	$proxypathinfo = parse_url($urlproxy);

	//print_r($path);
	$proxypath = $proxypathinfo['path'];

	

}
