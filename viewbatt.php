<?php
require "header.php";
require_once "helpers.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>View Battery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<table>
<?php
$battid = $_GET["id"];
require_once "connect.php";
$conn = connect();
$s = $conn->prepare("select num, make_id, type_id, battype.type as batterytype, whratio, acquired, nominal from battery, batmake, battype where num=? and battype.id=batmake.type_id and battery.make_id=batmake.id") or die ($conn->error);
$s->bind_param("i", intval($battid)) or die($conn->error);
$s->execute();
$rs = $s->get_result();
if ($assoc = $rs->fetch_assoc())
{
    $batt_num = $assoc["num"];
    $batt_type = $assoc["batterytype"];
    $acquired = $assoc["acquired"];
    $make_id = $assoc["make_id"];
    $height = 100 / $assoc["whratio"];
    echo "<tr><td>Number</td><td>$batt_num</td></tr>\n";
    echo "<tr><td>Type</td><td>$batt_type</td></tr>\n";
    echo "<tr><td>Make</td><td><img src=\"showimg.php?makeid=$make_id\" width=\"100\" height=\"$height\"></td></tr>\n";
    echo "<tr><td>Acquired</td><td>$acquired</td></tr>\n";
    echo "</tr></table><br><table border=1><tr><td>Event</td><td>device</td><td>date</td><td>Interval</td><td>Measurement</td></tr>";
    $sEvents = $conn->prepare("select mydate, mah, device_id, evt_type from evt where batt_id=? order by mydate desc, evt_type") or die($conn->error);
    $sEvents->bind_param("i", $battid) or die($conn->error);
    $sEvents->execute();
    $rsEvents = $sEvents->get_result();
    $lastdate = null;
    while ($assocEvents = $rsEvents->fetch_assoc())
    {
        

        switch ($assocEvents["evt_type"])
        {
            case 0:
                $event = "Charged";
                break;
            case 1:
                $event = "Measured";
                break;
            case 2:
                $event = "Load";
                break;
            case 3:
            case 4:
                $event = "Unload";
                if ($assocEvents["evt_type"] == 4)
                    $event .= " (was empty)";
                break;
        }
        echo "<tr><td>$event</td><td><img src=\"showimg.php?devid=".$assocEvents["device_id"]."\" width=50 height=50></td><td>".$assocEvents["mydate"]."</td>";
        $curdate = new DateTime($assocEvents["mydate"]);
        if ($lastdate == null)
            $datediff = "";
        else
            $datediff = format_interval($curdate->diff($lastdate));
        $lastdate = $curdate;
        echo "<td>".$datediff."</td>";
        echo "<td align=\"right\">".($assocEvents["mah"] == null ? "" : $assocEvents["mah"]." mAh")."</td></tr>";
    }
    $rsEvents->close();
    $sEvents->close();
    echo "</table>\n";
}
$rs->close();
$s->close();
?>
</body>
</html>
