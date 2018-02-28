<?php
require "header.php";

$measuring = (isset($_POST["measuring"]) && ($_POST["measuring"] == "1"));

// First get battery numbers
$batt_nums = array();
if ($measuring)
    $batt_nums[] = $_POST["battnum"];
else
{
    // Decompose battery numbers into array
    require_once "helpers.php";
    $batt_nums = getBatteryNumbers($_POST["battnum"]);
}

// Now get battery IDs
require_once "connect.php";
$conn = connect();
$s = $conn->prepare("select battery.id as id from battery, batmake where batmake.id=battery.make_id and num=? and type_id=? and user_id=?") or die($conn->error);
$batt_ids = array();
foreach ($batt_nums as $key => $batt_num)
{
    $s->bind_param("iii", $batt_num, $_POST["type"], $userid);
    $s->execute();
    $rs = $s->get_result();
    $assoc = $rs->fetch_assoc();
    if ($assoc == NULL)
        header("location: addcharge.php?message=".rawurlencode("ERROR : No such battery"));
    $batt_id = $assoc["id"];
    $batt_ids[] = $batt_id;
    $rs->close();
}
$s->close();

$charging_device = intval($_POST["charging_device"]);
$charged_date = $_POST["mydate"];
if ($measuring)
// This is a measurement, add it
{
    $s = $conn->prepare("insert into evt(batt_id, mah, device_id, mydate, evt_type) values(?, ?, ?, ?, 1)") or die($conn->error);
    $s->bind_param("iiis", $batt_ids[0], $measure = intval($_POST["measure"]), $charging_device, $charged_date) or die($conn->error);
    $s->execute() or die($conn->error);
    $s->close();
}

if ((!$measuring) || ($_POST["recharged"] == "Yes"))
// Need to add a charge
{
    $mah = (isset($_POST["charge_amount"]) && (!empty($_POST["charge_amount"]))) ? $_POST["charge_amount"] : NULL;
    
    $s = $conn->prepare("insert into evt(batt_id, device_id, mydate, mah, evt_type) values(?, ?, ?, ?, 0)") or die($conn->error);
    foreach ($batt_ids as $key => $batt_id)
    {
        $s->bind_param("iisi", $batt_id, $charging_device, $charged_date, $mah) or die($conn->error);
        $s->execute() or die($conn->error);
    }
    $s->close();
}
$conn->close();
header("Location: addcharge.php?measuring=".($measuring ? "1" : "0")."&message=".rawurlencode("Added ".($measuring ? "measure" : "charge")." successfully"));
die();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Measure</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
There was an error for some reason...
</body>
</html>
