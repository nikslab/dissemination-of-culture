<?php
/*******************************************************************************
 * visualization.php
 *
 * Creates GIFs and animated GIFs from two dimensional array matrix
 *
 * Created for use in https://github.com/nikslab/dissemination-of-culture
 * but can be easily adapted for other applications where you need to
 * visualize an array.
 *
 * TODO: Make it a class
 *
 * By Nik Stankovic, Jan 2017
 *
 ******************************************************************************/

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
 * Create a square GIF of roughly $size pixels wide and long from the
 * $agents two dimensional array.  $i is the order (generation).
 * Filename will be $target_dir/$pid_$i.gif
 * Target dir will be created if it doesn't exist.
 * No error checking on creating target dir!
 * Uses GD library.
 */
function createGif($target_dir, $size, $i, $agents)
{
    $pid = getmypid();
    $in = str_pad($i, 12, '0', STR_PAD_LEFT);
    $filename = "$target_dir/$pid-$in.gif";

    // Make sure target dir exists, if not create it
    if (!file_exists($target_dir) && !is_dir($target_dir)) {
        mkdir($target_dir);
    }

    $agents_size = count($agents);
    $pixel_size = floor($size / $agents_size);
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
 * Generates an animated GIF ($target) from a number of frames in $source_dir
 * Delay is in ms.
 * No error checking on frames.  If something fails, your problem.
 */
function createAnimatedGif($source_dir, $target, $delay)
{
    $frames = [];
    $framed = []; // holding delays between frames

    // Get a list of files
    $mask = "$source_dir/*.gif";
    $file_list = glob($mask);

    // Load frames from disk
    foreach ($file_list as $frame_file) {
        ob_start();
        $image = imagecreatefromgif($frame_file);
        imagegif($image);
        $frames[] = ob_get_contents();
        ob_end_clean();
        $framed[] = $delay;
    }

    // Generate animated GIF
    $gif = new GIFEncoder($frames, $framed, 1, 1, 0, 0, 0, 'bin');

    // Save to file
    $fp = fopen($target, 'w');
    fwrite($fp, $gif->GetAnimation());
    fclose($fp);
}

?>
