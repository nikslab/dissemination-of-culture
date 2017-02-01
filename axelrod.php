#!/usr/bin/php
<?php

define("VOCAB", "01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");

// Config
$n = 15;
$x = $n;
$y = $n;
$features = 10;
$traits = 5;
define("ALLELES", substr(VOCAB, 0, $traits));
$reach = 5;
$iterations = $n * 10000;
$report = $n*$n;

$pid = getmypid();
$title = $n . "px_" . $features . "Fx" . $traits . "T_" . "_" . $reach . "_" . $pid . ".gif";

require_once("functions.php");

$agents = randomAgents($x, $y, $features, $traits);

$i = 0;
$stop = false;
$count_no_change = 0;
$last_uniqs = -1;

while (!$stop) {

    $i++;

    $pickX = rand(1, $x);
    $pickY = rand(1, $y);
    $neighbourX = 0;
    $neighbourY = 0;
    while (   !(isset($agents[$neighbourX][$neighbourY]))
           && !(isset($agents[$neighbourX][$neighbourY]))
           && !(($neighbourX == $pickX) && ($neighbourY == $pickY))
          )
    {
        $neighbourX = $pickX + (rand(0, $reach) - 1);
        $neighbourY = $pickY + (rand(0, $reach) - 1);
    }
    $prob = interactionP($agents[$pickX][$pickY], $agents[$neighbourX][$neighbourY]);
    $roll = rand(0,100) / 100;
    if ($roll < $prob) {
        $agents[$pickX][$pickY] = interact($agents[$pickX][$pickY], $agents[$neighbourX][$neighbourY]);
    }

    if (($i % $report) == 0) {
        report($i, $agents);
    }

    if (($i % $iterations) == 0) {
        $answer = readline("Continue?  Type 'no' to stop: ");
        if ($answer == "no") {
            $stop = true;
        }
    }

    $uniqs = count(uniqAgents($agents));
    if ($uniqs < 2) {
        report($i, $agents);
        $stop = true;
    }
    if ($last_uniqs == $uniqs) {
        $count_no_change++;
    } else {
        $count_no_change = 0;
    }
    $last_uniqs = $uniqs;

    if ($count_no_change > 100000) {
        $stop = true;
    }

}

createAnimatedGif($title);
print "\nDone...\n";

?>
