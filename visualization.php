<?php

/**************************
 * Visualization
 **************************/

include('GIFEncoder.class.php'); // for animated GIFs


/*
 * Converts a dna string to a decimal number
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


/*
 * Converts a decimal number to an RGB color
 */
function dec2rgb($color_num)
{
    $R = 0;
    $d = 255*255;
    if ($color_num > $d) {
        $R = floor($color_num / $d);
        $color_num -= $R*$d;
    }

    $G = 0;
    $d = 255;
    if ($color_num > $d) {
        $G = floor($color_num / $d);
        $color_num -= $G*$d;
    }

    $B = $color_num;

    $result["R"] = $R;
    $result["G"] = $G;
    $result["B"] = $B;

    return $result;
}


/*
 * Convert dna string to an RGB color
 */
function dna2rgb($dna)
{
    $colors = 16777216; // 255*255*255
    $options = (strlen(ALLELES) - 1)**strlen($dna);
    $increment = $options / $colors;

    $color_num = floor(dna2dec($dna) / $increment);
    $RGB = dec2rgb($color_num);

    return $RGB;
}


/*
 * Create a square GIF of roughly 300 pixels wide x long from the $agents matrix
 * Using GD library
 */
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


/*
 * Generates an animated GIF from a number of snapshots
 */
function createAnimatedGif($dir, $title, $delay)
{
    $pid = getmypid();
    $filename = $dir . $title;
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
        $framed[] = $delay;
        unlink($frame_file);
    }

    // Created animated GIF
    $gif = new GIFEncoder($frames, $framed, 1, 1, 0, 0, 0, 'bin');

    // Save to file
    $fp = fopen($filename, 'w');
    fwrite($fp, $gif->GetAnimation());
    fclose($fp);
}

?>
