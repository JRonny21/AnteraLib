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

        $response = [];
        $clientesArray = array();
        $productosArray = array();
        $pedidoDataArray = array();


        $id_usuario = $_SESSION['idUser'];
        $response['success'] = false;
        $response['data'] = 'Sin nada que mostrar';
        
        if(isset($_POST['fetch'])) {    
            switch ($_POST['fetch']) {
            	case 'nombreCliente' :
			$sqlClientes = "SELECT c.idcliente, c.cedula, c.nombre, c.telefono, c.direccion, c.estatus, t.tipo, t.max_descuento
	                            FROM cliente c INNER JOIN tipocliente t ON c.tipo = t.idtipo";
	                $cliente_Query = mysqli_query($conn, $sqlClientes);
	
	                foreach ($cliente_Query as $i => $dataQuery) {
	                    $clientesArray[$i]['id'] = $dataQuery['idcliente'];
	                    $clientesArray[$i]['cedula'] = $dataQuery['cedula'];
	                    $clientesArray[$i]['nombre'] = $dataQuery['nombre'];
	                    $clientesArray[$i]['telefono'] = $dataQuery['telefono'];
	                    $clientesArray[$i]['direccion'] = $dataQuery['direccion'];
	                    $clientesArray[$i]['estatus'] = $dataQuery['estatus'];
	                    $clientesArray[$i]['tipo'] = $dataQuery['tipo'];
	                    $clientesArray[$i]['descuento'] = $dataQuery['max_descuento'];
	                } unset($dataQuery);
	
					$response['success'] = true;                
					$response['data'] = $clientesArray;
		            break;
		
		case 'codproducto' :
			$sqlProducto = "SELECT p.codproducto, p.descripcion, p.codigoBarra, p.precio, p.existencia, p.foto, cla.clase, cla.color, cat.categoria
					FROM producto p
					INNER JOIN claseProducto cla ON p.idclase = cla.idclase
					INNER JOIN categoriaProducto cat ON p.idcategoria = cat.idcategoria
					WHERE p.estatus = 'activo'";
			$producto_Query = mysqli_query($conn, $sqlProducto);

		    	foreach ($producto_Query as $i => $dataQuery) {
			    $productosArray[$i]['codproducto'] = $dataQuery['codproducto'];
			    $productosArray[$i]['descripcion'] = $dataQuery['descripcion'];
			    $productosArray[$i]['codigoBarra'] = $dataQuery['codigoBarra'];
			    $productosArray[$i]['precio'] = $dataQuery['precio'];
			    $productosArray[$i]['existencia'] = $dataQuery['existencia'];
			    $productosArray[$i]['foto'] = $dataQuery['foto'];
			    $productosArray[$i]['clase'] = $dataQuery['clase'];
			    $productosArray[$i]['color'] = $dataQuery['color'];
			    $productosArray[$i]['categoria'] = $dataQuery['categoria'];
		    	} unset($dataQuery);

			$response['success'] = true;
			$response['data'] = $productosArray;
			break;
			
		case 'datosPedido' :
			$idpedido = $_POST['data'];
	
			$sqlDetalle = "SELECT p.idcliente, p.fecha, ep.estado, ep.color, dp.*, DATEDIFF(dp.fecha_entrega,current_date()) as dias, p.estatus, u.usuario
									 FROM detallepedido dp
				       INNER JOIN pedido p ON dp.id = p.correlativodetalle
				       INNER JOIN estadopedido ep ON estatusPedido = ep.id
				       INNER JOIN usuario u ON dp.idusuario = u.idusuario
				     WHERE p.id = '$idpedido'";
		    	$queryDetalle = mysqli_query($conn, $sqlDetalle);
							 
			$sqlMov = "SELECT si.*, u.usuario FROM salida_inventario si
							    INNER JOIN pedido p ON si.correlativo = p.correlativomov
							    INNER JOIN usuario u ON si.idusuario = u.idusuario
							  WHERE p.id = '$idpedido'";             
			$queryMov = mysqli_query($conn, $sqlMov);
				
			if (!$queryDetalle || !$queryMov) {
			    echo("Error cargando detalle de pedido");
			} else {
			    foreach ($queryDetalle as $data){
				$pedidoDataArray["idcliente"] = $data['idcliente'];
				$pedidoDataArray["nombre"] = $data['nombre'];
				$pedidoDataArray["telefono"] = $data['celular'];
				$pedidoDataArray["porciento"] = $data['descuento'];
				$pedidoDataArray["direccion"] = $data['direccion'];
				    
				$pedidoDataArray["estatusPedido"] = $data['estatus'];
				$pedidoDataArray["estadoOrden"] = $data['estado'];
				$pedidoDataArray["estadoColor"] = $data['color'];
				$pedidoDataArray["estadoProceso"] = $data['estatusProceso'];
				$pedidoDataArray["comentario"] = $data['comentario'];
				
				$pedidoDataArray["fechaCreado"] = $data['fecha'];
				$pedidoDataArray["fechaProcesado"] = $data['fecha_registro'];
				$pedidoDataArray["fechaEntrega"] = $data['fecha_entrega'];
				$pedidoDataArray["diasRestantes"] = $data['dias'];
				
				$pedidoDataArray["editable"] = ($pedido_estado_pedido != 'Sin procesar') ? false : true ;
				
				foreach ($queryMov as $i => $tabledata) {
				    $pedidoDataArray["dataTabla"][$i]['No.'] = $i + 1;
				    $pedidoDataArray["dataTabla"][$i]['Id'] = $tabledata['codproducto'];
				    $pedidoDataArray["dataTabla"][$i]['Codigo'] = $tabledata['codbarra'];
				    $pedidoDataArray["dataTabla"][$i]['Descripcion'] = $tabledata['descripcion'];
				    $pedidoDataArray["dataTabla"][$i]['Cant.'] = $tabledata['cantidad'];
				    $pedidoDataArray["dataTabla"][$i]['Precio'] = $tabledata['precio'];
				    $pedidoDataArray["dataTabla"][$i]['Total'] = $tabledata['cantidad'] * $tabledata['precio'];
				}
			    }
		    	}
			    
			$response['success'] = true;
			$response['data'] = $pedidoDataArray;
			break;
					
		    default:
			    $response['data'] = "Variable fetch no definida";
	        }
	     }
        echo json_encode($response);
        
    }

?>