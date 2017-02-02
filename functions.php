<?php


/*
 * Generates an x times y matrix of dna strings with $features length
 */
function randomAgents($x, $y, $features, $traits)
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
 * Calculates probability of interaction given two dna strings
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
 * $dna1 will take one characteristic of $dns2 which is different
 */
function interact($dna1, $dna2)
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
function report($i, $agents)
{
    $uniqs = count(uniqAgents($agents));
    $flat = [];
    array_walk_recursive($agents,function($v, $k) use (&$flat){ $flat[] = $v; });
    $total = count($flat);
    $percent = round((1 - ($uniqs / $total)) * 100, 0);
    print "==== $i: $uniqs ($percent% same)\n";
    createGif($i, $agents);
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
