<?php
session_start();
require_once "connect.php";
$conn = connect();
$user = $_POST["name"];
$hashedpass = hash('sha512', $_POST["pwd"]);
$s = $conn->prepare("select id, name, pass from user where name=?") or die ($conn->error);
$s->bind_param("s", $user);
$s->execute();
$rs = $s->get_result();
$assoc = $rs->fetch_assoc();
$storedpass = $assoc['pass'];
$rs->close();
$s->close();
$conn->close();
if (($assoc != NULL) && (($storedpass == $hashedpass) || ($storedpass == $_POST["pwd"])))
{
    $_SESSION["USERID"] = $assoc["id"];
    header("Location: main.php");
    die();
}
else
{
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Logging in...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--link rel="stylesheet" type="text/css" media="screen" href="main.css" /-->
</head>
<body>
<?php
    echo "Could not log in. Wrong user or password. This will be logged.<br><a href='login.php'>Try again</a>";
    error_log("Someone tried to log in as user ".$_POST["name"]." with password ".$_POST["pwd"]." from IP address ".$_SERVER['REMOTE_ADDR']);
}
?>
</body>
</html>