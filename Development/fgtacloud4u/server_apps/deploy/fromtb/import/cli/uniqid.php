<?php
$file = __DIR__ . '/uniqid.txt';


$fp = fopen($file, "w");
for ($i=0; $i<34332; $i++) {
	fputs($fp, uniqid()."\r\n");
}

fclose($fp);




