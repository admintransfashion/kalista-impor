<?php

$filepath = __DIR__ . '/test.txt';

$dt = date('Y-m-d H:i:s');


$fp = fopen($filepath, "a+");
fputs($fp, "$dt\r\n");
fclose($fp);


