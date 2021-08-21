<?php 

session_start();
	if (empty($_SESSION['active'])) {

		header('location: ../');

	} else {
		include "conexion.php";
		include "includes/accesos.php"; 

        // Acceso de Perfiles de Usuario
        function Puede($privilegio,$componente) {
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
		$actID = 0;
		$titulo = '';
        $titulo_header = '';
		$boton = '';

		$codproveedor = '';
		$proveedor = '';
		$telefono = '';
		$contacto = '';
        $celular = '';
		$direccion = '';
		$id_usuario = $_SESSION['idUser'];


		if (empty($_GET) && Puede('c','proveedores')) {
            $titulo = 'Nuevo Proveedor';
			$titulo_header = '<span class="material-icons title-icon">add_business</span>Nuevo Proveedor';
			$boton = '<i class="material-icons btn-icon">save</i>Guardar';

			if (!empty($_POST)) {
				if (empty($_POST['proveedor']) || empty($_POST['telefono'])) {
					$alert = '<div class="alert alert-danger" role="alert">Nombre de proveedor y teléfono son obligatorios</div>';
				} else {
                    
					$proveedor = $_POST['proveedor'];
					$telefono = $_POST['telefono'];
					$contacto = $_POST['contacto'];
                    $celular = $_POST['celular'];
					$direccion = $_POST['direccion'];
	            
		            $insert = mysqli_query($conn, "INSERT INTO proveedor(proveedor,contacto,telefono,celular,direccion)
                                                            VALUES ('$proveedor','$contacto','$telefono','$celular','$direccion')");

                    if ($insert) {
                        $alert = '<div class="alert alert-success" role="alert">proveedor '.$proveedor.' guardado correctamente</div>';
                        header('Refresh: 2;URL=lista_proveedor.php'); 
                    } else {
                        $alert = '<div class="alert alert-danger" role="alert">Error al guardar el proveedor '.$proveedor.'</div>';
                    }
				}
			}

		} else if(!empty($_GET)) {
			if (!Puede('c','proveedores')) {
				header ('location: lista_proveedor.php');
			}
			//Cargar Datos
            $codproveedor = (empty($_GET['modificarID'])) ? 
                                ((empty($_GET['desactivarID'])) ? $_GET['eliminarID'] : $_GET['desactivarID']) : $_GET['modificarID'];

			$query = mysqli_query($conn, "SELECT * FROM proveedor WHERE codproveedor = '$codproveedor'");

			$result = mysqli_num_rows($query);
			if ($result == 0) {
				header ('location: lista_proveedor.php');

			} else {
				while ($data = mysqli_fetch_array($query)) {
					$actID = ($data['estatus'] == 'activo') ? 'desactivar' : 'activar';
					$codproveedor = $data['codproveedor'];
					$proveedor = $data['proveedor'];
					$contacto = $data['contacto'];
					$telefono = $data['telefono'];
                    $celular = $data['celular'];
					$direccion = $data['direccion'];
				}
			}
		}

		//Modificar datos del proveedor
        if(empty($_GET) || intval($_GET) > 0 || intval($_GET['desactivarID']) > 0 || intval($_GET['eliminarID']) > 0) { } else {
        header('location: lista_proveedor.php'); }

		if(!empty($_GET['modificarID']) && Puede('e','proveedores')) {
            $titulo = 'Modificar proveedor';
			$titulo_header = '<span class="material-icons title-icon">edit_location_alt</span>Modificar proveedor <br><small>'.$proveedor.'</small>';
			$boton = '<i class="material-icons btn-icon">update</i>Actualizar';

			if (!empty($_POST)) {
				if (empty($_POST['proveedor']) || empty($_POST['telefono'])) {
					$alert = '<div class="alert alert-danger" role="alert">Nombre de proveedor y teléfono son obligatorios</div>';
				} else {
					$codproveedor = $_GET['modificarID'];
					$proveedor = $_POST['proveedor'];
					$telefono = $_POST['telefono'];
					$contacto = $_POST['contacto'];
                    $celular = $_POST['celular'];
					$direccion = $_POST['direccion'];

					$query = mysqli_query($conn,"SELECT * FROM proveedor 
													WHERE codproveedor = '$codproveedor'");

					$result = mysqli_fetch_array($query);

					if ($result = 0) {
						$alert = '<p class="msg_error">El proveedor a modificar no existe</p>';
					} else {

						$query_update = mysqli_query($conn, "UPDATE proveedor
																	SET proveedor = '$proveedor', contacto = '$contacto',
                                                                        telefono = '$telefono',	direccion = '$direccion', celular = '$celular'
																	WHERE codproveedor = '$codproveedor'");

						if ($query_update) {
                            
							$alert = '<div class="alert alert-success" role="alert">Proveedor '.$proveedor.' actualizado correctamente</div>';
                            header('Refresh: 1;URL=lista_proveedor.php'); 
						} else {
							$alert = '<p class="msg_error">Error al actualizar el proveedor '.$proveedor.'</p>';
						}
					}
				}
			}


		} else if(!Puede('e','proveedores')) {
			header('location: lista_proveedor.php');
		}

		//Desactivar datos del proveedor
		if(!empty($_GET['desactivarID']) && $_GET['desactivarID'] > 0 && Puede('d','proveedores')) {
			$titulo = (($actID != 'desactivar') ? 'Activar' : 'Desactivar').' proveedor';
			$boton = ($actID != 'desactivar') ? '<i class="material-icons btn-icon">business</i>Activar proveedor'
                                            : '<i class="material-icons btn-icon">domain_disabled</i>Desactivar proveedor';

			if (!empty($_POST)) {
				$codproveedor =  $_POST['id'];
				$query = mysqli_query($conn, "SELECT * FROM proveedor WHERE codproveedor = '$codproveedor'");
				$result = mysqli_num_rows($query);
				
				if ($result == 0) {
					header ('location: lista_proveedor.php');

				} else {
					$estatus = ($actID == 'desactivar') ? 'inactivo' : 'activo';

					$query = mysqli_query($conn, "UPDATE proveedor
												SET estatus = '$estatus'
												WHERE codproveedor = '$codproveedor'");
					if ($query) {
						header ('location: lista_proveedor.php');
					} else {
						echo "<script type='text/javascript'> alert('Error al modificar proveedor'); </script>";
					}
				}	
				
			}

		} else if(!Puede('d','proveedores')) {
			header('location: lista_proveedor.php');
		}

        //Eliminar datos del proveedor
		if(!empty($_GET['eliminarID']) && $_GET['eliminarID'] > 0 && Puede('d','proveedores')) {
			$titulo = 'Eliminar proveedor';
            $actID = 'eliminar permanentemente';
			$boton = '<i class="material-icons btn-icon">delete</i>Eliminar proveedor';

			if (!empty($_POST)) {
				$codproveedor =  $_POST['id'];
				$query = mysqli_query($conn, "SELECT * FROM proveedor WHERE codproveedor = '$codproveedor'");
				$result = mysqli_num_rows($query);
				
				if ($result == 0) {
					header ('location: lista_proveedor.php');

				} else {

					$query = mysqli_query($conn, "DELETE FROM proveedor
												WHERE codproveedor = '$codproveedor'");
					if ($query) {
						header ('location: lista_proveedor.php');
					} else {
						echo "<script type='text/javascript'> alert('Error al eliminar proveedor'); </script>";
					}
				}	
				
			}

		} else if(!Puede('d','proveedores')) {
			header('location: lista_proveedor.php');
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
		if (!empty($_GET['desactivarID']) || !empty($_GET['eliminarID'])) {
	?>
	<section class="container h-200 light-static">
		<div class="panel panel-default text-center">
			<div class="panel-heading">
                <h2 class="display-1 light-static">¿Estás seguro de <?php echo $actID ?> el siguiente proveedor?</h2>
			</div>
            <div class="panel-body">
                <h2 class="panel-title">Nombre: <span><?php echo $proveedor ?></span></h2>
                <div class="panel-body">
                    <h5>Telefono: <span><?php echo $telefono ?></span></h5>
                    <h5>Contacto: <span><?php echo $contacto ?></span></h5>
                </div>
            </div>
            <div class="panel-footer">
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?php echo $codproveedor; ?>">
                    <a href="lista_proveedor.php" class="btn light text-center"><i class="material-icons btn-icon">cancel</i>Cancelar</a>
                    <button type="submit" class="btn dark text-center"><?php echo $boton; ?></button>
                </form>
            </div>
		</div>
	</section>


	<?php
		} else {
	 ?>

	<section class="container mx-5 light-static">

    	<div class="header">
			<h1><?php echo $titulo_header; ?></h1>
			<hr>
			<div class="alert">
				<?php echo isset($alert) ? $alert : ''; ?>
			</div>
        </div>
        <form action="" method="post">
            <div class="form-row">
                <div class="form-group col-sm-6">
                    <label for="proveedor">* Nombre del Proveedor</label>
                    <input type="text" class="form-control" name="proveedor" id="proveedor" placeholder="Razon social" value="<?php echo $proveedor; ?>" required>
                </div>
                <div class="form-group col-sm-6">
                    <label for="telefono">* Teléfono Principal</label>
                    <input type="text" class="form-control" data-type="telefono" name="telefono" id="telefono" placeholder="(809) 000-0000" value="<?php echo $telefono; ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-sm-6">
                    <label for="contacto">Contacto del Proveedor</label>
                    <input type="text" class="form-control" name="contacto" id="contacto" placeholder="Nombre personal" value="<?php echo $contacto; ?>">
                </div>
                <div class="form-group col-sm-6">
                    <label for="celular">Teléfono del Contacto</label>
                    <input type="text" class="form-control" data-type="celular" name="celular" id="celular" placeholder="+1 (809) 000-0000" value="<?php echo $celular; ?>">
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label for="direccion">Dirección física del local</label>
                <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Dirección" value="<?php echo $direccion; ?>" required>
            </div>
            <div class="form-row"><div class="form-row">
                <div class="form-group  col-xs-6 col-sm-4 px-0">
                    <a type="button" href="lista_proveedor.php" class="btn light text-center vcenter"><i class="material-icons btn-icon">cancel</i></span> Cancelar</a>
                </div>
                <div class="form-group  col-xs-6 col-sm-4 px-0">
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