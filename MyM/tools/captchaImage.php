<?php

// Modified by G. Sileno
/** by Antonio Palermi 2007 www.captcha.biz */

session_start();
header("Content-Type: image/jpg"); 	// or image/jpg

// to avoid some error on apache server linux
putenv('GDFONTPATH=' . realpath('.'));

// custom parameters
$box_w 				= 110;			// Width of the captha box
$box_h 				= 45;			// Height of the captha box
$font 				= 'Averia-Bold.ttf';	// Used font
$font_size 			= 20; 			// Size of the font
$font_angle 		= 0; 			// Angle of text
$font_x 			= 10; 			// Margin left
$font_y 			= 10; 			// Margin top
$thickness			= 0;			// Thickness of lines
$lines_angle		= 5;			// angle of lines (from 1 to 10)
$lines_number		= 5;			// numbers of lines

// set a passcode 
$pass = '';
$nchar = 5;							// number of characters in image
for($i=1;$i<=$nchar;$i++){
	$charOnumber = rand(1,2);
	if ($charOnumber == 1){
		$chars = 'AvfrnsfqhkyukQNGnfcCKeMVFNiGHoJQZBY';	// custom used characters
		$n = strlen($chars) - 1;
		$x = rand(1,$n);
		$char = substr($chars,$x,1);
		$pass .= $char;
	} else {
		//$number = rand(3,7);
		$numbers = array(1,2,3,4,7);	// custom used numbers
		$n = count($numbers)-1;
		$number = $numbers[rand(1,$n)];
		$pass .= $number;
	}
}

// set the session 
$_SESSION["pass"] = $pass;

// create the image resource
$image = ImageCreatetruecolor($box_w,$box_h);

$color_background 	= ImageColorAllocate($image, 255, 255, 255);    // Bakground color (RGB)
$color_text 		= ImageColorAllocate($image, 0, 0, 0);     		// Text color
$color_lines 		= ImageColorAllocate($image, 255, 255, 255);	// Lines color 
$color_lines2       = ImageColorAllocate($image, 255, 255, 255);    //

// set background 
imagefill($image, 0, 0, $color_background);

// set text 
imagettftext($image, $font_size, $font_angle, $font_x, $font_size + $font_y, $color_text, $font, $pass);

// set lines
imagesetthickness($image,$thickness);

$step = $box_w/$lines_number;

switch($lines_angle){
	case 1:
	$start 	= 5;
	$end	= 5;
	break;
	case 2:
	$start 	= 5;
	$end	= 10;
	break;
	case 3:
	$start 	= 5;
	$end	= 15;
	break;
	case 4:
	$start 	= 5;
	$end	= 20;
	break;
	case 5:
	$start 	= 5;
	$end	= 25;
	break;
	case 6:
	$start 	= 5;
	$end	= 30;
	break;
	case 7:
	$start 	= 5;
	$end	= 35;
	break;
	case 8:
	$start 	= 5;
	$end	= 40;
	break;
	case 9:
	$start 	= 5;
	$end	= 45;
	break;
	case 10:
	$start 	= 5;
	$end	= 50;
	break;
}

$a = $start;
$b = $end;

for($i=1;$i<=$lines_number;$i++){
	$l = $start;
	$l1 = $end;
	imageline($image, $l, 1, $l1, $box_h, $color_lines);
	$start = $a + ($step*$i-1);
	$end = $start + $b;
}

$a = -10;
$b = 90;

for($i=1;$i<=$lines_number;$i++){
	$l = $start;
	$l1 = $end;
	imageline($image, $l, 1, $l1, $box_h, $color_lines2);
	$start = $a + ($step*$i-1);
	$end = $start + $b;
}

// created image 
imagejpeg($image);
imagedestroy($image);
?>
