<?php

include('GIFEncoder.class.php');

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


function interactionP($dna1, $dna2)
{
    $dna1_split = str_split($dna1);
    $dna2_split = str_split($dna2);
    $c = count($dna1_split);
    $result = 0;
    for ($i=0; $i<$c; $i++) {
        if ($dna1_split[$i] == $dna2_split[$i]) {
            $result++;
        }
    }
    $result = $result / $c;
    return $result;
}


function interact($dna1, $dna2)
{
    // Find all that are different
    $dna1_split = str_split($dna1);
    $dna2_split = str_split($dna2);
    $c = count($dna1_split);
    $different = [];
    for ($i=0; $i<$c; $i++) {
        if ($dna1_split[$i] != $dna2_split[$i]) {
            $different[] = $i;
        }
    }

    $c = count($different);
    if ($c > 0) {
        $pick = rand(0, $c-1);
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


/*
 * Reporting
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


function uniqAgents($agents)
{
    $result = [];
    array_walk_recursive($agents,function($v, $k) use (&$result){ $result[] = $v; });
    $uniqs = array_count_values($result);
    return $uniqs;
}


/*
 * Imaging
 */

function dna2dec($dna)
{
    $digits = str_split($dna);
    $digits = array_reverse($digits);
    $max = strlen(ALLELES);
    $dec = 0;
    $power = 0;
    foreach ($digits as $d) {
        $pos = strpos(ALLELES, $d) + 1;
        $dec +=  $pos * ($max**$power);
        $power++;
    }
    return $dec;
}


function dec2rgb($color_num)
{
    $R = 0;
    if ($color_num > (255*255)) {
        $R = floor($color_num / (255*255));
        $color_num -= $R*255*255;
    }

    $G = 0;
    if ($color_num > 255) {
        $G = floor($color_num / 255);
        $color_num -= $G*255;
    }

    $B = $color_num;

    $result["R"] = $R;
    $result["G"] = $G;
    $result["B"] = $B;

    return $result;
}


function dna2rgb($dna)
{
    $colors = 16777216; // 255*255*255
    $options = (strlen(ALLELES) - 1)**strlen($dna);
    $increment = $options / $colors;

    $color_num = floor(dna2dec($dna) / $increment);
    $RGB = dec2rgb($color_num);

    return $RGB;
}


function createGif($i, $agents)
{
    $img_size = 300; // pixels, rough
    $pid = getmypid();
    $in = str_pad($i, 12, '0', STR_PAD_LEFT);
    $filename = "img/$pid-$in.gif";
    $agents_size = count($agents);
    $pixel_size = floor($img_size / $agents_size);
    $img_real_size = $pixel_size * $agents_size;
    $gd = imagecreatetruecolor($img_real_size, $img_real_size);
    for ($xx=0; $xx<$agents_size; $xx++) {
        for ($yy=0; $yy<$agents_size; $yy++) {
            $xxx = $xx + 1;
            $yyy = $yy + 1;
            $dna = $agents[$xxx][$yyy];
            $color_RGB = dna2rgb($dna);
            $R = $color_RGB["R"];
            $G = $color_RGB["G"];
            $B = $color_RGB["B"];
            $color = imagecolorallocate($gd, $R, $G, $B);
            imagefilledrectangle($gd, $xx*$pixel_size, $yy*$pixel_size, $xx*$pixel_size+$pixel_size, $yy*$pixel_size+$pixel_size, $color);
        }
    }
    imagegif($gd, $filename);
}

function createAnimatedGif($title)
{
    $pid = getmypid();
    $filename = "animated/" . $title;
    $frames = [];
    $framed = [];

    // Get a list of files for our PID
    $mask = "img/$pid-*.gif";
    $file_list = glob($mask);

    // Load frames from disk
    foreach ($file_list as $frame_file) {
        ob_start();
        $image = imagecreatefromgif($frame_file);
        imagegif($image);
        $frames[] = ob_get_contents();
        ob_end_clean();
        $framed[] = 10;
        unlink($frame_file);
    }

    // Created animated GIF
    $gif = new GIFEncoder($frames, $framed, 1, 2, 0, 0, 0, 'bin');

    // Save to file
    $fp = fopen($filename, 'w');
    fwrite($fp, $gif->GetAnimation());
    fclose($fp);
}
?>
