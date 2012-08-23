<?php
header("Content-Type: image/jpg");  // or image/jpg

// custom parameters
$box_w = 125;   // Width of the captha box
$box_h = 35;   // Height of the captha box
$font = 'arial.ttf'; // Used font
$font_size = 24;    // Size of the font
$font_angle = 0;    // Angle of text
$font_x = 10;    // Margin left
$font_y = 5;    // Margin top
$color_background_r = 'white';   // Bakground color: black, white, green, blu, red
$color_text = 'black';  // Text color:   black, white, green, blu, red
$color_lines = 'green';  // Lines color:  black, white, green, blu, red
$thickness = 1;   // Thickness of lines
$lines_angle = 5;   // angle of lines (from 1 to 10)
$lines_number = 5;   // numbers of lines

// create the image resource
$image = ImageCreatetruecolor($box_w,$box_h);

// set colors
$color_background = ImageColorAllocate($image, 255, 255, 255);
$color_text = ImageColorAllocate($image, 0, 0, 0);

// set background 
imagefill($image, 0, 0, $color_background);

// set text 
imagettftext($image, $font_size, $font_angle, $font_x, $font_size + $font_y, $color_text, $font, $text);

// created image 
imagejpeg($image);
imagedestroy($image);
?>
