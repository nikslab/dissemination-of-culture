#!/usr/bin/php
<?php

define("VOCAB", "01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");

$name = "test1";
$n = 10;
$x = $n;
$y = $n;
$features = 3;
$traits = 10;
define("ALLELES", substr(VOCAB, 0, $traits));
$iterations = $n * 50000;
$report = $n*$n*10;
require_once("functions.php");

$agents = randomAgents(10, 10, 10, 4);
var_dump($agents);

print "\nCount is " . count($agents);

?>
