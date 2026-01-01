<?php
// Settings
$width = 1544;
$height = 500;
$fontFile = '/Library/Fonts/Arial Unicode.ttf';
$iconFile = __DIR__ . '/assets/icon-256x256.png';
$outputFile = __DIR__ . '/assets/banner-1544x500.png';

// Create Canvas
$im = imagecreatetruecolor($width, $height);

// Colors
$bg = imagecolorallocate($im, 246, 247, 249); // #F6F7F9
$charcoal = imagecolorallocate($im, 51, 51, 51); // #333333
$royalBlue = imagecolorallocate($im, 0, 86, 179); // #0056B3

// Fill Background
imagefilledrectangle($im, 0, 0, $width, $height, $bg);

// Load & Place Icon
// We'll resize the 256px icon to 300px for better proportion, or keep it 256. 
// Let's keep it 256 to avoid interpolation blur, or 350 if we scale up.
// Actually, let's just use the 256px icon as is, centered vertically.
if (file_exists($iconFile)) {
    $icon = imagecreatefrompng($iconFile);
    if ($icon) {
        $iconW = imagesx($icon);
        $iconH = imagesy($icon);
        
        $targetIconSize = 256; 
        
        // Position: Left margin 120px
        $iconX = 120;
        $iconY = ($height - $targetIconSize) / 2;
        
        imagecopyresampled($im, $icon, $iconX, $iconY, 0, 0, $targetIconSize, $targetIconSize, $iconW, $iconH);
        imagedestroy($icon);
    }
}

// Text Settings
$titleText = "TableCrafter";
$subText = "WordPress Data Tables & Dynamic Content";

$titleSize = 90; // Large
$subSize = 32;   // Smaller to fit one line

$textX = 420; // 120 + 256 + 44 padding

// Vertical Centering Calc
// Title Baseline roughly at Y=230?
// Let's test positioning.
$titleY = 240; 
$subY = 310;

// Render Title
imagettftext($im, $titleSize, 0, $textX, $titleY, $charcoal, $fontFile, $titleText);

// Render Subtitle
imagettftext($im, $subSize, 0, $textX, $subY, $royalBlue, $fontFile, $subText);

// Save
imagepng($im, $outputFile);
imagedestroy($im);

echo "Banner generated at $outputFile\n";
