<?php
function connect()
{
	$ini_array = parse_ini_file("connect.ini");
	$connect = new mysqli($ini_array["host"], $ini_array["user"], $ini_array["pass"], $ini_array["database"]) or die(mysqli_connect_error());
	return $connect;
}
?>
