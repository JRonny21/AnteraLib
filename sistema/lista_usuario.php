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

		if (!Puede('usuarios','v')) {
			header('location: main.php');
		}

        //Filtro de Busqueda
        $filtro = '';

        // Paginador
        $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM usuario");

        if (!empty($_REQUEST['search'])) {
            $filtro = $_REQUEST['search'];
            $desde = 0;

            $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM usuario u
                                                    INNER JOIN rol r ON u.rol = r.idrol
                                                    WHERE (u.nombre LIKE  '%$filtro%' OR u.correo LIKE '%$filtro%'
                                                        OR r.rol LIKE '%$filtro%' OR u.usuario LIKE '%$filtro%')");

        }

        $total_reg = mysqli_fetch_array($sql_register)['total_reg'];

        $por_pagina = 5;
        $pagina = 1;
        if (empty($_GET['pag'])) {
            $pagina = 1;
        } else {
            $pagina = $_GET['pag'];
        }

        $desde = ($pagina-1) * $por_pagina;
        $total_pag = ceil($total_reg / $por_pagina);

	}

 ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include 'includes/script.php' ?>
	<title>Lista Usuarios</title>
</head>
<body>
	<?php include 'includes/header.php' ?>

	<div id="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <h1 class="light-static text-center"><span class="material-icons title-icon">admin_panel_settings</span>Listado de Usuarios</h1>
            </div>
    <?php if (Puede('usuarios','c')) { ?>
            <div class="col-xs-12 text-center my-5 col-sm-4 col-md-3 col-lg-2 text-center">
                <a type="button" href="usuario.php" class="btn dark"><span class="material-icons btn-icon">add_moderator</span> Nuevo Usuario</a>
            </div>
    <?php } ?>    
            <div class="col-xs-12 text-center my-5 col-sm-8 col-md-3 col-lg-4 text-center">
                <form method="get" action="lista_usuario.php"> 
                    <div class="row">
                        <div class="col-xs-12 col-md-10">
                            <div class="input-group">
                                <input class="form-control" type="search" name="search" placeholder="Filtrar" value="<?php echo $filtro; ?>" aria-label="Search"/>
                                <div class="input-group-btn">
                                    <button class="btn dark" type="submit">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr/>
    </div>
    <div class="table-responsive">
		<table class="table table-striped">
			<tr>
				<th>#</th>
				<th>Nombre</th>
				<th>Correo</th>
				<th>Rol</th>
				<th>Usuario</th>
			<?php 
				if (Puede('usuarios','e')) {
			?>
				<th>Acciones</th>
			<?php
				}
			?>
			</tr>
			<?php 

				// Contenido de la Tabla
				$list_users = mysqli_query($conn,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol, u.estatus
												FROM usuario u INNER JOIN rol r ON u.rol = r.idrol
												WHERE (u.nombre LIKE  '%$filtro%'
													OR u.correo LIKE '%$filtro%'
													OR r.rol LIKE '%$filtro%'
													OR u.usuario LIKE '%$filtro%')
												ORDER BY u.idusuario ASC");
				
				$result = mysqli_num_rows($list_users);

				if ($result > 0) {
					while ($data = mysqli_fetch_array($list_users)) {
						if ($data['estatus'] == 'activo') {

			?>			
							<tr>
								<td><?php echo $data['idusuario']; ?></td>
								<td><?php echo $data['nombre']; ?></td>
								<td><?php echo $data['correo']; ?></td>
								<td><?php echo $data['rol']; ?></td>
								<td><?php echo $data['usuario']; ?></td>
			<?php 
							if (Puede('usuarios','e')) {
			?>
								<td>
									<a class="link_edit" href="usuario.php?modificarID=<?php echo $data['idusuario']; ?>" data-toggle="tooltip" title="Editar">
                                        <span class="glyphicon glyphicon-edit"></span></a> | 
			<?php
									if (Puede('usuarios','d')) {
			?>							
								    <a class="link_dec" href="usuario.php?eliminarID=<?php echo $data['idusuario']; ?>" data-toggle="tooltip" title="Inactivar">
                                        <span class="glyphicon glyphicon-eye-close"></span></a></a>
			<?php						
									}
								}
			?>	
								
								</td>
							</tr>		
			<?php
						
						} else {
			?>
							<tr class="reg_desactivado">
								<td><?php echo $data['idusuario']; ?></td>
								<td><?php echo $data['nombre']; ?></td>
								<td><?php echo $data['correo']; ?></td>
								<td><?php echo $data['rol']; ?></td>
								<td><?php echo $data['usuario']; ?></td>
			<?php 
							if (Puede('usuarios','e')) {
			?>
								<td>
									<a class="link_edit" href="usuario.php?modificarID=<?php echo $data['idusuario']; ?>" data-toggle="tooltip" title="Editar">
                                        <span class="glyphicon glyphicon-edit"></span></a> | 

			<?php
									if (Puede('usuarios','d')) {
			?>							
								    <a class="link_dec" href="usuario.php?eliminarID=<?php echo $data['idusuario']; ?>" data-toggle="tooltip" title="Activar">
                                        <span class="glyphicon glyphicon-eye-open"></span></a></a>
			<?php						
									}
                        echo '</td>';
                            }
                    echo '</tr>';
						}
					}
				}
			 ?>
		</table>
    </div>

	<?php include 'includes/footer.php' ?>
</body>
</html>