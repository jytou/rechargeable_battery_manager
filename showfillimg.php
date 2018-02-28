<?php
require_once "header.php";
require_once "connect.php";
$img = imagecreate(101, 21);
$green = imagecolorallocate($img, 0, 255, 0);
$red = imagecolorallocate($img, 255, 100, 100);
$black = imagecolorallocate($img, 0, 0, 0);
imagefilledrectangle($img, 0, 0, 100, 20, $red);
$percent = intval(100.0 * $_GET["value"] / $_GET["total"]);
imagefilledrectangle($img, 0, 0, $percent, 20, $green);
imagestring($img, 4, 0, 0, $_GET["value"], $black);
header('Content-Type: image/png');
imagepng($img);
?>
