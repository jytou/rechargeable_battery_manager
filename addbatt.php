<?php
require "header.php";
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
<form action="doaddbatt.php" method="post">
<table>
<tr><td>Start Number</td><td><input type="text" name="start"></td></tr>
<tr><td>End Number</td><td><input type="text" name="end"></td></tr>
<tr><td>Date Acquired</td><td><?php echo "<input type=\"text\" name=\"acquired\" value=\"".date("Y-m-d H:i:s")."\">"; ?></td></tr>
<tr><td>Make</td><td>
<?php
require_once "connect.php";
$conn = connect();
// First gather the max for every type so the user knows which number to start with
$s = $conn->prepare("select max(num) maxnum, batmake.type_id as type_id from batmake, battery where user_id=? and battery.make_id=batmake.id group by type_id") or die ($conn->error);
$s->bind_param("i", $userid) or die($conn->error);
$s->execute();
$rs = $s->get_result();
$maxForTypes = array();
while ($assoc = $rs->fetch_assoc())
    $maxForTypes[$assoc["type_id"]] = $assoc["maxnum"];
$rs->close();
$s->close();

// Now for each make, show it, by type
$s = $conn->prepare("select batmake.id as id, type_id, battype.type as battype, whratio from batmake, battype where battype.id=batmake.type_id order by type_id, nominal") or die ($conn->error);
$s->execute();
$rs = $s->get_result();
$lastbattype = "";
while ($assoc = $rs->fetch_assoc())
{
    $makeid = $assoc["id"];
    $battype = $assoc["battype"];
    if ($battype != $lastbattype)
    {
        if ($lastbattype != "")
            echo "<br>";
        echo $battype." (new=".strval($maxForTypes[$assoc["type_id"]] + 1).") ";
        $lastbattype = $battype;
    }
    echo "<input type=\"radio\" name=\"make\" value=\"$makeid\"><img src=\"showimg.php?makeid=$makeid\" width=\"100\" height=\"".(100/$assoc["whratio"])."\">\n";
}
$rs->close();
$s->close();
$conn->close();
?>
</td></tr>
</table>
<input type="submit">
</form>
<p>
<a href="main.php">Back to Menu</a>
</body>
</html>