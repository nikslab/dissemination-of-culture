#!/usr/bin/php
<?php

define("VOCAB", "01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");

// Read config file
$config_file = "";
if (isset($argv[1])) {
    $config_file = $argv[1];
}
if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
    var_dump($config);
} else {
    print "Usage: ./axelrod.php <config>\nCan't find configuration.  Exiting...\n";
    exit(0);
}

// Config
$n = $config['matrix_size'];
$x = $n;
$y = $n;
$features = $config['features'];
$traits = $config['traits'];
$mutation_rate = $config['mutation'];
$gif_delay = $config['gif_delay'];
$report = $config['report'];
$save = false;
if ($config['save'] == 'yes') {
    $save = true;
}
define("ALLELES", substr(VOCAB, 0, $traits));

$reach = $config['reach'];

$iterations = $n * 10000;
$report = $n*$n*$report; // After each one has had a chance to act

$pid = getmypid();
$title = $n . "px_" . $features . "Fx" . $traits . "T_" . $reach . "_" . $pid;

require_once("functions.php");
require_once("visualization.php");

// Generate initial matrix
$agents = randomAgents($x, $y, $features, $traits);
$c = count($agents);
if ($c > 0) {
    print 'Generated agents in ' . $x . 'x' . $y . "matrix.\n";
} else {
    print "Could not generate matrix, maybe something wrong with config file.\n";
    exit(0);
}

$i = 0;
$stop = false;
$count_no_change = 0;
$last_uniqs = -1;

// Main loop
while (!$stop) {

    $i++;

    // Pick a cell
    $pickX = rand(1, $x);
    $pickY = rand(1, $y);

    // Pick a neighbour
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

    // Interact?
    $prob = interactionP($agents[$pickX][$pickY], $agents[$neighbourX][$neighbourY]);
    $roll = rand(0,100) / 100;
    if ($roll < $prob) {
        $agents[$pickX][$pickY] = interactAxelrod($agents[$pickX][$pickY], $agents[$neighbourX][$neighbourY]);
    }

    // Mutation
    $roll = rand(0,100) / 100;
    if ($roll < $mutation_rate) {
        $new_allele = rand(0, count(ALLELES)-1);
        $pick_gene = rand(0, strlen($agents[$pickX][$pickY])-1);
        $dna_split = str_split($agents[$pickX][$pickY]); // split into characters
        $dna_split[$pick_gene] = $new_allele;
        $new_dna = "";
        foreach ($dna_split as $d) {
            $new_dna .= $d;
        }
        $agents[$pickX][$pickY] = $new_dna;
    }

    if (($i % $report) == 0) {
        report($i, $agents);
        if ($save) {
            saveAgents("dat/" . $title . ".db", $i, $agents);
        }
    }

    if (($i % $iterations) == 0) {
        $answer = readline("Continue?  Type 'no' to stop, Enter to continue: ");
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

createAnimatedGif("img/" . $pid, "animated/" . $title . ".gif", $gif_delay);

// Delete source files

print "\nDone...\n";

?>
