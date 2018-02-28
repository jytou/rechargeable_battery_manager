<?php
require "header.php";
$start = intval($_POST["start"]);
$end = $_POST["end"];
if ($end == "")
    $end = $start;
else
    $end = intval($end);
$makeid = intval($_POST["make"]);
$date_acquired = $_POST["acquired"];
require_once "connect.php";
$conn = connect();
$s = $conn->prepare("insert into battery(num, make_id, user_id, acquired) values(?, ?, ?, ?)") or die($conn->error);
for ($i=$start; $i<=$end ; $i++)
{ 
    $s->bind_param("iiis", $i, $makeid, $userid, $date_acquired) or die($conn->error);
    $s->execute() or die($conn->error);
}
$s->close();
$conn->close();
header("Location: main.php?message=".rawurlencode("Battery added successfully"));
die();
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
