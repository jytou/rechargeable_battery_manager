<?php
require "header.php";

$devid = intval($_POST["devid"]);
$load = intval($_POST["load"]);
$mydate = $_POST["mydate"];

require_once "connect.php";
$conn = connect();

if ($load == 1)
/**
 * WE ARE LOADING BATTERIES INTO A DEVICE
 */
{
    /**
     * GET THE NUMBER OF BATTERIES FOR THIS DEVICE
     */
    $s = $conn->prepare("select nb_batt, shortname, type_id from device where device.id=?") or die ($conn->error);
    $s->bind_param("i", $devid) or die($conn->error);
    $s->execute();
    $rs = $s->get_result();
    if ($assoc = $rs->fetch_assoc())
    {
        $deviceshortname = $assoc["shortname"];
        $nbbattexpected = $assoc["nb_batt"];
        $batttype = $assoc["type_id"];
    }
    else
        die("Whoopsie, no such device found.");
    $rs->close();
    $s->close();

    /**
     * INSERT EACH BATTERY MOVE
     */
    $sInsert = $conn->prepare("insert into evt(batt_id, mydate, device_id, evt_type) values(?, ?, ?, 2)") or die ($conn->error);
    $sSelect = $conn->prepare("select battery.id as id from battery, batmake where num=? and type_id=? and batmake.id=make_id") or die ($conn->error);
    for ($i=0; $i < $nbbattexpected; $i++)
    { 
        $battnum = intval($_POST["batt$i"]);
        $sSelect->bind_param("ii", $battnum, $batttype) or die($conn->error);
        $sSelect->execute();
        $rs = $sSelect->get_result();
        if ($assoc = $rs->fetch_assoc())
        {
            $curid = $assoc["id"];
            $sInsert->bind_param("isi", $curid, $mydate, $devid) or die($conn->error);
        }
        else
            die("Oh Oh... No battery with number $battnum and type $batttype was found");
        $sInsert->execute();
        $rs->close();
    }
    $sInsert->close();
    $sSelect->close();
    $conn->close();

    header("Location: main.php?message=".rawurlencode("Batteries loaded successfully"));
    die();
}
else
/**
 * WE ARE UNLOADING BATTERIES FROM A DEVICE
 */
{
    $action = ($_POST["empty"] == "Yes" ? 4 : 3);
    /**
     * GET THE DEVICE INFO
     */
    $s = $conn->prepare("select nb_batt, name, shortname from device where device.id=?") or die ($conn->error);
    $s->bind_param("i", $devid) or die($conn->error);
    $s->execute();
    $rs = $s->get_result();
    while ($assoc = $rs->fetch_assoc())
    {
        $devicename = $assoc["name"];
        $deviceshortname = $assoc["shortname"];
        $nbbattexpected = $assoc["nb_batt"];
    }
    $rs->close();
    $s->close();

    /**
     * GET THE EXACT DATE OF THE LAST LOAD INTO THIS DEVICE
     */
    $s = $conn->prepare("select mydate from evt where device_id=? and evt_type=2 order by mydate desc limit 1") or die ($conn->error);
    $s->bind_param("i", $devid) or die($conn->error);
    $s->execute();
    $rs = $s->get_result();
    $assoc = $rs->fetch_assoc();
    $loaddate = $assoc["mydate"];
    $rs->close();
    $s->close();

    /**
     * GRAB THE BATTERY IDS FROM THEIR LAST MOVE INTO THIS DEVICE
     */
    $s = $conn->prepare("select batt_id from evt where device_id=? and mydate=? and evt_type=2") or die ($conn->error);
    $s->bind_param("is", $devid, $loaddate) or die($conn->error);
    $s->execute();
    $rs = $s->get_result();
    $battids = array();
    $nbbattfound = 0;
    while ($assoc = $rs->fetch_assoc())
    {
        $battids[] = $assoc["batt_id"];
        $nbbattfound++;
    }
    $rs->close();
    $s->close();
    if ($nbbattexpected != $nbbattfound)
        die("Bad number of batteries found. $nbbattexpected were expected and $nbbattfound were found.");

    /**
     * INSERT EACH BATTERY MOVES
     */
    $s = $conn->prepare("insert into evt(batt_id, mydate, device_id, evt_type) values(?, ?, ?, ?)") or die ($conn->error);
    for ($i=0; $i < $nbbattfound; $i++)
    { 
        $battid = $battids[$i];
        $s->bind_param("isii", $battid, $mydate, $devid, $action) or die($conn->error);
        $s->execute();
    }
    $s->close();

    if (isset($_POST["charging_device"]) && (!empty($_POST["charging_device"])))
    {
        $charge_dev_id = $_POST["charging_device"];
        /**
         * INSERT THE CHARGE
         */
        $s = $conn->prepare("insert into evt(batt_id, mydate, device_id, evt_type) values(?, ?, ?, 0)") or die($conn->error);
        for ($i=0; $i < $nbbattfound; $i++)
        { 
            $battid = $battids[$i];
            $s->bind_param("isi", $battid, $mydate, $charge_dev_id) or die($conn->error);
            $s->execute() or die($conn->error);
        }
        $s->close();
    }
    $conn->close();

    header("Location: main.php?message=".rawurlencode("Batteries unloaded successfully"));
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Batteries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
There was an error for some reason...
</body>
</html>
