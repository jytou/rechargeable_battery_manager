<?php
session_start();
if (!isset($_SESSION['USERID']))
{
	if (strpos($_SERVER['REMOTE_ADDR'], "192.168.") == 0)
		$userid = 1;
	else
		die("Wrong call, please <a href='login.php'>reconnect</a>");
}
else
	$userid = $_SESSION['USERID'];
?>
