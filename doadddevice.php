<?php
require_once "header.php";

if ((!isset($_FILES["image"])) || ($_FILES["image"]["error"] > 0))
    die("Error in getting image : ".(isset($_FILES["image"]) ? $_FILES["image"]["error"] : "No Image Set"));
if ($_FILES["image"]["size"] > 100000)
    die("Image is too big, please not more than 100kb");
//echo $_FILES["image"]['name'];
//echo substr(strrchr($_FILES[$index]['name'],'.'), 1);
if (strtolower(substr(strrchr($_FILES["image"]['name'],'.'), 1)) != "jpg")
    die("Only jpg files are accepted");
$img = file_get_contents($_FILES["image"]['tmp_name']);
/*$realimage = imagecreatefromstring($img);
$ratio = 1.0*imagesx($realimage)/imagesy($realimage);*/

require_once "connect.php";
$conn = connect();
$s = $conn->prepare("insert into device(name, shortname, type_id, nb_batt, dev_type, image) values(?, ?, ?, ?, ?, ?)") or die($conn->error);
$s->bind_param("ssiiib", $_POST["name"], $_POST["shortname"], $battype = intval($_POST["type"]), $nb_batt = intval($_POST["nb_batt"]), $const_on = ($_POST["const_on"] == "Yes" ? 1 : 0), $null = NULL) or die($conn->error);
$s->send_long_data(5, $img);
$s->execute() or die($conn->error);
$s->close();
$conn->close();
header("Location: main.php?message=".rawurlencode("Added device successfully"));
die();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Device</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
There was an error for some reason...
</body>
</html>
