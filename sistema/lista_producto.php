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

		if (!Puede('v','productos')) {
			header('location: index.php');
		}

        //Variables de entorno
        $filtro = '';
        $classEstatus = '';
        $estatus = '';
        $location = 'inventario/';
		$id_usuario = $_SESSION['idUser'];

        $producto_Query = mysqli_query($conn,"SELECT p.codproducto, p.descripcion, p.codigoBarra, p.costo, p.precio, p.existencia,
                                                    p.foto, p.estatus, cla.clase, cla.color, cat.categoria, pr.proveedor, us.usuario
                                                FROM producto p
                                        LEFT JOIN proveedor pr ON p.codproveedor = pr.codproveedor
                                        LEFT JOIN categoriaProducto cat ON p.idcategoria = cat.idcategoria
                                        LEFT JOIN claseProducto cla ON p.idclase = cla.idclase
                                        LEFT JOIN usuario us ON p.idusuario = us.idusuario
                                        ORDER BY p.codproducto ASC;");

        if (!empty($_REQUEST['search'])) {
            $filtro = $_REQUEST['search'];
            $desde = 0;

            $producto_Query = mysqli_query($conn,"SELECT p.codproducto, p.descripcion, p.codigoBarra, p.costo, p.precio, p.existencia,
                                                    p.foto, p.estatus, cla.clase, cla.color, cat.categoria, pr.proveedor, us.usuario
                                                FROM producto p
                                                    LEFT JOIN proveedor pr ON p.codproveedor = pr.codproveedor
                                                    LEFT JOIN categoriaProducto cat ON p.idcategoria = cat.idcategoria
                                                    LEFT JOIN claseProducto cla ON p.idclase = cla.idclase
                                                    LEFT JOIN usuario us ON p.idusuario = us.idusuario
                                                WHERE (p.codigoBarra LIKE '%$filtro%' OR p.descripcion LIKE '%$filtro%'
                                                        OR cla.clase LIKE '%$filtro%' OR cat.categoria LIKE '%$filtro%'
                                                        OR pr.proveedor LIKE '%$filtro%' OR us.usuario LIKE '%$filtro%')
                                                ORDER BY p.codproducto ASC;");

        }
    }

 ?>

<!doctype html>
<html lang="es">
<head>
	<?php include 'includes/script.php' ?>
	<title>Lista Productos</title>
</head>
<body>
	<?php include 'includes/header.php' ?>

	<div id="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <h1 class="light-static text-center"><span class="material-icons title-icon">category</span>Listado de Productos</h1>
            </div>
    <?php if (Puede('c','productos')) { ?>
            <div class="col-xs-12 my-5 col-sm-4 col-md-3 col-lg-3 text-center">
                <a type="button" href="producto.php" class="btn dark"><span class="material-icons btn-icon">library_add</span> Nuevo Producto</a>
            </div>
    <?php } ?>    
            <div class="col-xs-12 my-5 col-sm-8 col-md-3 col-lg-3 text-center">
                <form method="get" action="lista_producto.php"> 
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
			    <th>#</th>
			    <th>Descripcion</th>
                <th>Codigo</th>
				<th>Clase</th>
				<th>Categoria</th>
                <th>Proveedor</th>
			<?php if (Puede('v','compras')) {?>	
                <th>Costo</th>
            <?php } ?>
                <th>Precio</th>
                <th>Existencia</th>
                <th>Foto</th>
            <?php if (Puede('v','usuarios')) {?>	
                <th>Registrado por</th>
            <?php } ?>
                <th>Estatus</th>
			<?php if (Puede('e','productos')) { ?>
				<th>Acciones</th>
			<?php } ?>
			</tr>
			<?php foreach($producto_Query as $i => $data) {
					$classEstatus = ($data['estatus'] == 'activo') ? '' : 'reg_desactivado';
                    $estatus = ($data['estatus'] == 'activo') ? 
                            array('lb' => 'Desactivar', 'btn' => 'glyphicon-eye-close' ) :
                            array('lb' => 'Activar', 'btn' => 'glyphicon-eye-open' ) ; ?>			
                    <tr class="<?php echo $classEstatus; ?>">
                        <td><?php echo $data['codproducto']; ?></td>
			            <td><?php echo $data['descripcion']; ?></td>
                        <td><?php echo $data['codigoBarra']; ?></td>
                        <td style="background:<?php echo $data['color']?>;"><?php echo $data['clase']; ?></td>
                        <td><?php echo $data['categoria']; ?></td>
                        <td><?php echo $data['proveedor']; ?></td>
                    <?php if (Puede('v','compras')) {?>
                        <td>RD $<?php echo $data['costo']; ?></td>
                    <?php } ?>
                        <td>RD $<?php echo $data['precio']; ?></td>
                        <td><?php echo $data['existencia']; ?></td>
                        <td>
                            <div class="photo_list">
                                <img onClick="$(this).imgView('img_<?php echo $i?>','<?php echo $data['descripcion']?>','<?php echo $data['color']?>')"
                                    id="img_<?php echo $i?>" src="<?php echo $location.$data['foto']; ?>">
                            </div>
                        </td>
                    <?php if (Puede('v','usuarios')) {?>
                        <td><?php echo $data['usuario']; ?></td>
                    <?php } ?>
                        <td><?php echo $data['estatus']; ?></td>
                    <?php if (Puede('e','productos')) { ?>
                        <td>
                            <a class="link_edit" href="producto.php?modificarCOD=<?php echo $data['codproducto']; ?>" data-toggle="tooltip" title="Editar">
                                <span class="glyphicon glyphicon-edit"></span></a> | 
                        <?php if (Puede('d','productos')) { ?>							
                            <a class="link_dec" href="producto.php?desactivarCOD=<?php echo $data['codproducto'];?>"
                                data-toggle="tooltip" title="<?php echo $estatus['lb']; ?>">
                                <span class="glyphicon <?php echo $estatus['btn']; ?>"></span></a> | 
                            <a class="link_dec" href="producto.php?eliminarCOD=<?php echo $data['codproducto']; ?>"
                                data-toggle="tooltip" title="Eliminar">
                                <span class="glyphicon glyphicon-trash"></span></a>
                        <?php } ?>
                    <?php }	?>	
                        
                        </td>
                    </tr>
		    <?php } ?>
		</table>
	</div>

    <!-- Modal para mostrar imagenes -->
    <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
                    <h4 class="modal-title" id="myModalLabel"></h4>
                </div>
                <div class="modal-body" style="text-align:center">
                    <img src="" id="imagepreview" style="width: 100%;" >
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn dark" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

	<?php include 'includes/footer.php' ?>
</body>
</html>