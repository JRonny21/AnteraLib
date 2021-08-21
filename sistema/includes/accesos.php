<?php 
	if (empty($_SESSION['active'])) {

		header('location: ../');

	} else {

		include "../conexion.php";

		$idrol = $_SESSION['idrol'];
		$respuesta = array();

		include "../conexion.php";
        include "conexion.php";

        $rol_query = mysqli_query($conn, "SELECT * FROM perfiles");

        $access = array();
        
        foreach($rol_query as $i => $rol){
            //$values = "'".strval($rol['ver'])."'";
            $access[$i]['a'] = $rol['acceso'];
            $access[$i]['v'] = (strpos(("'".strval($rol['ver'])), $idrol) == false) ? 0 : 1;
            $access[$i]['e'] = (strpos(("'".strval($rol['editar'])), $idrol) == false) ? 0 : 1;
            $access[$i]['c'] = (strpos(("'".strval($rol['crear'])), $idrol) == false) ? 0 : 1;
            $access[$i]['d'] = (strpos(("'".strval($rol['eliminar'])), $idrol) == false) ? 0 : 1;
        }
		$result = mysqli_num_rows($rol_query);

		if ($result > 0) {
			$respuesta = $access;
		} else {
			$respuesta[0]['a'] = "Usuario sin privilegios";
		}
        mysqli_close();
	}

 ?>