<?php
require "header.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Batteries Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
<?php
if (isset($_GET["message"]))
    echo htmlentities(rawurldecode($_GET["message"]))."<br>";
?>
    <a href="addmove.php">Load/Unload Batteries into Device</a><br>
    <a href="addcharge.php?measuring=0">Charge Batteries</a><br>
    <a href="addcharge.php?measuring=1">Add Measurement</a><br>
    <a href="addbatt.php">Add Batteries</a><br>
    <a href="adddevice.php">Add Device</a><br>
    <a href="addmake.php">Add Make</a><br>
    <a href="searchbat.php">Search Batteries</a>
</body>
</html>