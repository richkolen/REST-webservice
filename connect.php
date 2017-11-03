<?php

$db_name="0855044";				//databasenaam
$db_user="0855044";			    //username van database
$db_server="localhost";			//servernaam
$db_password="a5e26bc";	        //server password

$mysqli = new mysqli($db_server, $db_user, $db_password, $db_name);		//connectie string



if ($mysqli->connect_errno) {
    printf("DB connect failed: %s\n", $mysqli->connect_error);	//print een error wanneer de connectie niet kan worden gemaakt.
    exit;
}
