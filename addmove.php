<?php
require "header.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add a Move on Batteries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
<form action="doaddmove.php" method="post">
<?php
if (isset($_GET["message"]))
    echo htmlentities(rawurldecode($_GET["message"]))."<br>";

require_once "connect.php";
$conn = connect();

/**
 * MAKE SURE PARAMETERS ARE OK
 */

/**
 * DEVICE CHOSEN - LOAD OR UNLOAD BATTERIES
 */
if (isset($_GET["devid"]))
{
    // A device has been chosen, check whether we're loading or unloading
    $devid = $_GET["devid"];
    if (!is_numeric($devid))
        die("Are you trying to cheat? Devid is not a number here.");
    echo "<input type=\"hidden\" name=\"devid\" value=\"$devid\"><br>";

    /**
     * THEN GET THE LATEST MOVE FOUND FOR THIS DEVICE - SEE IF WE ARE LOADING OR UNLOADING
     */
    $s = $conn->prepare("select mydate, evt_type from evt where device_id=? and evt_type in (2, 3, 4) order by mydate desc limit 1") or die ($conn->error);
    $s->bind_param("i", $devid) or die($conn->error);
    $s->execute();
    $rs = $s->get_result();
    $assoc = $rs->fetch_assoc();
    if ($assoc && ($assoc["evt_type"] == 2))
    {
        // There is an action, and it was a load. We have to unload now.
        $loaddate = $assoc["mydate"];
        $load = 0;
    }
    else
        // Either there was no action or the last was not a load: we have to load now
        $load = 1;
    $rs->close();
    $s->close();
    echo "<input type=\"hidden\" name=\"load\" value=\"$load\"><br>";

    if ($load == 1)
    /**
     * LOADING SOME BATTERIES INTO A DEVICE
     */
    {
        /**
         * CHECK THE NUMBER OF BATTERIES AND OFFER TO INPUT THEM
         */
        $s = $conn->prepare("select battype.type as typename, nb_batt, name, shortname from device, battype where battype.id=device.type_id and device.id=?") or die ($conn->error);
        $s->bind_param("i", $devid) or die($conn->error);
        $s->execute();
        $rs = $s->get_result();
        while ($assoc = $rs->fetch_assoc())
        {
            $typeid = $assoc["type_id"];
            $typename = $assoc["typename"];
            $nbbatt = $assoc["nb_batt"];
            echo "Loading batteries into device \"".$assoc["name"]."\" (".$assoc["shortname"].") which has $nbbatt <b>$typename</b> batteries, fill in their numbers :<br>";
?>
<table>
<?php
            for ($i = 0 ; $i < $nbbatt ; $i++)
                echo "<tr><td>Battery #$i</td><td><input type=\"text\" name=\"batt$i\"></td></tr>";
        }
        $rs->close();
        $s->close();
    }
    else
    /**
     * UNLOADING BATTERIES FROM A DEVICE - GET THE EXISTING LOADED ONES
     */
    {
        /**
         * FIRST GET THE DEVICE INFO
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
         * FINALLY GRAB THE BATTERY NUMBERS
         */
        $s = $conn->prepare("select num from battery, evt where mydate=? and batt_id=id and device_id=? and evt_type=2") or die ($conn->error);
        $s->bind_param("si", $loaddate, $devid) or die($conn->error);
        $s->execute();
        $rs = $s->get_result();
        $batnumbers = "";
        $nbbattfound = 0;
        while ($assoc = $rs->fetch_assoc())
        {
            if ($batnumbers != "")
                $batnumbers .= ", ";
            $batnumbers .= $assoc["num"];
            $nbbattfound++;
        }
        $rs->close();
        $s->close();
        if ($nbbattexpected != $nbbattfound)
            echo "<font color=\"#FF0000\">WARNING: FOUND $nbbattfound LOADED BATTERIES BUT $nbbattexpected WERE EXPECTEDd!</font><br>";

        echo "Unloading batteries <b>$batnumbers</b> from device \"$devicename\" ($deviceshortname), is that correct? If so, complete the form and submit.<br>";
?>
<table>
<tr><td>Were the batteries empty?</td><td><input type="checkbox" name="empty" value="Yes" CHECKED></td></tr>
<tr><td>Recharge in...</td><td>
<select name="charging_device"><option value=""></option>
<?php
require_once "helpers.php";
showChargingDeviceOptions($conn, 0);
?>
</select>
</td></tr>
<?php
    }
/**
 * GENERIC LOADING/UNLOADING PARAMETERS
 */
?>
<tr><td>Date Moved</td><td><?php echo "<input type=\"text\" name=\"mydate\" value=\"".date("Y-m-d H:i:s")."\">"; ?></td></tr>
</table>
<input type="submit">
</form>
<?php
}
else
/**
 * NO DEVICE CHOSEN YET - OFFER THE CHOICE FROM THE IMAGES
 */
{
?>
Choose a device.<br>
<?php
    // No device chosen, offer to choose one first, with either the combo, either by clicking on the device's image
    $sMove = $conn->prepare("select evt_type from evt where device_id=? and evt_type in (2, 3, 4) order by mydate desc limit 1") or die ($conn->error);
    $sDevice = $conn->prepare("select id, shortname from device where dev_type < 2") or die ($conn->error);
    $sDevice->execute();
    $rsDevice = $sDevice->get_result();
    while ($assocDevice = $rsDevice->fetch_assoc())
    {
        $deviceid = $assocDevice["id"];
        /**
         * THEN GET THE LATEST MOVE FOUND FOR THIS DEVICE - SEE IF WE ARE LOADING OR UNLOADING
         */
        $sMove->bind_param("i", $deviceid) or die ($conn->error);
        $sMove->execute();
        $rsMove = $sMove->get_result();
        $assocMove = $rsMove->fetch_assoc();
        $loaded = False;
        if ($assocMove && ($assocMove["evt_type"] == 2))
            $loaded = True;
        $rsMove->close();
        echo "<a href=\"addmove.php?devid=$deviceid\"><img src=\"showimg.php?devid=$deviceid\" border=\"3\" style=\"border-color:#".($loaded ? "FF0000" : "00AA00")."\"></a>\n";
    }
    $rsDevice->close();
    $sDevice->close();
    $sMove->close();
    $conn->close();
}
?>
<p>
<a href="main.php">Back to Menu</a>
</body>
</html>
