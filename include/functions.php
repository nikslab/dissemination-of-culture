<?php
/*******************************************************************************
 * functions.php
 *
 * Various helper functions for
 * https://github.com/nikslab/dissemination-of-culture
 *
 * By Nik Stankovic, Jan 2017
 *
 ******************************************************************************/

/*
 * Generates an x times y matrix of dna strings with $features length
 */
function agentsRandom($x, $y, $features, $traits)
{
    $agents = [];

    for ($xx=1; $xx<=$x; $xx++) {
        for ($yy=1; $yy<=$y; $yy++) {
            $dna = "";
            for ($ff=1; $ff<=$features; $ff++) {
                $trait = rand(0, $traits-1);
                $dna .= substr(ALLELES, $trait, 1);
            }
            $agents[$xx][$yy] = $dna;
        }
    }

    return $agents;
}


/*
 * Calculates probability of interaction given two dna strings.
 * Probability is equal to the percent of "genes" that are the same.
 */
function interactionP($dna1, $dna2)
{
    $dna1_split = str_split($dna1); // split into characters
    $dna2_split = str_split($dna2);
    $c = count($dna1_split); // number of features

    // Figure out how many traits match between two dna strings
    $result = 0;
    for ($i=0; $i<$c; $i++) {
        if ($dna1_split[$i] == $dna2_split[$i]) {
            $result++;
        }
    }

    $result = $result / $c; // probability

    return $result;
}


/*
 * Makes two dna strings "interact" as per Axelrod.
 * $dna1 will take on one characteristic of $dna2 which is different
 */
function interactAxelrod($dna1, $dna2)
{
    // Find all that are different
    $dna1_split = str_split($dna1); // split into characters
    $dna2_split = str_split($dna2);

    $c = count($dna1_split); // how many features

    // Find indexes which are different
    $different = [];
    for ($i=0; $i<$c; $i++) {
        if ($dna1_split[$i] != $dna2_split[$i]) {
            $different[] = $i;
        }
    }

    $c = count($different);
    if ($c > 0) { // if there is a difference
        $pick = rand(0, $c-1); // pick one
        $selected = $different[$pick];
        $dna1_split[$selected] = $dna2_split[$selected];
    }

    // Roll back dna1
    $result = "";
    foreach ($dna1_split as $gene) {
        $result .= $gene;
    }

    return $result;
}


/**************************
 * Reporting
 **************************/

/*
 * Whatever needs to be done in each reporting cycle
 */
function report($config, $i, $agents)
{

    // Figure out top three agents
    $ag = [];
    array_walk_recursive($agents, function($a) use (&$ag) { $ag[] = $a; });
    $c = [];
    $total = 0;
    foreach ($ag as $n=>$dna) {
        if (isset($c[$dna])) {
            $c[$dna]++;
        } else {
            $c[$dna] = 1;
        }
        $total++;
    }
    arsort($c);
    $k = 1;
    foreach ($c as $dna=>$n) {
        $o[$k] = $dna;
        $oc[$k] = round(($n / $total) * 100, 1);
        $k++;
        if ($k > 3) {
            break;
        }
    }
    $first = $o[1]; $first_percent = $oc[1];
    $second = ''; $second_percent = 0;
    $third = ''; $third_percent = 0;
    $top = $first_percent;
    if (isset($o[2])) { $second = $o[2]; $second_percent = $oc[2]; $top += $second_percent; }
    if (isset($o[3])) { $third = $o[3]; $third_percent = $oc[3]; $top += $third_percent; }

    $uniqs = count(uniqAgents($agents));
    $flat = [];
    array_walk_recursive($agents,function($v, $k) use (&$flat){ $flat[] = $v; });
    $total = count($flat);
    $percent = round((1 - ($uniqs / $total)) * 100, 0);
    $size = $config['matrix_size']*$config['matrix_size'];
    $generation = round($i / $size, 0);
    print "= $i (gen $generation): $uniqs uniq ($percent% same) $first|$second|$third = $top%\n";

    $pid = getmypid();
    if ($config['gif'] == 'yes') {
        createGif("img/" . $pid, 300, $i, $agents);
    }

    if ($config['save'] == 'yes') {
        saveAgents('dat/' . $config['filename'] . ".dat", $i, $agents);
    }

    
    // Log
    $log_file = 'dat/log.txt';
    $pid = getmypid();
    $stamp = time();
    $log_line = $stamp."|".$config['name']."|".$pid."|".$config['matrix_size']."|".
                $config['features']."|".$config['traits']."|".$config['reach']."|".
                $config['mutation_rate']."|"."$i|$generation|$uniqs|$percent|$top|$first|$first_percent|$second|$second_percent|$third|$third_percent\n";
    file_put_contents($log_file, $log_line, FILE_APPEND);
}


/*
 * Saves the agents array.
 * Agents are saved in a three dimensional array.
 * The first dimension becomes the iteration ($i).
 */
function saveAgents($target_file, $i, $agents)
{
    $db = [];
    if (file_exists($target_file)) {
        $db = unserialize(file_get_contents($target_file));
    }
    $db["$i"] = $agents;
    file_put_contents($target_file, serialize($db));
}


/*
 * Calculates number of unique agents in the matrix
 */
function uniqAgents($agents)
{
    $result = [];
    array_walk_recursive($agents,function($v, $k) use (&$result){ $result[] = $v; });
    $uniqs = array_count_values($result);
    return $uniqs;
}


?>
