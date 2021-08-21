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
		if (!Puede('clientes','v')) {
			header('location: index.php');
		}

        //Filtro de Busqueda
        $filtro = '';

        // Paginador
        $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM cliente");

        if (!empty($_REQUEST['search'])) {
            $filtro = $_REQUEST['search'];
            $desde = 0;

            $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM cliente c
                                                    INNER JOIN tipocliente t ON c.tipo = t.idtipo
                                                    INNER JOIN usuario u ON c.usuario_id = u.idusuario
                                                    WHERE (c.cedula LIKE '%$filtro%'
                                                        OR c.nombre LIKE '%$filtro%' OR c.telefono LIKE '%$filtro%'
                                                        OR c.direccion LIKE '%$filtro%' OR c.tipo LIKE '%$filtro%'
                                                        OR u.usuario LIKE '%$filtro%' OR c.dateadd LIKE '%$filtro%')");

        }

        $total_reg = mysqli_fetch_array($sql_register)['total_reg'];

        $por_pagina = 20;
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

<!doctype html>
<html lang="es">
<head>
	<?php include 'includes/script.php' ?>
	<title>Lista Clientes</title>
</head>
<body>
	<?php include 'includes/header.php' ?>

	<div id="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <h1 class="light-static text-center"><span class="material-icons title-icon">people_alt</span>Listado de Clientes</h1>
            </div>
    <?php if (Puede('clientes','c')) { ?>
            <div class="col-xs-12 my-5 col-sm-4 col-md-2 col-lg-2 text-center">
                <a type="button" href="cliente.php" class="btn dark"><span class="material-icons title-icon">person_add</span> Nuevo cliente</a>
            </div>
    <?php } ?>    
            <div class="col-xs-12 my-5 col-sm-8 col-md-4 col-lg-4 text-center">
                <form method="get" action="lista_cliente.php"> 
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
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
    </div>
    <div class="table-responsive">
		<table class="table table-striped" id="listaTabla">
			<tr>
				<th><b>#</b> <i onclick="sortNumber()" class="fa fa-fw fa-sort"></th>
				<th>Identificacion<i onclick="sortName(1)" class="fa fa-fw fa-sort"></th>
				<th>Nombre<i onclick="sortName(2)" class="fa fa-fw fa-sort"></i></th>
				<th>Telefono<i onclick="sortName(3)" class="fa fa-fw fa-sort"></th>
				<th>Direccion<i onclick="sortName(4)" class="fa fa-fw fa-sort"></th>
				<th>Tipo Cliente<i onclick="sortName(5)" class="fa fa-fw fa-sort"></th>
				<th>Registrado por<i onclick="sortName(6)" class="fa fa-fw fa-sort"></th>
				<th>Fecha Registro<i onclick="sortName(7)" class="fa fa-fw fa-sort"></th>
			<?php 
				if (Puede('clientes','e')) {
			?>
				<th>Acciones</th>
			<?php
				}
			?>
			</tr>
			<?php 

				// Contenido de la Tabla
				$list_cliente = mysqli_query($conn,"SELECT c.idcliente, c.cedula, c.nombre, c.telefono, c.direccion, t.tipo, u.usuario, c.dateadd, c.estatus
													FROM cliente c
													INNER JOIN tipocliente t ON c.tipo = t.idtipo
													INNER JOIN usuario u ON c.usuario_id = u.idusuario
													WHERE (c.cedula LIKE '%$filtro%'
														OR c.nombre LIKE '%$filtro%'
														OR c.telefono LIKE '%$filtro%'
														OR c.direccion LIKE '%$filtro%'
														OR t.tipo LIKE '%$filtro%'
														OR u.usuario LIKE '%$filtro%'
														OR c.dateadd LIKE '%$filtro%')
													ORDER BY c.idcliente ASC");
				
				$result = mysqli_num_rows($list_cliente);

				if ($result > 0) {
					while ($data = mysqli_fetch_array($list_cliente)) {
						if ($data['estatus'] == 'activo') {

			?>			
							<tr>
								<td><?php echo $data['idcliente']; ?></td>
								<td><?php echo $data['cedula']; ?></td>
								<td><?php echo $data['nombre']; ?></td>
								<td><?php echo $data['telefono']; ?></td>
								<td><?php echo $data['direccion']; ?></td>
								<td><?php echo $data['tipo']; ?></td>
								<td><?php echo $data['usuario']; ?></td>
								<td><?php echo $data['dateadd']; ?></td>
			<?php 
							if (Puede('clientes','e')) {
			?>
								<td>
									<a class="link_edit" href="cliente.php?modificarID=<?php echo $data['idcliente']; ?>" data-toggle="tooltip" title="Editar">
                                        <span class="glyphicon glyphicon-edit"></span></a> | 
			<?php
									if (Puede('clientes','d')) {
			?>							
								    <a class="link_dec" href="cliente.php?eliminarID=<?php echo $data['idcliente']; ?>" data-toggle="tooltip" title="Inactivar">
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
								<td><?php echo $data['idcliente']; ?></td>
								<td><?php echo $data['cedula']; ?></td>
								<td><?php echo $data['nombre']; ?></td>
								<td><?php echo $data['telefono']; ?></td>
								<td><?php echo $data['direccion']; ?></td>
								<td><?php echo $data['tipo']; ?></td>
								<td><?php echo $data['usuario']; ?></td>
								<td><?php echo $data['dateadd']; ?></td>
			<?php 
							if (Puede('clientes','e')) {
			?>
								<td>
									<a class="link_edit" href="cliente.php?modificarID=<?php echo $data['idcliente']; ?>" data-toggle="tooltip" title="Editar">
                                        <span class="glyphicon glyphicon-edit"></span></a> | 
			<?php
									if (Puede('clientes','d')) {
			?>							
									<a class="link_dec" href="cliente.php?eliminarID=<?php echo $data['idcliente']; ?>" data-toggle="tooltip" title="Activar">
                                        <span class="glyphicon glyphicon-eye-open"></span></a></a>
			<?php						
									}
								}
			?>	
								
								</td>
							</tr>
			<?php
						}
					}
				}

			 ?>
		</table>
	</div>
    <hr>
    <button class="btn dark" data-toggle="collapse" data-target="#addInsert">Simple collapsible</button>
    <div class="collapse" id="addInsert">
        <hr>
        <?php 
            $_GET['addInside'] = 'new';
            include 'cliente.php';
        ?>
    </div>

	<?php include 'includes/footer.php' ?>
</body>
</html>