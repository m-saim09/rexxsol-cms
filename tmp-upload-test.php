<?php

$image = imagecreatetruecolor(1000, 1000);
for ($x = 0; $x < 1000; $x += 30) {
    for ($y = 0; $y < 1000; $y += 30) {
        $color = imagecolorallocate($image, ($x * 17) % 256, ($y * 13) % 256, (($x + $y) * 7) % 256);
        imagefilledrectangle($image, $x, $y, $x + 30, $y + 30, $color);
    }
}
imagepng($image, __DIR__.'/large-upload-test.png', 0);
imagedestroy($image);