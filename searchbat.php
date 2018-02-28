<?php
require "header.php";
require_once "helpers.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Browse Batteries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body onload="javascript:document.getElementById('nbtxt').focus();">

<!-- SEARCH FIELDS -->
<form action="searchbat.php" method="post">
<table>
<tr><td>Numbers (separated by commas, and ranges with dashes)</td><td>
<?php
echo "<input type=\"text\" name=\"number\" id=\"nbtxt\"";
if (isset($_POST["number"]))
    echo " value=\"".$_POST["number"]."\"";
echo ">";
?>
</td></tr>

<tr><td>Include Retired Batteries?</td><td>
<?php
$include_retired = False;
if (isset($_POST["include_retired"]) && ($_POST["include_retired"] == "Yes"))
    $include_retired = True;

echo "<input type=\"checkbox\" name=\"include_retired\" value=\"Yes\"".($include_retired ? " checked" : "").">";
?>
</td></tr>

<tr><td colspan=2>
<?php
require_once "connect.php";
$conn = connect();
$s = $conn->prepare("select batmake.id as id, battype.id as typeid, battype.type as battype, whratio from batmake, battype where battype.id=batmake.type_id order by type_id, nominal") or die ($conn->error);
$s->execute();
$rs = $s->get_result();
$lastbattype = "";
while ($assoc = $rs->fetch_assoc())
{
    $makeid = $assoc["id"];
    $battype = $assoc["battype"];
    $typeid = $assoc["typeid"];
    if ($battype != $lastbattype)
    {
        if ($lastbattype != "")
            echo "<br>";
        echo "<input type=\"checkbox\" name=\"type$typeid\" value=\"Yes\"";
        if (isset($_POST["type$typeid"]) && ($_POST["type$typeid"] == "Yes"))
            echo " checked";
        echo ">".$battype." ";
        $lastbattype = $battype;
    }
    echo "<input type=\"checkbox\" name=\"make$makeid\" value=\"Yes\"";
    if (isset($_POST["make$makeid"]) && ($_POST["make$makeid"] == "Yes"))
        echo " checked";
    echo "><img src=\"showimg.php?makeid=$makeid\" width=\"100\" height=\"".(100/$assoc["whratio"])."\">\n";
}
$rs->close();
$s->close();
?>
</td></tr>
</table>
<input type="submit">
</form>

<!-- SEARCH RESULTS -->

<table border=1>
<tr><td><b>Number</b></td><td><b>Available</b></td><td><b>Type</b></td><td><b>Make</b></td><td><b>Acquired</b></td><td><b>Measures</b></td><td><b>Last Operations</b></td></tr>
<?php
$filter = "";
$types = "";
$params = array();

/**
 * GET THE BATTERY TYPES AND MAKES TO SORT CORRECTLY DEPENDING ON THE PREVIOUS SEARCH IF ANY
 */
// THE BATTERY NUMBERS
if (isset($_POST["number"]) and (strlen($_POST["number"]) > 0))
{
    $batt_nums = getBatteryNumbers($_POST["number"]);
    if (count($batt_nums) == 1)
    {
        $filter = " and num=?";
        $types .= "i";
        $number = $_POST["number"];
        $params[] = &$number;
    }
    else
    {
        $filter .= " and num in (";
        $first = true;
        $nbbatt = count($batt_nums);
        for ($i=0; $i < $nbbatt; $i++)
        { 
            if ($first)
                $first = false;
            else
                $filter .= ",";
            $filter .= $batt_nums[$i];
        }
        $filter .= ")";
    }
}

// FILTER BATTERY TYPES
$s = $conn->prepare("select id, type from battype") or die ($conn->error);
$s->execute();
$rs = $s->get_result();
$typefilter = "";
$selectedTypes = array();
while ($assoc = $rs->fetch_assoc())
{
    if (isset($_POST["type".$assoc["id"]]) && ($_POST["type".$assoc["id"]] == "Yes"))
    {
        if (strlen($typefilter) > 0)
            $typefilter .= ",";
        $typefilter .= "?";
        $selectedTypes[] = intval($assoc["id"]);
        $params[] = &$selectedTypes[count($selectedTypes) - 1];
        $types .= "i";
    }
}
if (strlen($typefilter) > 0)
    $filter .= " and battype.id in ($typefilter)";
$rs->close();
$s->close();

