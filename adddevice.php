<?php
require "header.php";
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
<form action="doadddevice.php" method="post" enctype="multipart/form-data">
<table>
<tr><td>Name</td><td><input type="text" name="name"></td></tr>
<tr><td>Short Name</td><td><input type="text" name="shortname"></td></tr>
<tr><td>Type</td><td><select name="type">
<?php
require_once "connect.php";
$conn = connect();
require_once "helpers.php";
showBatteryTypeOptions($conn);
$conn->close();
?>
</select></td></tr>
<tr><td>Nb Batteries</td><td><input type="text" name="nb_batt"></td></tr>
<tr><td>Constantly On</td><td><input type="checkbox" name="const_on" value="Yes"></td></tr>
<tr><td>Image</td><td><input type="file" name="image"></td></tr>
</table>
<input type="submit">
</form>
<p>
<a href="main.php">Back to Menu</a>
</body>
</html>
