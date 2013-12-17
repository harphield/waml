<?php
require 'dbconfig.php';

try {
	$dbh = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_name, $db_user, $db_pass);
	$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
	print "Error!: " . $e->getMessage() . "<br/>";
    die();
}