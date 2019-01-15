<?php

$host         = "excior.com.br";
$username     = "excior30_odbc";
$password     = "yo1px6";
$dbname       = "excior30_inventario_dev";

try {

    $dbconn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password,
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

} catch (PDOException $e) {

    print "Error!: " . $e->getMessage() . "<br/>";
    die();
	
}
?>