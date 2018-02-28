<?php
require_once "header.php";
require_once "connect.php";
$conn = connect();

$query = "";
$paramid = -1;
$objname = "";

if (isset($_GET["devid"]))
{
	$query = "select image from device where id=?";
	$paramid = intval($_GET["devid"]);
	$objname = "device";
}
else if (isset($_GET["makeid"]))
{
	$query = "select image from batmake where id=?";
	$paramid = intval($_GET["makeid"]);
	$objname = "make";
}

$s = $conn->prepare($query) or die($conn->error);
$s->bind_param("i", $paramid) or die($conn->error);
$s->execute() or die($conn->error);
$rs = $s->get_result() or die ($conn->error);
$assoc = $rs->fetch_assoc() or die ($conn->error);
if ($assoc == NULL)
	die("No such $objname $paramid");
$img = $assoc['image'];
$rs->close();
$s->close();
$conn->close();
header('Content-Type: image/jpeg');
echo $img;
?>
