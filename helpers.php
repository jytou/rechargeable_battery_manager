<?php
function showBatteryTypeOptions($conn)
{
    $s = $conn->prepare("select id, type from battype") or die ($conn->error);
    $s->execute();
    $rs = $s->get_result();
    while ($assoc = $rs->fetch_assoc())
    {
        $typeid = $assoc["id"];
        $typename = $assoc["type"];
        echo "<option value=\"$typeid\">$typename</option>\n";
    }
    $rs->close();
    $s->close();
}

function showChargingDeviceOptions($conn, $showMeasuringOnly)
{
    $s = $conn->prepare("select id, shortname from device where dev_type".($showMeasuringOnly ? ">2" : ">1")) or die ($conn->error);
    $s->execute();
    $rs = $s->get_result();
    while ($assoc = $rs->fetch_assoc())
    {
        $mdeviceid = $assoc["id"];
        $name = $assoc["shortname"];
        echo "<option value=\"$mdeviceid\">$name</option>\n";
    }
    $rs->close();
    $s->close();
}

function getBatteryNumbers($expr)
{
    $batt_nums = array();
    $series = explode(",", $expr);
    foreach ($series as $key => $serie)
    {
        if (strpos($serie, "-"))
        // It's a range
        {
            $start = intval(substr($serie, 0, strpos($serie, "-")));
            $end = intval(substr($serie, strpos($serie, "-") + 1));
            for ($i = $start; $i <= $end ; $i++)
                $batt_nums[] = $i;
        }
        else
        // It's an individual battery number
            $batt_nums[] = intval($serie);
    }
    return $batt_nums;
}

/**
 * Format an interval to show all existing components.
 * If the interval doesn't have a time component (years, months, etc)
 * That component won't be displayed.
 *
 * @param DateInterval $interval The interval
 *
 * @return string Formatted interval string.
 */
function format_interval(DateInterval $interval)
{
    $result = "";
    if ($interval->y) { $result .= $interval->format("%y years "); }
    if ($interval->m) { $result .= $interval->format("%m months "); }
    if ($interval->d) { $result .= $interval->format("%d days "); }
    if ($interval->h) { $result .= $interval->format("%h hours "); }
    if ($interval->i) { $result .= $interval->format("%i minutes "); }
    if ($interval->s) { $result .= $interval->format("%s seconds "); }

    return $result;
}
?>
