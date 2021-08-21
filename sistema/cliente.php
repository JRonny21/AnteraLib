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
		$status = 0;
		$titulo = '';
        $titulo_header = '';
		$boton = '';

		$idcliente = '';
		$cedula = '';
		$nombre = '';
		$telefono = '';
		$direccion = '';
		$idtipo = '';
        $tipo = '';
		$id_usuario = $_SESSION['idUser'];

		if ((empty($_GET) || !empty($_GET['addInside'])) && (Puede('clientes','c'))) {
            $titulo = 'Nuevo Cliente';
			$titulo_header = '<span class="material-icons title-icon">person_add</span>Nuevo Cliente';
			$boton = '<span class="material-icons btn-icon">save</span>Guardar';

			if (!empty($_POST)) {
				if (empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['tipo'])) {
					$alert = '<div class="alert alert-danger" role="alert">Todos los campos son obligatorios</div>';
				} else {

					$cedula = $_POST['cedula'];
					$nombre = $_POST['nombre'];
					$telefono = $_POST['telefono'];
					$direccion = $_POST['direccion'];
					$idtipo = $_POST['tipo'];

					$result = 0;
					if ((strtolower($cedula) != 'menor')) {
						$query = mysqli_query($conn,"SELECT * FROM cliente WHERE cedula = '$cedula'");
						$result = mysqli_fetch_array($query);
					}

					if ($result > 0) {
						$alert = '<div class="alert alert-warning" role="alert">El documento de identidad ya ha sido registrado</div>';			
					} else {

						$query_last = mysqli_query($conn, "SELECT idcliente FROM `cliente` ORDER BY idcliente DESC LIMIT 1");
						$idlast = 0;

						while ($getid = mysqli_fetch_array($query_last)) {
							$idlast = $getid['idcliente'] + 1;
						}

						mysqli_query($conn,"ALTER TABLE cliente AUTO_INCREMENT = '$idlast'");

						$insert = mysqli_query($conn, "INSERT INTO cliente(idcliente,cedula,nombre,telefono,direccion,tipo,usuario_id)
																VALUES ('$idlast','$cedula','$nombre','$telefono','$direccion','$idtipo','$id_usuario')");

						if ($insert) {
							$alert = '<div class="alert alert-success" role="alert">Cliente '.$nombre.' guardado correctamente</div>';
                            $url = (empty($_GET['addInside'])) ? "lista_cliente.php" : "pedido.php?load=".$idlast ;
						    header('Refresh: 2;URL='.$url); 
						} else {
							$alert = '<div class="alert alert-danger" role="alert">Error al guardar el cliente '.$nombre.'</div>';
						}
					}
				}
			}

		} else if(!empty($_GET)) {
			if (!(Puede('clientes','c'))) {
				header ('location: lista_cliente.php');
			}
			//Cargar Datos
			$idcliente = (empty($_GET['modificarID'])) ? $_GET['eliminarID'] : $_GET['modificarID'];

			$query = mysqli_query($conn, "SELECT c.idcliente, c.cedula, c.nombre, c.telefono, c.direccion, t.idtipo, c.dateadd, c.usuario_id, c.estatus, t.tipo, t.max_descuento 
                                            FROM cliente c INNER JOIN tipocliente t ON c.tipo = t.idtipo
                                            WHERE c.idcliente = '$idcliente'");

			$result = mysqli_num_rows($query);
			if ($result == 0) {
				header ('location: lista_cliente.php');

			} else {
				while ($data = mysqli_fetch_array($query)) {
					$status = ($data['estatus'] == 'activo') ? 'desactivar' : 'activar';
					$idcliente = $data['idcliente'];
					$cedula = $data['cedula'];
					$nombre = $data['nombre'];
					$telefono = $data['telefono'];
					$direccion = $data['direccion'];
					$idtipo = $data['idtipo'];
                    $tipo = $data['tipo'].' - descuento maximo: '.$data['max_descuento'].'%';
				}
			}
		}
        
		//Modificar datos del cliente
		if(!empty($_GET['modificarID']) && (Puede('clientes','e'))) {
            $titulo = 'Modificar Cliente';
			$titulo_header = '<span class="material-icons title-icon">edit</span>Modificar Cliente <br><small>'.$nombre.'</small>';
			$boton = '<span class="material-icons btn-icon">update</span>Actualizar';

			if (!empty($_POST)) {
				if (empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['tipo'])) {
					$alert = '<div class="alert alert-warning" role="alert">Todos los campos son obligatorios</div>';
				} else {
					$idcliente = $_GET['modificarID'];
					$cedula2 = $_POST['cedula'];
					$nombre = $_POST['nombre'];
					$telefono = $_POST['telefono'];
					$direccion = $_POST['direccion'];
					$idtipo = $_POST['tipo'];

                    $ced_query = mysqli_query($conn,"SELECT * FROM cliente WHERE cedula = '$cedula2'");

                    $res_ced = mysqli_fetch_array($ced_query);

					if($res_ced > 0 && $cedula2 != 'menor' && $cedula != $cedula2){
                        $alert = '<div class="alert alert-warning" role="alert">
                                    El documento de identidad ya ha sido registrado a nombre de <b><small>'. $res_ced['nombre'] .'</small></div>';
                    } else {

						$query_update = mysqli_query($conn, "UPDATE cliente
																	SET cedula = '$cedula2', nombre = '$nombre', telefono = '$telefono',
																		direccion = '$direccion', tipo = '$idtipo'
																	WHERE idcliente = '$idcliente'");
                        
                        if ($query_update) {
							$alert = '<div class="alert alert-success" role="alert">Cliente '.$nombre.' actualizado correctamente</div>';
						    header('Refresh: 1;URL=lista_cliente.php'); 
						} else {
							$alert = '<div class="alert alert-danger" role="alert">Error al actualizar el cliente '.$nombre.'</div>';
						}
					}
				}
			}


		} else if(!(Puede('clientes','e'))) {
			header('location: lista_cliente.php');
		}

		//Eliminar datos del cliente
		if(!empty($_GET['eliminarID']) && (Puede('clientes','d'))) {
			$titulo = (($status != 'desactivar') ? 'Activar' : 'Desactivar').' Cliente';
			$boton = ($status != 'desactivar') ? 'Activar Cliente' : 'Desactivar Cliente';

			if (!empty($_POST)) {
				$idcliente =  $_POST['id'];
				$query = mysqli_query($conn, "SELECT * FROM cliente WHERE idcliente = '$idcliente'");
				$result = mysqli_num_rows($query);
				
				if ($result == 0) {
					header ('location: lista_cliente.php');

				} else {
					$estatus = ($status == 'desactivar') ? 'inactivo' : 'activo';

					$query = mysqli_query($conn, "UPDATE cliente
												SET estatus = '$estatus'
												WHERE idcliente = '$idcliente'");
					if ($query) {
						header ('location: lista_cliente.php');
					} else {
						echo "<script type='text/javascript'> alert('Error al modificar usuario'); </script>";
					}
				}	
				
			}

		} else if(!(Puede('clientes','d'))) {
			header('location: lista_cliente.php');
		}
	}
?>

<?php if (!empty($_GET['addInside'])) { ?>
    <section class="container mx-5 light-static">
    	<div class="header light-static">
			<h4><?php echo $titulo_header; ?></h4>
			<div id="snackbar">
				<?php echo isset($alert) ? $alert : ''; ?>
			</div>
        </div>
        <form action="" method="post">
            <div class="form-row">
                <div class="form-group col-sm-4">
                    <input type="text" class="form-control" name="cedula" id="cedula" placeholder="Cedula" value="<?php echo $cedula; ?>" maxlength="20" required>
                </div>
                <div class="form-group col-sm-4">
                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre completo" value="<?php echo $nombre; ?>" required>
                </div>
                <div class="form-group col-sm-4">
                    <input type="text" class="form-control" name="telefono" id="telefono" data-type="telefono" placeholder="(809) 000-0000" value="<?php echo $telefono; ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-sm-6">
                    <?php $query_tipo = mysqli_query($conn,"SELECT * FROM tipocliente");
                        $result_tipo = mysqli_num_rows($query_tipo);  ?>
                    <select name="tipo" id="tipo" class="form-control" <?php if(!Puede('asignaDescuento','e')) { echo "disabled"; } ?> >
                    <?php if ($result_tipo > 0) {
                                while ($idtipo = mysqli_fetch_array($query_tipo)){  ?>
                        <option value="<?php echo $idtipo['idtipo']; ?>"><?php echo $idtipo['tipo'].' (descuento max.: '.$idtipo['max_descuento'].'%)'; ?></option>
                            <?php }
                            } ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Calle, Casa, Sector" value="<?php echo $direccion; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group  col-xs-6 col-sm-4 px-0">
                    <a type="button" href="lista_cliente.php" class="btn light text-center vcenter"><i class="material-icons btn-icon">cancel</i></span> Cancelar</a>
                </div>
                <div class="form-group  col-xs-6 col-sm-4 px-0">
                    <button type="submit" class="btn dark"><?php echo $boton; ?></button>
                </div>
            </div>
        </form>
    </section>
<?php } else { ?>

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
                <h2 class="display-1 light-static">¿Estás seguro de <?php echo $status ?> el siguiente registro?</h2>
			</div>
            <div class="panel-body">
                <h2 class="panel-title">Nombre: <span><?php echo $nombre ?></span></h2>
                <div class="panel-body">
                    <h5>Cedula: <span><?php echo $cedula ?></span></h5>
                    <h5>Teléfono: <span><?php echo $nombre ?></span></h5>
                    <h5>Tipo: <span><?php echo $tipo ?></span></h5>
                </div>
            </div>
            <div class="panel-footer">
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?php echo $idcliente; ?>">
                    <a href="lista_cliente.php" class="btn light text-center"><i class="material-icons btn-icon">cancel</i>Cancelar</a>
                    <button type="submit" class="btn dark text-center"><i class="material-icons btn-icon">business</i><?php echo $boton; ?></button>
                </form>
            </div>
		</div>
	</section>

	<?php
		} else {
	 ?>

	<section class="container mx-5 light-static">
    	<div class="header light-static">
			<h1><?php echo $titulo_header; ?></h1>
			<hr>
			<div class="alert">
				<?php echo isset($alert) ? $alert : ''; ?>
			</div>
        </div>
        <form action="" method="post">
            <div class="form-row">
                <div class="form-group col-sm-6">
                <label for="cedula">Documento de Identidad <br></label>
                <input type="text" class="form-control" name="cedula" id="cedula" placeholder="menor o numeros" value="<?php echo $cedula; ?>" maxlength="20">
                </div>
                <div class="form-group col-sm-6">
                <label for="nombre"><br>* Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre completo" value="<?php echo $nombre; ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-sm-6">
                <label for="telefono">* Telefono <small class="my-0">(Preferible con Whatsapp)</small></label>
                <input type="text" class="form-control" name="telefono" id="telefono" data-type="telefono" placeholder="(809) 000-0000" value="<?php echo $telefono; ?>" required>
                </div>
                <div class="form-group col-sm-6">
                    <label for="tipo">* Tipo de cliente</label>
                    <?php 

                        $query_tipo = mysqli_query($conn,"SELECT * FROM tipocliente");
                        $result_tipo = mysqli_num_rows($query_tipo);

                    ?>
                    <select name="tipo" id="tipo" class="form-control" <?php if(!Puede('asignaDescuento','e')) { echo "disabled"; } ?> >
                    <?php 
                            if ($result_tipo > 0) {
                                while ($idtipo = mysqli_fetch_array($query_tipo)){
                    ?>
                                    <option value="<?php echo $idtipo['idtipo']; ?>"><?php echo $idtipo['tipo'].' (descuento max.: '.$idtipo['max_descuento'].'%)'; ?></option>
                    <?php
                                }
                            }
                    ?>
                        
                    </select>
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label for="direccion">Direccion</label>
                <input type="text" class="form-control" name="direccion" id="direccion" placeholder="Calle, Casa, Sector" value="<?php echo $direccion; ?>">
            </div>
            <div class="form-row"><div class="form-row">
                <div class="form-group  col-xs-6 col-sm-4 px-0">
                    <a type="button" href="lista_cliente.php" class="btn light text-center vcenter"><i class="material-icons btn-icon">cancel</i></span> Cancelar</a>
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

<? } ?>