// FILTER BATTERY MAKES
$s = $conn->prepare("select id from batmake") or die ($conn->error);
$s->execute();
$rs = $s->get_result();
$makefilter = "";
$selectedMakes = array();
while ($assoc = $rs->fetch_assoc())
{
    if (isset($_POST["make".$assoc["id"]]) && ($_POST["make".$assoc["id"]] == "Yes"))
    {
        if (strlen($makefilter) > 0)
            $makefilter .= ",";
        $makefilter .= "?";
        $selectedMakes[] = intval($assoc["id"]);
        $params[] = &$selectedMakes[count($selectedMakes) - 1];
        $types .= "i";
    }
}
if (strlen($makefilter) > 0)
    $filter .= " and batmake.id in ($makefilter)";
$rs->close();
$s->close();

if (!$include_retired)
    $filter .= " and retired is null";

// PERFORM THE FILTERED QUERY
// Prepare the query to get measures on each battery
// Measuring events
$sMeasure = $conn->prepare("select mah, mydate, name from evt, device where evt_type=1 and device_id=id and batt_id=? order by mydate desc limit 1") or die ($conn->error);
// Charging events
$sCharged = $conn->prepare("select mydate from evt where evt_type=0 and batt_id=? order by mydate desc limit 1") or die ($conn->error);
// Moves to/from devices
$sOperation = $conn->prepare("select name, mydate, evt_type from evt, device where evt_type in (2, 3, 4) and batt_id=? and device.id=device_id order by mydate desc limit 1") or die ($conn->error);
$s = $conn->prepare("select battery.id as battid, num, make_id, type_id, battype.type as batterytype, whratio, acquired, nominal from battery, batmake, battype where battype.id=batmake.type_id and battery.make_id=batmake.id$filter order by type_id, num") or die ($conn->error);
if (count($params) > 0)
    call_user_func_array(array($s, "bind_param"), array_merge(array($types), $params));
$s->execute();
$rs = $s->get_result();
while ($assoc = $rs->fetch_assoc())
{
    $battid = $assoc["battid"];
    $number = $assoc["num"];
    $makeid = $assoc["make_id"];
    $typeid = $assoc["type_id"];
    $batterytype = $assoc["batterytype"];
    $whratio = $assoc["whratio"];
    $acquired = $assoc["acquired"];
    $nominal = $assoc["nominal"];

    // GET THE MEASURES FOR EACH BATTERY
    $sMeasure->bind_param("i", $battid) or die($conn->error);
    $sMeasure->execute();
    $rsMeasure = $sMeasure->get_result();
    $measures = "";
    while ($assoc = $rsMeasure->fetch_assoc())
    {
        if (strlen($measures) > 0)
            $measures .= "<br>";
        $measures .= "<img src=\"showfillimg.php?total=$nominal&value=".strval($assoc["mah"])."\"> ".$assoc["mydate"]." (".$assoc["name"].")";
    }
    $rsMeasure->close();
    if (strlen($measures) == 0)
        $measures = "No measure yet!";

    // GET THE OPERATIONS FOR EACH BATTERY
    $available = True;
    $sOperation->bind_param("i", $battid) or die($conn->error);
    $sOperation->execute();
    $rsOperation = $sOperation->get_result();
    $operation = "No known operations.";
    if ($assoc = $rsOperation->fetch_assoc())
    {
        $operation = ($assoc["evt_type"] == 2 ? "Loaded into " : "Removed from ")." ".$assoc["name"]." on ".$assoc["mydate"].($assoc["evt_type"] == 4 ? " because it was empty" : "");
        if ($assoc["evt_type"] == 2)
            $available = False;
    }
    $rsOperation->close();

    // GET THE LAST DATE CHARGED
    $sCharged->bind_param("i", $battid) or die($conn->error);
    $sCharged->execute();
    $rsCharged = $sCharged->get_result();
    $lastcharged = "Last charge date unknown";
    if ($assoc = $rsCharged->fetch_assoc())
        $lastcharged = "Last charged on ".$assoc["mydate"];
    $rsCharged->close();

    echo "<tr><td><a href=\"viewbatt.php?id=$battid\">$number</a></td>";
    echo "<td><img src=\"".($available ? "" : "not_")."available.png\" width=32 height=32></td>";
    echo "<td>$batterytype</td>";
    echo "<td><img src=\"showimg.php?makeid=$makeid\" width=\"100\" height=\"".(100/$assoc["whratio"])."\"></td>";
    echo "<td>$acquired</td><td>$measures</td><td>$operation<br>$lastcharged</td></tr>\n";
}
$rs->close();
$s->close();
$sOperation->close();
$sCharged->close();
$sMeasure->close();
$conn->close();
?>
</table>
<p>
<a href="main.php">Back to Menu</a>
</body>
</html>
