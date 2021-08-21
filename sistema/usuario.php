<?php 

session_start();
	if (empty($_SESSION['active'])) {

		header('location: ../');

	} else {
		include "conexion.php";
		include "includes/accesos.php"; 

		// Acceso de Perfiles de Usuario
        function Puede($componente,$privilegio) {
            global $respuesta;
            $estado = false;
            
            for($i=0; $i <= count($respuesta); $i++) {
                if ($respuesta[$i]['a'] == $componente && $respuesta[$i][$privilegio] > 0){
                    $estado = true;
                }
            }

            return $estado;
        }

		$alert	= '';
		$estatusID = 0;
		$titulo = '';
		$boton = '';
        $getUserID = '';

		$idusuario = '';
		$nombre = '';
		$correo = '';
        $usuario = '';
		$clave = '';
        $rol = '';
		$actual_usuario = $_SESSION['idUser'];

        $query_rol = mysqli_query($conn,"SELECT * FROM rol");

		if (empty($_GET) && Puede('usuarios','c')) {
			$titulo = 'Nuevo Usuario';
            $titulo_header = '<span class="material-icons title-icon">add_moderator</span>Nuevo Usuario';
			$boton = '<i class="material-icons btn-icon">save</i>Guardar';

			if (!empty($_POST)) {
				if (empty($_POST['nombre']) || empty($_POST['usuario']) || empty($_POST['clave']) || empty($_POST['rol'])) {
					$alert = '<div class="alert alert-danger" role="alert">Todos los campos marcados son obligatorios</div>';
				} else {

					$nombre = $_POST['nombre'];
                    $correo = $_POST['correo'];
                    $usuario = $_POST['usuario'];
                    $clave = md5($_POST['clave']);
                    $rol = $_POST['rol'];

                    $query = mysqli_query($conn,"SELECT * FROM usuario WHERE usuario = '$usuario'");
                    $result = mysqli_fetch_array($query);

                    if ($result > 0) {
                        $alert = '<div class="alert alert-warning" role="alert">El nombre de usuario ya existe en el sistema</div>';
                    } else {
                        $query_last = mysqli_query($conn, "SELECT idusuario FROM `usuario` ORDER BY idusuario DESC LIMIT 1");

                        $idlast = '';

                        while ($getid = mysqli_fetch_array($query_last)) {
                            $idlast = $getid['idusuario'] + 1;
                        }

                        mysqli_query($conn,"ALTER TABLE usuario AUTO_INCREMENT = '$idlast'");

                        $query_insert = mysqli_query($conn, "INSERT INTO usuario(idusuario,nombre,correo,usuario,clave,rol)
                                                                VALUES('$idlast','$nombre','$correo','$usuario','$clave','$rol')");

                        if ($query_insert) {
                            $alert = '<div class="alert alert-success" role="alert">Usuario '.$usuario.' creado correctamente</div>';
                            header("Refresh:1; URL:lista_usuario.php");
                        } else {
                            $alert = '<div class="alert alert-warning" role="alert">Error al crear el usuario '.$usuario.'</div>';
                        }
                    }
                }
			}

		} else if(!empty($_GET)) {
			if (!Puede('usuarios','c') && $actual_usuario != $_GET['actualizarmiID']) {
				header ('location: lista_usuario.php');
			}
            
			//Cargar Datos
			$getUserID = (empty($_GET['modificarID'])) ? ((empty($_GET['eliminarID'])) ? $_GET['actualizarmiID'] : $_GET['eliminarID'] ) : $_GET['modificarID'];

			$query = mysqli_query($conn, "SELECT u.idusuario, u.nombre, u.correo, u.usuario, u.estatus, (u.rol) as idrol, (r.rol) as rol
										FROM usuario u INNER JOIN rol r ON u.rol = r.idrol WHERE idusuario = '$getUserID'");

			$result = mysqli_num_rows($query);
			if ($result == 0) {
				header ('location: lista_usuario.php');

			} else {
				while ($data = mysqli_fetch_array($query)) {
					$estatusID = ($data['estatus'] == 'activo') ? 'desactivar' : 'activar';
					$idusuario = $data['idusuario'];
                    $nombre = $data['nombre'];
                    $correo = $data['correo'];
                    $usuario = $data['usuario'];
                    $idrol = $data['idrol'];
                    $rol = $data['rol'];
				}
			}
		}

        //Modificar datos de usuario actual
        if(!empty($_GET['actualizarmiID']) && $actual_usuario == $getUserID) {
            $titulo = "Modificar mi cuenta";
            $titulo_header = '<span class="material-icons title-icon">edit</span>Modificar mis datos <br><small>'. $usuario.'</small>';
			$boton = '<i class="material-icons btn-icon">update</i>Actualizar';
            
            if (!empty($_POST)) {
				if (empty($_POST['nombre']) || empty($_POST['usuario'])) {
					$alert = '<div class="alert alert-danger" role="alert">Todos los campos marcados son obligatorios</div>';
				} else {
					$nombre = $_POST['nombre'];
                    $correo = $_POST['correo'];
                    $usuario = $_POST['usuario'];
                    $clave = md5($_POST['clave']);

					$query = mysqli_query($conn,"SELECT * FROM usuario 
													WHERE idusuario = '$actual_usuario'");

					$result = mysqli_fetch_array($query);

					if ($result = 0) {
						$alert = '<div class="alert alert-warning" role="alert">El usuario a modificar no existe</div>';
                        header('Refresh: 1;URL=main.php'); 
					} else {

						if (empty($_POST['clave'])) {
                            $query_update = mysqli_query($conn, "UPDATE usuario
                                                                    SET nombre = '$nombre', correo = '$correo', usuario = '$usuario'
                                                                    WHERE idusuario = '$actual_usuario'");
                        } else {
                            $query_update = mysqli_query($conn, "UPDATE usuario
                                                                    SET nombre = '$nombre', correo = '$correo', usuario = '$usuario', clave = '$clave' 
                                                                    WHERE idusuario = '$actual_usuario'");
                        }

						if ($query_update) {
							$alert = '<div class="alert alert-success" role="alert">Tus datos han sido actualizado correctamente</div>';
                            header('Refresh: 1;URL=index.php'); 
						} else {
							$alert = '<div class="alert alert-warning" role="alert">Error al actualizar tus datos</div>';
                            header('Refresh: 1;URL=index.php'); 
						}
					}
				}
			}
		} else if(!empty($_GET['actualizarmiID']) && $actual_usuario != $getUserID && Puede('usuarios','e')){
            header('location: index.php');
		}
        
		//Modificar datos de otro usuario
		if(!empty($_GET['modificarID']) && Puede('usuarios','e')) {
            $titulo = "Modificar usuario: ".$usuario;
			$titulo_header = '<span class="material-icons title-icon">edit</span>Modificar usuario <br><small>'. $usuario.'</small>';
			$boton = '<i class="material-icons btn-icon">update</i>Actualizar';

			if (!empty($_POST)) {
				if (empty($_POST['nombre']) || empty($_POST['usuario']) || empty($_POST['rol'])) {
					$alert = '<div class="alert alert-danger" role="alert">Todos los campos marcados son obligatorios</div>';
				} else {
					$nombre = $_POST['nombre'];
                    $correo = $_POST['correo'];
                    $usuario = $_POST['usuario'];
                    $clave = md5($_POST['clave']);
                    $rol = $_POST['rol'];

					$query = mysqli_query($conn,"SELECT * FROM usuario 
													WHERE idusuario = '$getUserID'");
                    
					$result = mysqli_fetch_array($query);

					if ($result = 0) {
						$alert = '<div class="alert alert-warning" role="alert">El usuario a modificar no existe</div>';
					} else {

						if (empty($_POST['clave'])) {
                            $query_update = mysqli_query($conn, "UPDATE usuario
                                                                    SET nombre = '$nombre', correo = '$correo', rol = '$rol', usuario = '$usuario'
                                                                    WHERE idusuario = '$getUserID'");
                        } else {
                            $query_update = mysqli_query($conn, "UPDATE usuario
                                                                    SET nombre = '$nombre', correo = '$correo', rol = '$rol', clave = '$clave', usuario = '$usuario' 
                                                                    WHERE idusuario = '$getUserID'");
                        }

						if ($query_update) {
							$alert = '<div class="alert alert-success" role="alert">Usuario '.$usuario.' actualizado correctamente</div>';
                            header('Refresh: 1;URL=lista_usuario.php'); 
						} else {
							$alert = '<div class="alert alert-warning" role="alert">Error al actualizar el usuario '.$usuario.'</div>';
						}
					}
				}
			}
		} else if(!Puede('usuarios','e')  && $actual_usuario != $_GET['actualizarmiID']) {
			header('location: lista_usuario.php');
		}
        
		//Eliminar datos del usuario
		if(!empty($_GET['eliminarID']) && Puede('usuarios','d')) {
        
        $titulo = (($estatusID != 'desactivar') ? 'Activar' : 'Desactivar').' usuario';
        $boton = ($estatusID != 'desactivar') ? '<i class="material-icons btn-icon">visibility</i>Activar usuario'
                                         : '<i class="material-icons btn-icon">visibility_off</i>Desactivar usuario';
            //Comprobar que no sea un admin
            $si_admin = mysqli_query($conn,"SELECT * FROM usuario WHERE rol = 1");
		
            $datos = array();
            while ($a = mysqli_fetch_array($si_admin)) {
                $datos[] = $a['idusuario'];
            }
            if (!in_array($getUserID, $datos)) {
                
                if (!empty($_POST)) {
                    $getid =  $_POST['idusuario'];
                    if (in_array($getid, $datos)) {
                        header ('location: lista_usuario.php');

                    } else {

                        $idusuario =  $getid;
                        $query = mysqli_query($conn, "SELECT * FROM usuario WHERE idusuario = '$idusuario'");
                        $result = mysqli_num_rows($query);
                        
                        if ($result == 0) {
                            header ('location: lista_usuario.php');

                        } else {
                            $estatus = ($estatusID == 'desactivar') ? 'inactivo' : 'activo';

                            $query = mysqli_query($conn, "UPDATE usuario
                                                        SET estatus = '$estatus'
                                                        WHERE idusuario = '$idusuario'");
                            if ($query) {
                                header ('location: lista_usuario.php');
                            } else {
                                echo '<div class="alert alert-warning" role="alert">Error al desabilitar el usuario '.$nombre.'</div>';
                            }                            
                        }
                    }
                }
            } else {
			    header ('location: lista_usuario.php');
            }  
		} else if(!Puede('usuarios','d') && $actual_usuario != $_GET['actualizarmiID']) {
			header('location: lista_usuario.php');
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include 'includes/script.php' ?>
	<title><?php echo $titulo; ?></title>
</head>
<body>
	<?php include 'includes/header.php' ?>
	<?php 
		if (!empty($_GET['eliminarID'])) {
	?>
	<section class="container h-200 light-static">
		<div class="panel panel-default text-center">
			<div class="panel-heading">
                <h2 class="display-1 light-static">¿Estás seguro de <?php echo $estatusID ?> el siguiente usuario?</h2>
			</div>
            <div class="panel-body">
                <h2 class="panel-title">Usuario: <span><?php echo $usuario ?></span></h2>
                <div class="panel-body">
                    <h5>Nombre: <span><?php echo $nombre ?></span></h5>
                    <h5>Rol de sistema: <span><?php echo $rol ?></span></h5>
                </div>
            </div>
            <div class="panel-footer">
                <form method="post" action="">
                    <input type="hidden" name="idusuario" value="<?php echo $idusuario; ?>">
                    <a href="lista_usuario.php" class="btn light text-center"><i class="material-icons btn-icon">cancel</i>Cancelar</a>
                    <button type="submit" class="btn dark text-center"><?php echo $boton; ?></button>
                </form>
            </div>
		</div>
	</section>


	<?php
		} else {
	 ?>

	<section class="container mx-5">

    	<div class="header light-static">
			<h1><?php echo $titulo_header; ?></h1>
			<hr>
			<div class="alert">
				<?php echo isset($alert) ? $alert : ''; ?>
			</div>
        </div>
        <form action="" method="POST" class="light-static">
            <div class="form-row">
                <div class="form-group col-sm-6">
                    <label for="nombre">* Nombre Completo</label>
                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre completo" value="<?php echo $nombre; ?>" required>
                </div>
                <div class="form-group col-sm-6">
                    <label for="usuario">* Usuario</label>
                    <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Nombre de usuario" value="<?php echo $usuario; ?>" required>
                </div>
            </div>
            <div class="form-row">
                <?php if(Puede('usuarios','e')) { ?>
                    <div class="form-group col-sm-6">
                        <label for="rol">* Rol del usuario</label>
                        <select type="select" class="form-control light-select" name="rol" id="rol" required>
                        <?php while ($rol = mysqli_fetch_array($query_rol)) { ?>
                            <?php if($idrol == $rol['idrol']) { ?>
                                <option selected value="<?php echo $rol["idrol"]; ?>"><?php echo $rol["rol"]; ?></option>
                            <?php } else {?>
                                <option value="<?php echo $rol["idrol"]; ?>"><?php echo $rol["rol"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                        </select>
                    </div>
                <?php } ?>
                <div class="form-group col-sm-6">
                    <label for="clave">Contraseña</label>
                    <input type="password" class="form-control" name="clave" id="clave" placeholder="Clave" value="<?php echo $clave; ?>">
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label for="correo">Correo Electrónico</label>
                <input type="email" class="form-control" name="correo" id="correo" placeholder="email" value="<?php echo $correo; ?>">
            </div>
            <div class="form-row"><div class="form-row">
                <div class="form-group col-xs-6 col-sm-4 px-0">
                    <a type="button" href="lista_usuario.php" class="btn light text-center vcenter"><i class="material-icons btn-icon">cancel</i></span> Cancelar</a>
                </div>
                <div class="form-group col-xs-6 col-sm-4 px-0">
                    <button type="submit" class="btn dark"><?php echo $boton; ?></button>
                </div>
            </div>
        </form>
    </section>
	<?php
		}
	?>
	<?php include 'includes/footer.php' ?>
</body>
</html>