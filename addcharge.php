<?php
require "header.php";
$measuring = (isset($_GET["measuring"]) && ($_GET["measuring"] == "1"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $measuring ? "Add Measurement on Battery" : "Add Battery Charge";?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
<?php
if (isset($_GET["message"]))
    echo htmlentities(rawurldecode($_GET["message"]))."<br>";
?>
<form action="doaddcharge.php" method="post">
<?php echo "<input type=\"hidden\" name=\"measuring\" value=\"".($measuring ? "1" : "0")."\">"; ?>
<table>
<tr><td>Battery Number<?php echo $measuring ? "" : "s<br>ex: 32-34,40,44-48"; ?></td><td><input type="text" name="battnum"></td></tr>
<tr><td>Type</td><td><select name="type">
<?php
require_once "connect.php";
require_once "helpers.php";
$conn = connect();
showBatteryTypeOptions($conn);
?>
</select></td></tr>
<tr><td><?php echo $measuring ? "Measuring Device" : "Charging Device";?></td><td><select name="charging_device">
<?php
showChargingDeviceOptions($conn, $measuring);
$conn->close();
?>
</select></td></tr>
<?php
if ($measuring)
{
?>
<tr><td>Measurement</td><td><input type="text" name="measure"></td></tr>
<tr><td>Charged at end of Measurement?</td><td><input type="checkbox" name="recharged" value="Yes" checked></td></tr>
<?php
}
?>
<tr><td>Date <?php echo $measuring ? "Measured" : "Charged";?></td><td><?php echo "<input type=\"text\" name=\"mydate\" value=\"".date("Y-m-d H:i:s")."\">"; ?></td></tr>
<tr><td>Charge measure (opt)</td><td><input type="text" name="charge_amount"></td></tr>

</table>
<input type="submit">
</form>
<p>
<a href="main.php">Back to Menu</a>
</body>
</html>
