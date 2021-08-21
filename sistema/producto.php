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
		$actPROD = 0;
		$titulo = '';
        $titulo_header = '';
		$boton = '';

		$codproducto = '';
        $clase = '';
        $idclase = '';
        $categoria = '';
		$idcategoria = '';
		$descripcion = '';
        $codigoBarra = '';
		$proveedor = '';
        $codproveedor = '';
        $costo = '';
        $precio = '';
        $existencia = '';
        $foto = '';
        $def_product = 'def_product.png';
        $location = 'inventario/';
		$id_usuario = $_SESSION['idUser'];

        $proveedores_Query = mysqli_query($conn,"SELECT codproveedor, proveedor FROM proveedor");
        $categoria_Query = mysqli_query($conn,"SELECT * FROM categoriaProducto");
        $clase_Query = mysqli_query($conn,"SELECT * FROM claseProducto");

		// Crear nuevo producto
        if (empty($_GET) && Puede('c','productos')) {
            $titulo = 'Nuevo producto';
			$titulo_header = '<span class="material-icons title-icon">library_add</span>Nuevo producto';
			$boton = '<i class="material-icons btn-icon">save</i>Guardar';

			if (!empty($_POST)) {
				if (empty($_POST['producto']) || empty($_POST['categoria']) || empty($_POST['clase'])) {
					$alert = '<div class="alert alert-danger" role="alert">Campos marcados * son obligatorios</div>';
				} else {
					$codproveedor = $_POST['proveedor'];
					$producto = $_POST['producto'];
                    $producto = ucwords(strtolower($producto));
                    $codigoBarra = $_POST['codigoBarra'];
                    $idclase = $_POST['clase'];
					$idcategoria = $_POST['categoria'];
                    $costo = preg_replace("/[^0-9.]/","",$_POST['costo']);
					$precio = preg_replace("/[^0-9.]/","",$_POST['precio']);
                    $cantidad = $_POST['cantidad'];
					$foto = $_FILES['foto'];

                    $query_last = mysqli_query($conn, "SELECT codproducto FROM producto ORDER BY codproducto DESC LIMIT 1");
                    $idlast = 1;

                    $cantidad = ($idclase == 3) ? "&#x221e;" : $cantidad;

                    while ($getid = mysqli_fetch_array($query_last)) {
                        $idlast = $getid['codproducto'] + 1;
                    }

                    $url_temp = $foto['tmp_name'];
                    $src = '';
                    $imgProducto = $def_product;

                    if(!empty($foto['name'])) {
                        $img_nombre = 'producto_'.md5(date('d-m-Y H:m:s'));
                        $imgProducto = $img_nombre.'.png';
                        $src = $location.$imgProducto;
                    }

                    $insert = mysqli_query($conn, "INSERT INTO producto(codproducto,idclase,idcategoria,descripcion,codigoBarra,codproveedor,costo,precio,existencia,idusuario,foto)
                                                            VALUES ('$idlast','$idclase','$idcategoria','$producto','$codigoBarra','$codproveedor','$costo','$precio','$cantidad','$id_usuario','$imgProducto')");

                    if ($insert) {
                        if(!empty($foto['name'])) {
                            move_uploaded_file($url_temp, $src);
                        }
                        $alert = '<div class="alert alert-success" role="alert">producto '.$nombre.' guardado correctamente</div>';
                        echo "<script>setTimeout(function(){ window.history.go(-2); }, 1500);</script>";
                    } else {
                        $alert = '<div class="alert alert-danger" role="alert">Error al guardar el producto '.$nombre.'</div>';
                    }
				}
			}

		} else if(!empty($_GET)) {
			if (!Puede('c','productos')) {
				header ('location: lista_producto.php');
			}
			//Cargar Datos
			$codproducto = (empty($_GET['modificarCOD'])) ? 
                                ((empty($_GET['desactivarCOD'])) ? $_GET['eliminarCOD'] : $_GET['desactivarCOD']) : $_GET['modificarCOD'];

			$query = mysqli_query($conn, "SELECT p.descripcion, p.costo, p.precio, p.existencia, p.foto, p.estatus, pr.proveedor,
                                                cat.idcategoria, p.codigoBarra, cat.categoria, cla.idclase, cla.clase, cla.color
                                            FROM producto p
                                            LEFT JOIN proveedor pr ON p.codproveedor = pr.codproveedor
                                            INNER JOIN categoriaProducto cat ON p.idcategoria = cat.idcategoria
                                            INNER JOIN claseProducto cla ON p.idclase = cla.idclase
                                            WHERE p.codproducto = '$codproducto'");

			$result = mysqli_num_rows($query);
			if ($result == 0) {
				header ('location: lista_producto.php');

			} else {
				while ($data = mysqli_fetch_array($query)) {
					$actPROD = ($data['estatus'] == 'activo') ? 'desactivar' : 'activar';
					$idclase = $data['idclase'];
                    $clase = $data['clase'];
                    $idcategoria = $data['idcategoria'];
                    $categoria = $data['categoria'];
                    $producto = $data['descripcion'];
                    $producto = ucwords(strtolower($producto));
                    $codigoBarra = $data['codigoBarra'];
                    $codproveedor = $data['codproveedor'];
                    $proveedor = $data['proveedor'];
                    $costo = 'RD $'.$data['costo'];
                    $precio = 'RD $'.$data['precio'];
                    $cantidad = $data['existencia'];
                    $foto = $data['foto'];
				}
			}
		}

		//Modificar datos del producto
		if(!empty($_GET['modificarCOD']) && Puede('e','productos')) {
            $titulo = 'Modificar producto';
			$titulo_header = '<span class="glyphicon glyphicon-edit title-icon"></span>Modificar producto <br><small>'.$producto.'</small>';
			$boton = '<i class="material-icons btn-icon">update</i>Actualizar';

			if (!empty($_POST)) {
				if (empty($_POST['producto']) || empty($_POST['categoria']) || empty($_POST['clase'])) {
					$alert = '<div class="alert alert-danger" role="alert">Campos marcados * son obligatorios</div>';
				} else {
					$codproveedor = $_POST['proveedor'];
                    $codproducto = $_GET['modificarCOD'];
					$producto = $_POST['producto'];
                    $codigoBarra = $_POST['codigoBarra'];
					$idcategoria = $_POST['categoria'];
                    $idclase = $_POST['clase'];
                    $costo = preg_replace("/[^0-9.]/","",$_POST['costo']);
					$precio = preg_replace("/[^0-9.]/","",$_POST['precio']);
                    $cantidad = $_POST['cantidad'];
					$fotoNew = $_FILES['foto'];
                    
                    $cantidad = ($idclase == 3) ? "&#x221e;" : $cantidad;
                    
					$query = mysqli_query($conn,"SELECT * FROM producto 
													WHERE codproducto = '$codproducto'");

					$fotoOld =  mysqli_fetch_array($query)['foto'];
                    $checkif = $_POST['checkif'];

                    $result = mysqli_fetch_array($query);

					if ($result = 0) {
						$alert = '<div class="alert alert-warning" role="alert">El producto a modificar no existe</div>';
					} else {
                        
                        //print_r($fotoNew); echo ' - '.$fotoOld; exit;
                        $url_temp = $fotoNew['tmp_name'];
                        $src = '';
                        $imgProducto = $fotoOld;

                        if(!empty($fotoNew['name'])) {
                            $img_nombre = 'producto_'.md5(date('d-m-Y H:m:s'));
                            $imgProducto = $img_nombre.'.png';
                        } else if(empty($checkif)) {
                            $imgProducto = $def_product;
                        }
                        $src = $location.$imgProducto;

                        $query_update = mysqli_query($conn, "UPDATE producto
                                                                SET idcategoria = '$idcategoria', idclase = '$idclase', descripcion = '$producto',
                                                                codproveedor = '$codproveedor', costo = '$costo', precio = '$precio',
                                                                codigoBarra = '$codigoBarra', existencia = '$cantidad',
                                                                foto = '$imgProducto', idusuario = '$id_usuario'
                                                                WHERE codproducto = '$codproducto'");

                        if ($query_update) {
                        
                            if(!empty($fotoNew['name'])) {
                                move_uploaded_file($url_temp, $src);
                            }
                            echo "<script>setTimeout(function(){ window.history.go(-2); }, 1500);</script>";
                            $alert = '<div class="alert alert-success" role="alert">Producto '.$producto.' actualizado correctamente</div>'; 
                        } else {
                            $alert = '<p class="msg_error">Error al actualizar el producto '.$producto.'</p>';
                        }
					}
				}
			}


		} else if(!Puede('e','productos')) {
			header('location: lista_producto.php');
		}

		//Desactivar datos del producto
		if(!empty($_GET['desactivarCOD']) && Puede('d','productos')) {
			$titulo = (($actPROD != 'desactivar') ? 'Activar' : 'Desactivar').' producto';
			$boton = ($actPROD != 'desactivar') ? '<i class="material-icons btn-icon">business</i>Activar producto'
                                            : '<i class="material-icons btn-icon">domain_disabled</i>Desactivar producto';

			if (!empty($_POST)) {
				$codproducto =  $_POST['id'];
				$query = mysqli_query($conn, "SELECT * FROM producto WHERE codproducto = '$codproducto'");
				$result = mysqli_num_rows($query);
				
				if ($result == 0) {
					header ('location: lista_producto.php');

				} else {
					$estatus = ($actPROD == 'desactivar') ? 'inactivo' : 'activo';

					$query = mysqli_query($conn, "UPDATE producto
												SET estatus = '$estatus'
												WHERE codproducto = '$codproducto'");
					if ($query) {
						header ('location: lista_producto.php');
					} else {
						echo "<script type='text/javascript'> alert('Error al modificar producto'); </script>";
					}
				}	
				
			}

		} else if(!Puede('d','productos')) {
			header('location: lista_producto.php');
		}

        //Eliminar datos del producto
		if(!empty($_GET['eliminarCOD']) && Puede('d','productos')) {
			$titulo = 'Eliminar producto';
            $actPROD = 'eliminar permanentemente';
			$boton = '<i class="material-icons btn-icon">delete</i>Eliminar producto';

			if (!empty($_POST)) {
				$codproducto =  $_POST['id'];
				$query = mysqli_query($conn, "SELECT * FROM producto WHERE codproducto = '$codproducto'");
				$result = mysqli_num_rows($query);
				
				if ($result == 0) {
					header ('location: lista_producto.php');

				} else {

					$query = mysqli_query($conn, "DELETE FROM producto
												WHERE codproducto = '$codproducto'");
					if ($query) {
						header ('location: lista_producto.php');
					} else {
						echo "<script type='text/javascript'> alert('Error al eliminar producto'); </script>";
					}
				}	
				
			}

		} else if(!Puede('d','productos')) {
			header('location: lista_producto.php');
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
		if (!empty($_GET['desactivarCOD']) || !empty($_GET['eliminarCOD'])) {
	?>
	<section class="container h-200 light-static">
		<div class="panel panel-default text-center">
			<div class="panel-heading">
                <h2 class="display-1 light-static">¿Estás seguro de <?php echo $actPROD ?> el siguiente producto?</h2>
			</div>
            <div class="panel-body">
                <h1 class="panel-title"><span><?php echo $producto ?></span></h1>
                <hr>
                <div class="panel-body row">
                    <div class="col-sm-6">
                        <h5>Existencia en inventario: <span style="font-weight: bolder;"><?php echo $cantidad ?></span></h5>
                        <h5>Proveedor: <span style="font-weight: bolder;"><?php echo $proveedor ?></span></h5>
                        <h5>Costo: <span style="font-weight: bolder;"><?php echo $costo ?></span></h5>
                        <h5>Precio de venta: <span style="font-weight: bolder;"><?php echo $precio ?></span></h5>
                    </div>
                    <div class="col-sm-6">
                        <div class="photo">
                            <div class="prevPhoto">
                                <label for="foto"></label>
                            </div>
                            <div class="upimg">
                                <input type="file" name="foto" id="foto" value="<?php echo $location.$foto; ?>" disabled="disabled">
                            </div>
                            <div id="form_alert"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?php echo $codproducto; ?>">
                    <a href="lista_producto.php" class="btn light text-center"><i class="material-icons btn-icon">cancel</i>Cancelar</a>
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
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-sm-6">
                    <label for="producto">* Nombre del producto</label>
                    <input type="text" class="form-control text-capitalize" name="producto" id="producto" value="<?php echo $producto; ?>" required>
                </div>
                <div class="form-group col-sm-6">
                    <label for="proveedor">Proveedor</label>
                    <div class="inline">
                        <select type="select" class="form-control" name="proveedor" id="proveedor">
                        <?php foreach($proveedores_Query as $prov) { 
                            if($codproveedor == $prov['codproveedor']) { ?> 
                                <option value="<?php echo $prov['codproveedor']; ?>" selected><?php echo $prov['proveedor']; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $prov['codproveedor']; ?>"><?php echo $prov['proveedor']; ?></option>
                            <?php } ?>
                        <?php } unset($prov);?>
                        </select>
                        <?php if(Puede('c','proveedores')) { ?>
                        <a href="proveedor.php?addInside=new" class="btn dark" data-toggle="tooltip" title="Crear">
                            <span class="material-icons btn-icon">add_business</span>
                        </a>
                        <? } ?>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-sm-4">
                    <label for="clase">* Clase</label>
                    <div class="inline">
                        <select type="select" class="form-control" name="clase" id="clase" required>
                        <?php foreach($clase_Query as $class) { 
                            if($idclase == $class['idclase']) { ?>
                                <option value="<?php echo $class['idclase']?>" selected><?php echo $class['clase']?></option>
                            <?php } else { ?>
                                <option value="<?php echo $class['idclase']?>"><?php echo $class['clase']?></option>
                            <?php } ?>
                        <?php } unset($class);?>
                        </select>
                        <?php if(Puede('c','clases')) { ?>
                        <a href="claseProducto.php?addInside=new" class="btn dark" data-toggle="tooltip" title="Nueva">
                            <span class="material-icons btn-icon">view_sidebar</span>
                        </a>
                        <? } ?>
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label for="categoria">* Categoria</label>
                    <div class="inline">
                        <select type="select" class="form-control light-select" name="categoria" id="categoria" required>
                        <?php foreach($categoria_Query as $cat) { 
                            if($idcategoria == $cat['idcategoria']) { ?>
                                <option value="<?php echo $cat['idcategoria']?>" selected><?php echo $cat['categoria']?></option>
                            <?php } else { ?>
                                <option value="<?php echo $cat['idcategoria']?>"><?php echo $cat['categoria']?></option>
                            <?php } ?>
                        <?php } unset($cat);?>
                        </select>
                        <?php if(Puede('c','categorias')) { ?>
                        <a href="categoria.php?addInside=new" class="btn dark" data-toggle="tooltip" title="Añadir">
                            <span class="material-icons btn-icon">dashboard_customize</span>
                        </a>
                        <? } ?>
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label for="codigoBarra">Código de Barra</label>
                    <div class="inline">
                        <span class="glyphicon glyphicon-barcode btn-icon" style="font-size: xx-large;"></span>
                        <input type="text" class="form-control" name="codigoBarra" id="codigoBarra" value="<?php echo $codigoBarra; ?>">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-sm-3">
                    <label for="costo">Costo</label>
                    <input type="text" class="form-control" data-type="currency" placeholder="RD $1,234.00" name="costo" id="costo" value="<?php echo $costo; ?>">
                </div>
                <div class="form-group col-sm-3">
                    <label for="precio">Precio</label>
                    <input type="text" class="form-control" data-type="currency" placeholder="RD $1,234.00" name="precio" id="precio" value="<?php echo $precio; ?>">
                </div>
                <div class="form-group col-sm-2">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" class="form-control" name="cantidad" id="cantidad" value="<?php echo $cantidad; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <div class="photo">
                        <label for="foto">Foto del Producto</label>
                            <div class="prevPhoto">
                                <span class="delPhoto notBlock">X</span>
                                <label for="foto"></label>
                            </div>
                            <div class="upimg">
                            <input type="file" name="foto" id="foto" value="<?php echo $location.$foto; ?>">
                            <input type="hidden" name="checkif" id="checkif" />
                            </div>
                            <div id="form_alert"></div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group p-15 text-center">
                    <a type="button" href="javascript:history.back()" class="btn light text-center"><i class="material-icons btn-icon">cancel</i></span> Cancelar</a>
                    <button type="submit" class="btn dark"><?php echo $boton; ?></button>
                </div>
            </div>
        </form>
    </section>
	<?php
		}
	?>
	<?php include 'includes/footer.php' ?>

    <!-- Modal para mostrar imagenes -->
    <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
                    <h4 class="modal-title" id="myModalLabel"></h4>
                </div>
                <div class="modal-body" style="text-align:center">
                    <img src="" id="imagepreview">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn dark" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>