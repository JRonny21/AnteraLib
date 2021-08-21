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
        
		if (!Puede('ventas','v')) {
			header('location: index.php');
		}

        //Filtro de Busqueda
        $filtro = '';

        // Paginador
        $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM pedido");

        if (!empty($_REQUEST['search'])) {
            $filtro = $_REQUEST['search'];
            $desde = 0;

            $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM pedido p
                                                    INNER JOIN detallepedido dp ON p.correlativodetalle = dp.id
                                                    INNER JOIN usuario u ON p.idusuario = u.idusuario
                                                    WHERE (p.id LIKE '%$filtro%'
		                                                        OR dp.nombre LIKE '%$filtro%' OR dp.celular LIKE '%$filtro%'
		                                                        OR dp.direccion LIKE '%$filtro%' OR dp.fecha_registro LIKE '%$filtro%'
		                                                        OR dp.fecha_entrega LIKE '%$filtro%' OR dp.estatusPedido LIKE '%$filtro%'
		                                                        OR dp.estatusProceso LIKE '%$filtro%' OR dp.comentario LIKE '%$filtro%'
		                                                        OR u.usuario LIKE '%$filtro%')");

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
	<title>Lista Pedidos</title>
</head>
<body>
	<?php include 'includes/header.php' ?>

	<div id="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <h1 class="light-static text-center"><span class="material-icons title-icon">add_shopping_cart</span>Listado de Pedidos</h1>
            </div>
    <?php if (Puede('ventas','c')) { ?>
            <div class="col-xs-12 my-5 col-sm-4 col-md-2 col-lg-2 text-center">
                <a type="button" href="pedido.php" class="btn dark"><span class="material-icons title-icon">add_shopping_cart</span> Nuevo pedido</a>
            </div>
    <?php } ?>    
            <div class="col-xs-12 my-5 col-sm-8 col-md-4 col-lg-4 text-center">
                <form method="get" action="lista_pedido.php"> 
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
				<th>Fecha<i onclick="sortName(1)" class="fa fa-fw fa-sort"></th>
				<th>Cliente<i onclick="sortName(2)" class="fa fa-fw fa-sort"></i></th>
				<th>Total<i onclick="sortName(3)" class="fa fa-fw fa-sort"></th>
				<th>Fecha Entrega<i onclick="sortName(4)" class="fa fa-fw fa-sort"></th>
				<th>Dias Restantes<i onclick="sortName(5)" class="fa fa-fw fa-sort"></th>
				<th>Registrado por<i onclick="sortName(6)" class="fa fa-fw fa-sort"></th>
				<th>Estatus Pedido<i onclick="sortName(7)" class="fa fa-fw fa-sort"></th>
				<th>Estado Orden<i onclick="sortName(8)" class="fa fa-fw fa-sort"></th>
				<th>Estatus<i onclick="sortName(9)" class="fa fa-fw fa-sort"></th>
			<?php 
				if (Puede('ventas','e')) {
			?>
				<th>Acciones</th>
			<?php
				}
			?>
			</tr>
			<?php 

				// Contenido de la Tabla
				$lista_pedido = mysqli_query($conn,"SELECT p.id, dp.fecha_registro, dp.nombre, dp.total, dp.fecha_entrega, DATEDIFF(dp.fecha_entrega,current_date()) as days, u.usuario, ep.estado, ep.color, dp.estatusProceso, dp.estatus
                                                    FROM detallepedido dp
                                                    INNER JOIN pedido p ON dp.id = p.correlativodetalle
                                                    INNER JOIN estadopedido ep ON estatusPedido = ep.id
                                                    INNER JOIN usuario u ON dp.idusuario = u.idusuario
                                                    WHERE (p.id LIKE '%$filtro%'
                                                                OR dp.nombre LIKE '%$filtro%' OR dp.celular LIKE '%$filtro%'
                                                                OR dp.direccion LIKE '%$filtro%' OR dp.fecha_registro LIKE '%$filtro%'
                                                                OR dp.fecha_entrega LIKE '%$filtro%' OR dp.estatusPedido LIKE '%$filtro%'
                                                                OR dp.estatusProceso LIKE '%$filtro%' OR dp.comentario LIKE '%$filtro%'
                                                                OR u.usuario LIKE '%$filtro%')
                                                    ORDER BY days ASC");
				
				$result = mysqli_num_rows($lista_pedido);

				if ($result > 0) {
					while ($data = mysqli_fetch_array($lista_pedido)) {
						if ($data['estatus'] == 'activo') {

			?>			
							<tr>
								<td><?php echo $data['id']; ?></td>
								<td><?php echo $data['fecha_registro']; ?></td>
								<td><?php echo $data['nombre']; ?></td>
								<td><?php echo $data['total']; ?></td>
								<td><?php echo $data['fecha_entrega']; ?></td>
								<td><?php echo $data['days']; ?></td>
								<td><?php echo $data['usuario']; ?></td>
								<td style="background-color: <?php echo $data['color']; ?>"><?php echo $data['estado']; ?></td>
								<td><?php echo $data['estatusProceso']; ?></td>
								<td><?php echo $data['estatus']; ?></td>
			<?php
				if (Puede('ventas','c')) {
		   ?>
			                    <td>
					                <a href="pedido.php?verPedido=<?php echo $data['id']; ?>" data-toggle="tooltip" title="Ver pedido">
                                        <span class="material-icons btn-icon">add_shopping_cart</span></a>|
			<?php
			    }
			?>
			<?php 
							if (Puede('ventas','e')) {
			?>
									<a class="link_edit" href="pedido.php?modificarID=<?php echo $data['id']; ?>" data-toggle="tooltip" title="Editar">
                                        <span class="glyphicon glyphicon-edit"></span></a>| 
			<?php
									if (Puede('ventas','d')) {
			?>							
								    <a class="link_dec" href="pedido.php?eliminarID=<?php echo $data['id']; ?>" data-toggle="tooltip" title="Inactivar">
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
								<td><?php echo $data['id']; ?></td>
								<td><?php echo $data['fecha_registro']; ?></td>
								<td><?php echo $data['nombre']; ?></td>
								<td><?php echo $data['total']; ?></td>
								<td><?php echo $data['fecha_entrega']; ?></td>
								<td><?php echo $data['days']; ?></td>
								<td><?php echo $data['usuario']; ?></td>
								<td style="background-color: <?php echo $data['color']; ?>"><?php echo $data['estado']; ?></td>
								<td><?php echo $data['estatudProceso']; ?></td>
								<td><?php echo $data['estatus']; ?></td>
		    <?php
				if (Puede('ventas','c')) {
		   ?>
			                    <td>
					                <a href="pedido.php?verPedido=<?php echo $data['id']; ?>" data-toggle="tooltip" title="Ver Pedido">
                                        <span class="material-icons btn-icon">add_shopping_cart</span></a>|
			<?php
			    }
			?>
			<?php 
							if (Puede('ventas','e')) {
			?>
								<td>
									<a class="link_edit" href="pedido.php?modificarID=<?php echo $data['id']; ?>" data-toggle="tooltip" title="Editar">
                                        <span class="glyphicon glyphicon-edit"></span></a>| 
			<?php
									if (Puede('ventas','d')) {
			?>							
									<a class="link_dec" href="pedido.php?eliminarID=<?php echo $data['id']; ?>" data-toggle="tooltip" title="Activar">
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
	<?php include 'includes/footer.php' ?>
</body>
</html>