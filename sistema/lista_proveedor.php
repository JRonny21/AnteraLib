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

        if (!Puede('v','proveedores')) {
			header('location: index.php');
		}

        //Filtro de Busqueda
        $filtro = '';

        // Paginador
        $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM proveedor");

        if (!empty($_REQUEST['search'])) {
            $filtro = $_REQUEST['search'];
            $desde = 0;

            $sql_register = mysqli_query ($conn, "SELECT COUNT(*) as total_reg FROM proveedor p
                                                    WHERE (proveedor LIKE '%$filtro%' OR contacto LIKE '%$filtro%'
                                                        OR telefono LIKE '%$filtro%' OR direccion LIKE '%$filtro%')");

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

        // Contenido de la Tabla
        $proveedor_Query = mysqli_query($conn,"SELECT *	FROM proveedor
                                            WHERE (proveedor LIKE '%$filtro%' OR contacto LIKE '%$filtro%'
                                                OR telefono LIKE '%$filtro%' OR direccion LIKE '%$filtro%')
                                            ORDER BY codproveedor ASC");
	}

 ?>

<!doctype html>
<html lang="es">
<head>
	<?php include 'includes/script.php' ?>
	<title>Lista Proveedores</title>
</head>
<body>
	<?php include 'includes/header.php' ?>

	<div id="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <h1 class="light-static text-center"><span class="material-icons title-icon">business</span>Listado de Proveedores</h1>
            </div>
    <?php if (Puede('c','proveedores')) { ?>
            <div class="col-xs-12 my-5 col-sm-4 col-md-3 col-lg-3 text-center">
                <a type="button" href="proveedor.php" class="btn dark"><span class="material-icons btn-icon">add_business</span> Nuevo Proveedor</a>
            </div>
    <?php } ?>    
            <div class="col-xs-12 my-5 col-sm-8 col-md-3 col-lg-3 text-center">
                <form method="get" action="lista_proveedor.php"> 
                    <div class="row">
                            <div class="input-group">
                                <input class="form-control" type="search" name="search" placeholder="Filtrar" value="<?php echo $filtro; ?>" aria-label="Search"/>
                                <div class="input-group-btn">
                                    <button class="btn dark" type="submit">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>
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
		<table class="table table-striped">
			<tr>
				<th><input type="checkbox" name="select_all" id="select_all" value="select_all" /></th>
				<th>Proveedor</th>
				<th>Telefono</th>
				<th>Contacto</th>
                <th>Celular</th>
				<th>Direccion</th>
			<?php 
				if (Puede('e','proveedores')) {
			?>
				<th>Acciones</th>
			<?php
				}
			?>
			</tr>
			<?php 
            foreach($proveedor_Query as $i => $data) { 
                $classEstatus = ($data['estatus'] == 'activo') ? '' : 'reg_desactivado';
                $estatus = ($data['estatus'] == 'activo') ? 
                        array('lb' => 'Desactivar', 'btn' => 'glyphicon-eye-close' ) :
                        array('lb' => 'Activar', 'btn' => 'glyphicon-eye-open' ) ; ?>
		
                <tr class="<?php echo $classEstatus; ?>">
                    <td><input type="checkbox" name="select_item" id="select_item" value="<?php echo $data['codproveedor']; ?>" /></td>
                    <td><?php echo $data['proveedor']; ?></td>
                    <td><?php echo $data['telefono']; ?></td>
                    <td><?php echo $data['contacto']; ?></td>
                    <td><?php echo $data['celular']; ?></td>
                <?php if(!empty($data['direccion'])) { ?>
                    <td><a type="button" class="btn dark-static" data-type="localizar" href="#">
                        <span class="material-icons btn-icon">place</span><small><?php echo $data['direccion']; ?></small></a>
                    </td>
                <?php  } if (Puede('e','proveedores') && $data['codproveedor'] != '0') { ?>
                    <td>
                        <a class="link_edit" href="proveedor.php?modificarID=<?php echo $data['codproveedor']; ?>" data-toggle="tooltip" title="Editar">
                            <span class="glyphicon glyphicon-edit"></span></a> | 
                    <?php if (Puede('d','proveedores')) { ?>							
                        <a class="link_dec" href="proveedor.php?desactivarID=<?php echo $data['codproveedor']; ?>"
                            data-toggle="tooltip" title="<?php echo $estatus['lb']; ?>">
                            <span class="glyphicon <?php echo $estatus['btn']; ?>"></span></a> | 
                        <a class="link_dec" href="proveedor.php?eliminarID=<?php echo $data['codproveedor']; ?>"
                            data-toggle="tooltip" title="Eliminar">
                            <span class="glyphicon glyphicon-trash"></span></a>
                    <?php } ?>
                <?php } ?>
                    </td>
                </tr>	
            <?php } ?>	
		</table>
	</div>
	<?php include 'includes/footer.php' ?>
</body>
</html>