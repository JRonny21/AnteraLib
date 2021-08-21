<?php 

	$host = 'sql203.mipropia.com';
	$user = 'mipc_27770320';
	$pass = 'abcd1234';
	$db = 'mipc_27770320_mvgraphics_db';

	$conn = @mysqli_connect($host,$user,$pass,$db);

	if(!$conn){
		echo "Error en la conexion de la base de datos " . $db;
	}

 ?>