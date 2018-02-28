<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login to Batteries Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--link rel="stylesheet" type="text/css" media="screen" href="main.css" /-->
</head>
<body>
    <form action="dologin.php" method="post">
    <table>
    <tr><td>Login</td><td><input type="text" name="name"></td></tr>
    <tr><td>Password</td><td><input type="password" name="pwd"></td></tr>
    </table>
    <input type="submit">
    </form>
</body>
</html>