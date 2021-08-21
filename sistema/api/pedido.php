<?php
    session_start();
	if (empty($_SESSION['active'])) {

		header('location: ../../');

	} else {
		include "../conexion.php";
		include "../includes/accesos.php"; 

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

        $id_usuario = $_SESSION['idUser'];
        $response = [];

        if(!empty($_POST) && isset($_POST['adicionar'])) {
            
            $obj = json_decode($_POST['adicionar'], true);
            $estado_orden = "En espera de pago";

			$origen = 'Pedido Interno';
            $idcliente = $obj['cliente']['idcliente'];
            $nombre = $obj['cliente']['nombre'];
            $celular = $obj['cliente']['celular'];
            $porciento = $obj['cliente']['porciento'];
            $direccion = $obj['cliente']['direccion'];
            $comentario = $obj['detalle']['comentario'];
            $fecha_registro = $obj['detalle']['fecha_registro'];
            $fecha_entrega = $obj['detalle']['fecha_entrega'];
            $totalPedido = $obj['detalle']['total'];
                        
            $last_detalle = mysqli_query($conn, "SELECT id FROM detallepedido ORDER BY id DESC LIMIT 1");
            $correlativodetalle = intval(mysqli_fetch_array($last_detalle)[0]) + 1;
            
            $last_mov = mysqli_query($conn, "SELECT correlativo FROM salida_inventario ORDER BY id DESC LIMIT 1");
            $correlativomov = intval(mysqli_fetch_array($last_mov)[0]) + 1;

			$insDetalle = mysqli_query($conn, "INSERT INTO detallepedido(nombre,celular,direccion,descuento,total,fecha_registro,fecha_entrega,estatusProceso,comentario,idusuario,estatus)
                                                VALUES('$nombre','$celular','$direccion','$porciento','$totalPedido','$fecha_registro','$fecha_entrega',$estado_orden,'$comentario','$id_usuario','activo')");

			if ($insDetalle) {
				
				$sqlProductos = "INSERT INTO salida_inventario(correlativo,codproducto,codbarra,origen,descripcion,cantidad,precio,idusuario,estatus) VALUES";
				$response['updating'] = "Todo bien";
				
				foreach ($obj['productos'] as $value){
					$num = $value["Id"];
                    $cod = $value["Código"];
                    $desc = $value["Descripción"];
                    $cant = $value["Cant."];
                    $precio = substr($value["Precio"],4,-1);
                    
					$sqlProductos = $sqlProductos . "('$correlativomov','$num','$cod','$origen','$desc','$cant','$precio','$id_usuario','activo'),";
		
					$sqlupdate = "UPDATE producto SET existencia = (existencia-'$cant') WHERE `producto`.`codproducto` = '$num';";
					$updating = mysqli_query($conn,$sqlupdate);
					
					if (!($updating)) {
						$response['updating'] = "Error actualizando producto #" . $desc;
					}
				}

                $sqlProductos = substr($sqlProductos,0,-1);
				
				if ($conn->query($sqlProductos)) {
					
					$insPedido = mysqli_query($conn, "INSERT INTO pedido(idcliente,correlativodetalle,correlativomov,idusuario,estatus)
																			VALUES('$idcliente','$correlativodetalle','$correlativomov','$id_usuario','activo')");
					
					$idpedido = mysqli_fetch_array(mysqli_query($conn, "SELECT id FROM pedido ORDER BY id DESC LIMIT 1"));
					
					if ($insPedido) {
						$response['data'] = 'Pedido guardado correctamente ';
		                $response['success'] = true; 
						$response['idpedido'] = $idpedido[0];
						
					} else {
						$response['data'] = 'Error al guardar el pedido ';
		                $response['success'] = false; 
					}
				} else {
					$response['data'] = 'Error al guardar la lista de producto ';
	                $response['success'] = false; 
				}
			} else {
				$response['data'] = 'Error al guardar el detalle del pedido '; //mysqli_error($conn);
                $response['success'] = false; 
			}
        }else {
            $response['success'] = false;
            $response['data'] = "Post invalido "; 
        }

        echo json_encode($response);
    }
?>