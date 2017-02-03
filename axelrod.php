#!/usr/bin/php
<?php

define("VOCAB", "01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");


/*
 * Read config file
 */
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

/*
 * Parse config file
 */
$n = $config['matrix_size'];
$x = $n;
$y = $n;
$features = $config['features'];
$traits = $config['traits'];
$gif_delay = $config['gif_delay'];
$report = $config['report'];
$save = false;
if ($config['save'] == 'yes') {
    $save = true;
}
define("ALLELES", substr(VOCAB, 0, $traits)); // subset of VOCAB!

$reach = $config['reach'];

$iterations = $n * 10000;
$report = $n*$n*$report; // After each one has had a chance to act

// Filename for data files and visualization
$pid = getmypid();
$name = "";
if (isset($config['name'])) {
    $name = $config['name'] . "_";
}
$title = $name . $n . "px_" . $features . "Fx" . $traits . "T_" . $reach . "_" . $pid;
$config['filename'] = $title;

require_once("include/functions.php");
require_once("include/visualization.php");

// Generate initial matrix
$agents = agentsRandom($x, $y, $features, $traits);
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

// Iterate
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
    if (isset($config['mutation_rate'])) {
        $roll = rand(0,100) / 100;
        if ($roll < $config['mutation_rate']) {

            // Generate mutation
            $new_allele = rand(0, count(ALLELES)-1);
            $pick_gene = rand(0, strlen($agents[$pickX][$pickY])-1);

            // Splice and insert mutation
            $dna_split = str_split($agents[$pickX][$pickY]); // split into characters
            $dna_split[$pick_gene] = $new_allele;

            // Roll it back into string
            $new_dna = "";
            foreach ($dna_split as $d) {
                $new_dna .= $d;
            }
            $agents[$pickX][$pickY] = $new_dna;

        }
    }

    if (($i % $report) == 0) {
        report($config, $i, $agents);
    }

    if (($i % $iterations) == 0) {
        $answer = readline("Continue?  Type 'no' to stop, Enter to continue: ");
        if ($answer == "no") {
            $stop = true;
        }
    }

    $uniqs = count(uniqAgents($agents));
    if ($uniqs < 2) {
        report($config, $i, $agents);
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

if ($config['gif'] == 'yes') {
    createAnimatedGif("img/" . $pid, "animated/" . $title . ".gif", $gif_delay);
}

print "\nDone.\n\n";

?>
