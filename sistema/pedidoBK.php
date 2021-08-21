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

        $titulo_header = '';
        $titulo = '';
        $foto = '';
        $def_product = 'def_product.png';
        $location = 'inventario/';
        $id_usuario = $_SESSION['idUser'];

        $Nombre = '';
        $Porcentaje = '';

        $cliente_Query = mysqli_query($conn, "SELECT c.idcliente, c.cedula, c.nombre, c.telefono, c.direccion, c.estatus, t.tipo, t.max_descuento
                                            FROM cliente c INNER JOIN tipocliente t ON c.tipo = t.idtipo");

        $producto_Query = mysqli_query($conn, "SELECT p.codproducto, p.descripcion, p.codigoBarra, p.precio, p.existencia, p.foto, cla.clase, cla.color, cat.categoria
                                                    FROM producto p
                                                        INNER JOIN claseProducto cla ON p.idclase = cla.idclase
                                                        INNER JOIN categoriaProducto cat ON p.idcategoria = cat.idcategoria
                                                WHERE p.estatus = 'activo'");

        $clientesArray = array();
        foreach ($cliente_Query as $i => $dataQuery) {
            $clientesArray[$i]['id'] = $dataQuery['idcliente'];
            $clientesArray[$i]['cedula'] = $dataQuery['cedula'];
            $clientesArray[$i]['nombre'] = $dataQuery['nombre'];
            $clientesArray[$i]['telefono'] = $dataQuery['telefono'];
            $clientesArray[$i]['direrccion'] = $dataQuery['direccion'];
            $clientesArray[$i]['estatus'] = $dataQuery['estatus'];
            $clientesArray[$i]['tipo'] = $dataQuery['tipo'];
            $clientesArray[$i]['descuento'] = $dataQuery['max_descuento'];
        } unset($dataQuery);

        $productoArray = array();
        foreach ($producto_Query as $i => $dataQuery) {
            $productoArray[$i]['codproducto'] = $dataQuery['codproducto'];
            $productoArray[$i]['descripcion'] = $dataQuery['descripcion'];
            $productoArray[$i]['codigoBarra'] = $dataQuery['codigoBarra'];
            $productoArray[$i]['precio'] = $dataQuery['precio'];
            $productoArray[$i]['existencia'] = $dataQuery['existencia'];
            $productoArray[$i]['foto'] = $dataQuery['foto'];
            $productoArray[$i]['clase'] = $dataQuery['clase'];
            $productoArray[$i]['color'] = $dataQuery['color'];
            $productoArray[$i]['categoria'] = $dataQuery['categoria'];
        } unset($dataQuery);

        if (Puede('ventas','c')) {
            $titulo = 'Nuevo Pedido';
			$titulo_header = '<span class="material-icons title-icon">add_shopping_cart</span>Registro de Pedido';
			$boton = '<span class="material-icons btn-icon">save</span>Guardar';

            if(!empty($_POST)) {
                if(empty($_POST['nombreCliente'])) {
                    
                }
            }
        
        } else if (!empty($_GET)) {
            if (!(Puede('ventas','c'))) {
				header ('location: lista_pedidos.php');
			}
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include 'includes/script.php' ?>
	<title><?php echo $titulo; ?></title>

    <script type="text/javascript">
        let objClient = <?php echo json_encode($clientesArray); ?>;
        let objProduct = <?php echo json_encode($productoArray); ?>;

        function setNombre(data) {
            let len = Object.keys(objClient).length;
            let index = "nada";

            for(let [i, producto] of objClient.entries()) {
                if (producto['nombre'] == data) { 
                    index = i;
                }
            }

            if(index != "nada") {
                if(objClient[index]['estatus'] != 'activo') {
                    popUp("El cliente <strong>" + objClient[index]['nombre'] + "</strong> esta deshabilitado");
                    $(".client input").val('');
                    $("input[name='porciento']").removeAttr("max");
                    $("input[name='porciento']").removeAttr("placeholder");
                    $(".client label").removeClass("textAnimation");
                } else {
                    $("input[name='porciento']").attr("max", objClient[index]['descuento']);
                    $("input[name='porciento']").attr("placeholder", "Maximo descuento: " + objClient[index]['descuento']);
                    $(".client label").addClass("textAnimation");
                    $("input[name='celular']").val(objClient[index]['telefono']);
                }
            } else {
                $(".client input").val('');
                $("input[name='porciento']").removeAttr("max");
                $("input[name='porciento']").removeAttr("placeholder");
                $(".client label").removeClass("textAnimation");
            }            
        }

        function popUp(msg) {
            // Get the snackbar DIV
            let x = document.getElementById("snackbar");

            x.innerHTML = msg;

            // Add the "show" class to DIV
            x.className = "show";

            // After 3 seconds, remove the show class from DIV
            setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
        } 

        function resetMax(objeto){
            value = parseInt(objeto.value);
            let max = parseInt(objeto.getAttribute('max'));
            if(value > max) {
                objeto.value = max;
            }       
        }

        function loadProducto(producto) {
            let lenP = Object.keys(objProduct).length;
            let location = <?php echo json_encode($location);?>;
            let pos = "nada";

            for(let [i, nombre] of objProduct.entries()) {
                if (nombre['descripcion'] == producto) { 
                    pos = i;
                }
            }

            if(pos != "nada") {
                $("#foto").attr("value", location + objProduct[pos]['foto']);
                $(".prevPhoto").css('background', 'transparent');
                $("#img").remove();
                $(".delPhoto").removeClass('notBlock');
                $(".prevPhoto").append("<img id='img' src="+ location + objProduct[pos]['foto'] +">");

                $("input[name='cantidad'], textarea[name='detalle']").val('');
                $("textarea[name='detalle']").val('');
                $("input[name='cantidad']").attr("max", objProduct[pos]['existencia']);
                $("input[name='cantidad']").attr("placeholder", "Existencia: " + objProduct[pos]['existencia']);
                $("#addProduct label").addClass("textAnimation");
            } else {
                $("#foto").attr("value", "inventario/");
                $("#img").remove();
                $(".delPhoto").addClass('notBlock');
                $(".prevPhoto").removeAttr('style');

                $("input[name='nombreProducto'], input[name='cantidad']").val('');
                $("textarea[name='detalle']").val('');
                $("input[name='cantidad']").removeAttr("max");
                $("input[name='cantidad']").removeAttr("placeholder");
                $("#addProduct label").removeClass("textAnimation");
            }
        }
        
    </script>

</head>
<body>
	<?php include 'includes/header.php' ?>

    <section class="container mx-5 light-static">
    	<div class="header light-static">
			<h1><?php echo $titulo_header; ?></h1>
			<hr>
        </div>
        <div id="snackbar"></div>
        <div class="main-container">
            <div class="row">
                <div class="col-md-4 container_3d pb-5">
                    <div class="client card-1">
                        <h3 class="client_tittle">Datos del Cliente</h3>
                        <hr>
                        <div class="form-group">
                            <label for="nombreCliente">Nombre del Cliente</label>
                            <div class="inline">
                                <input list="nombreCliente" onchange="setNombre(this.value)" class="form-control"
                                    name="nombreCliente" placeholder="Selecione de la lista">
                                <datalist id="nombreCliente">
                                <?php foreach($cliente_Query as $name) { ?> 
                                    <option value="<?php echo $name['nombre']?>">
                                <?php } ?>
                                </datalist>
                                <?php if(Puede("clientes","c")) { ?>
                                <a href="cliente.php?addInside=new" class="btn dark" data-toggle="tooltip" title="Crear">
                                    <span class="material-icons btn-icon">person_add</span>
                                </a>
                                <? } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="celular">Celular:</label>
                            <input type="text" class="form-control" name="celular" data-type="celular">
                        </div>
                        <div class="form-group">
                            <label for="porciento">% Descuento:</label>
                            <input type="number" onkeyup="resetMax(this)" class="form-control" name="porciento" min="0">
                        </div>
                        <div class="form-group">
                            <label for="DirEntrega">Direccion de Entrega</label>
                            <input type="text" class="form-control" name="DireccionEntrega" id="DirEntrega" placeholder="Entregar en MVGraphics (cambiar en caso contrario)">
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="panel panel-default shadow pb-5">
                        <div class="panel-heading inline">
                            <button class="btn btn-default" data-toggle="collapse" data-target="#listaOrden">Lista</button>
                            <h2 class="text-center">Ordenes del pedido</h2>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive" id="listaOrden">
                                <table class="table table-striped table-bordered dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Cant.</th>
                                        <th>U/M</th>
                                        <th>Precio</th>
                                        <th>Total</th>
                                    </tr>
                                    <tr>
                                        <td><a href="#">001</a></td>
                                        <td class="text-nowrap item-block">
                                            <a href="#">BOT-WHT-1</a>
                                            <a href="#">SUBL-BOT-A</a>
                                        </td>
                                        <td class="item-block">
                                            <span>- Botella de metal blanca (tamaño grande)</span>
                                            <span>- Servicio de sublimado sencillo</span>
                                        </td>
                                        <td>10</td>
                                        <td>Botellas</td>
                                        <td class="text-nowrap">RD $125.00</td>
                                        <td class="text-nowrap"><b>RD $1,250.00<b></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div style="display:flex;">
                                <div class="col-xs-6 col-sm-9 col-md-9 text-right">
                                    <span class="row text-nowrap">Subtotal</span>
                                    <span class="row text-nowrap"><i>Descuento</i></span>
                                    <span class="row text-nowrap"><b>Total</b></span>
                                </div>
                                <div class="col-xs-6 col-sm-3 col-md-3 text-right">
                                    <span class="row text-nowrap">RD $1,250.00</span>
                                    <span class="row text-nowrap"><i>RD $125.00</i></span>
                                    <span class="row text-nowrap"><b>RD $1,125.00</b></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-warning shadow my-5 py-5">
                        <div class="panel-heading inline">
                            <span class="panel-title">Adicionar a la lista de pedido</span>
                        </div>
                        <div id="addProduct" class="panel-body">
                            <div class="optionContainer row">
                                <div class="option col-xs-6 col-sm-4">
                                    <span>Adicionar Servicio</span>  
                                    <a href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0, 0, 400,391.9629057187017"><g id="svgg"><path id="path0" d="M240.558 1.792 C 239.212 3.138,239.274 2.879,236.796 17.505 C 234.619 30.364,234.493 30.670,231.056 31.507 C 229.617 31.857,226.325 32.724,223.742 33.433 C 216.252 35.488,216.621 35.689,207.190 24.420 C 197.562 12.916,196.865 12.387,193.622 14.113 C 187.985 17.113,172.778 26.072,171.761 26.993 C 170.033 28.556,170.307 29.899,174.961 42.658 C 180.386 57.530,180.514 55.990,173.250 63.226 C 166.052 70.398,167.647 70.263,153.193 64.915 C 136.715 58.817,138.976 57.963,129.937 73.705 C 121.691 88.065,121.252 86.007,134.985 97.372 C 145.563 106.127,145.887 106.645,144.150 112.023 C 143.458 114.166,142.583 117.488,142.205 119.405 C 141.225 124.376,140.990 124.492,126.739 127.032 C 109.749 130.061,111.307 128.403,111.170 143.600 C 111.031 158.986,111.072 159.566,112.386 160.852 C 113.112 161.562,117.537 162.609,126.430 164.175 C 140.138 166.589,141.259 167.028,141.889 170.234 C 142.099 171.304,142.984 174.769,143.857 177.934 C 145.866 185.224,146.406 184.304,134.003 194.738 C 121.977 204.855,122.221 204.314,126.433 211.541 C 129.802 217.324,129.923 217.372,142.504 217.901 C 167.205 218.939,183.140 223.235,207.813 235.507 C 221.430 242.280,223.225 242.731,249.768 246.060 C 303.547 252.804,312.137 255.214,324.042 266.899 L 328.146 270.926 332.388 268.587 C 341.731 263.436,341.614 264.075,336.013 248.724 C 330.720 234.219,330.641 235.094,337.891 227.844 C 344.322 221.413,344.576 221.312,349.436 223.252 C 372.797 232.572,372.528 232.568,377.467 223.684 C 379.415 220.179,382.361 215.046,384.013 212.279 C 388.840 204.194,389.297 205.035,372.333 190.802 C 364.985 184.638,365.079 184.842,366.848 178.890 C 367.642 176.219,368.639 172.568,369.064 170.775 C 369.493 168.963,370.303 167.267,370.888 166.954 C 371.768 166.482,395.416 161.978,397.009 161.978 C 399.627 161.978,400.000 159.952,400.000 145.713 C 400.000 128.275,401.615 130.238,384.798 127.244 C 370.686 124.732,369.088 124.116,369.088 121.186 C 369.088 120.588,368.265 117.239,367.260 113.745 C 365.008 105.922,364.392 107.078,376.029 97.301 C 389.079 86.337,388.817 86.943,383.999 78.911 C 382.339 76.144,379.542 71.311,377.783 68.172 C 372.582 58.886,373.511 59.037,357.782 64.915 C 343.184 70.370,344.655 70.500,337.397 63.118 C 330.245 55.843,330.447 57.837,335.476 44.197 C 341.903 26.768,342.275 28.435,330.421 21.556 C 312.535 11.177,314.993 11.085,305.463 22.493 C 295.396 34.545,295.316 34.622,292.920 34.620 C 289.370 34.617,279.250 31.597,277.728 30.086 C 276.370 28.738,273.730 17.405,272.342 6.967 C 272.105 5.188,271.361 3.032,270.687 2.175 L 269.462 0.618 255.597 0.618 C 242.803 0.618,241.640 0.709,240.558 1.792 M268.616 78.828 C 314.153 88.546,337.088 137.388,315.209 178.053 C 286.354 231.685,206.707 222.856,190.101 164.185 C 176.271 115.324,219.487 68.343,268.616 78.828 M11.437 220.006 C -0.476 220.769,0.618 214.052,0.618 286.442 L 0.618 346.649 2.032 348.749 C 4.350 352.193,6.226 352.493,24.140 352.277 C 41.765 352.064,42.516 351.895,44.256 347.731 C 45.616 344.474,45.558 227.051,44.194 224.178 C 42.280 220.144,31.237 218.738,11.437 220.006 M123.957 227.301 C 98.761 229.335,64.518 236.551,59.324 240.921 C 56.210 243.541,56.178 244.041,56.380 287.172 C 56.612 336.600,55.429 332.389,69.683 334.529 C 82.547 336.461,85.131 337.350,104.173 346.397 C 160.182 373.008,171.300 378.136,188.924 385.493 C 209.349 394.020,210.476 394.011,267.697 384.855 C 275.348 383.631,288.300 381.676,296.479 380.510 C 321.599 376.931,323.209 376.271,348.429 359.211 C 375.300 341.033,384.846 333.180,388.236 326.465 C 391.306 320.383,390.412 316.091,385.531 313.479 C 383.420 312.349,383.486 311.972,386.368 308.698 C 398.296 295.147,379.370 281.804,358.269 288.888 C 351.111 291.291,344.239 295.434,336.894 301.775 C 323.166 313.626,317.538 316.095,298.376 318.670 C 262.021 323.555,249.484 323.207,230.712 316.793 C 222.956 314.143,207.110 307.314,207.110 306.621 C 207.110 304.564,227.885 304.906,265.905 307.591 C 304.412 310.311,303.891 310.332,310.521 305.795 C 324.450 296.263,325.532 278.134,312.686 269.555 C 304.198 263.886,277.791 257.761,252.799 255.662 C 224.494 253.285,218.916 252.149,208.521 246.642 C 178.921 230.962,152.752 224.976,123.957 227.301 " stroke="none"></path></g></svg>
                                    </a>
                                </div>
                                <div class="option col-xs-6 col-sm-4">
                                    <span>Adicionar Combo</span> 
                                    <a href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0, 0, 400,400"><g id="svgg"><path id="path0" d="M182.038 25.785 C 181.860 26.343,180.756 32.200,179.584 38.800 C 178.413 45.400,177.397 50.841,177.327 50.891 C 177.257 50.941,166.616 54.456,153.679 58.702 L 130.158 66.421 124.879 61.436 C 121.976 58.694,117.910 54.729,115.845 52.625 C 111.670 48.372,111.348 48.269,107.881 50.062 C 105.128 51.485,86.117 64.963,83.491 67.353 L 81.782 68.908 84.004 74.654 C 85.227 77.814,87.739 83.192,89.586 86.605 L 92.944 92.810 88.973 98.205 C 86.789 101.172,84.438 104.680,83.750 106.000 C 83.061 107.320,79.911 111.640,76.750 115.600 C 73.589 119.560,69.405 125.037,67.452 127.770 C 64.332 132.139,63.642 132.693,61.751 132.352 C 57.182 131.526,38.043 128.890,37.871 129.063 C 37.554 129.379,26.400 161.898,26.400 162.504 C 26.400 162.822,31.620 165.829,38.000 169.186 L 49.600 175.291 49.600 200.000 L 49.600 224.709 38.000 230.814 C 31.620 234.171,26.400 237.178,26.400 237.496 C 26.400 238.102,37.554 270.621,37.871 270.937 C 38.043 271.110,57.182 268.474,61.751 267.648 C 63.642 267.307,64.332 267.861,67.452 272.230 C 69.405 274.963,73.589 280.440,76.750 284.400 C 79.911 288.360,83.061 292.680,83.750 294.000 C 84.438 295.320,86.790 298.829,88.975 301.798 L 92.948 307.195 89.242 314.049 C 87.204 317.818,84.701 323.204,83.680 326.017 L 81.824 331.131 83.512 332.667 C 86.124 335.043,105.142 348.522,107.881 349.938 C 111.348 351.731,111.670 351.628,115.845 347.375 C 117.910 345.271,121.976 341.306,124.879 338.564 L 130.158 333.579 153.679 341.298 C 166.616 345.544,177.260 349.059,177.333 349.109 C 177.407 349.159,178.506 354.960,179.776 362.000 L 182.086 374.800 200.000 374.800 L 217.914 374.800 220.224 362.000 C 221.494 354.960,222.593 349.164,222.667 349.120 C 222.740 349.076,233.373 345.582,246.296 341.354 L 269.792 333.668 276.496 339.804 C 280.183 343.178,283.200 346.205,283.200 346.529 C 283.200 349.025,288.967 351.417,291.767 350.082 C 294.626 348.718,313.233 335.599,316.509 332.637 L 318.218 331.092 315.996 325.346 C 314.773 322.186,312.267 316.818,310.426 313.418 L 307.080 307.235 311.940 300.546 C 314.613 296.867,316.810 293.529,316.821 293.128 C 316.833 292.728,319.027 289.700,321.696 286.400 C 324.366 283.100,328.707 277.445,331.344 273.834 C 335.610 267.990,336.372 267.309,338.269 267.640 C 343.227 268.508,361.974 271.093,362.135 270.932 C 362.452 270.615,373.600 238.091,373.600 237.483 C 373.600 237.157,368.380 234.144,362.000 230.786 L 350.400 224.682 350.400 200.000 L 350.400 175.318 362.000 169.214 C 368.380 165.856,373.600 162.843,373.600 162.517 C 373.600 161.909,362.452 129.385,362.135 129.068 C 361.973 128.906,343.202 131.496,338.249 132.364 C 336.363 132.694,335.661 132.129,332.548 127.770 C 330.595 125.037,326.411 119.560,323.250 115.600 C 320.089 111.640,316.939 107.320,316.250 106.000 C 315.562 104.680,313.211 101.172,311.027 98.205 L 307.056 92.810 310.414 86.605 C 312.261 83.192,314.773 77.814,315.996 74.654 L 318.218 68.908 316.509 67.364 C 313.815 64.930,296.793 52.768,292.665 50.327 L 288.930 48.118 279.465 57.260 C 274.259 62.288,269.640 66.275,269.200 66.119 C 268.760 65.964,258.140 62.490,245.600 58.399 C 233.060 54.309,222.740 50.925,222.667 50.881 C 222.593 50.836,221.494 45.040,220.224 38.000 L 217.915 25.200 200.138 24.985 C 186.326 24.817,182.289 24.996,182.038 25.785 M222.000 70.759 C 295.386 83.821,343.442 154.163,328.329 226.400 C 311.044 309.023,222.087 354.114,145.600 319.023 C 142.300 317.510,138.520 315.817,137.200 315.263 C 125.540 310.365,100.335 287.530,90.959 273.369 C 62.042 229.698,62.042 170.340,90.959 126.623 C 96.646 118.024,117.098 97.302,125.229 91.898 C 153.494 73.115,190.196 65.098,222.000 70.759 M180.400 83.206 C 136.602 89.309,95.153 126.790,84.858 169.600 C 66.389 246.403,122.431 318.549,200.432 318.385 C 297.404 318.182,353.096 207.441,295.372 129.600 C 268.324 93.125,227.484 76.645,180.400 83.206 M206.962 103.368 C 208.193 103.901,212.440 106.372,216.400 108.859 C 220.360 111.346,226.128 114.630,229.218 116.157 C 232.307 117.684,238.373 121.063,242.696 123.667 C 247.020 126.270,255.705 131.460,261.998 135.200 C 274.755 142.783,276.108 143.827,279.104 148.400 L 281.200 151.600 281.200 200.000 L 281.200 248.400 279.104 251.600 C 276.108 256.173,274.755 257.217,261.998 264.800 C 255.705 268.540,247.020 273.730,242.696 276.333 C 238.373 278.937,232.307 282.312,229.218 283.834 C 226.128 285.356,221.161 288.175,218.181 290.097 C 203.100 299.824,197.853 300.192,185.600 292.381 C 176.970 286.879,172.409 284.238,170.486 283.627 C 169.482 283.309,164.282 280.413,158.930 277.192 C 153.579 273.971,144.160 268.379,138.000 264.765 C 125.560 257.467,123.944 256.222,120.904 251.600 L 118.800 248.400 118.800 200.000 L 118.800 151.600 120.904 148.400 C 123.944 143.778,125.560 142.533,138.000 135.235 C 144.160 131.621,153.579 126.029,158.930 122.808 C 164.282 119.587,169.482 116.691,170.486 116.373 C 172.549 115.718,178.771 112.114,183.694 108.722 C 192.545 102.624,200.835 100.717,206.962 103.368 M191.600 118.073 C 188.520 119.649,185.460 121.413,184.800 121.993 C 184.140 122.573,176.580 127.172,168.000 132.215 C 144.868 145.809,138.761 149.626,139.155 150.243 C 139.681 151.068,149.869 157.005,150.800 157.029 C 151.240 157.041,154.036 155.554,157.014 153.725 C 159.992 151.896,162.707 150.400,163.048 150.400 C 163.388 150.400,167.612 148.076,172.433 145.235 C 180.435 140.520,181.339 140.169,182.800 141.209 C 185.467 143.109,196.686 149.600,197.300 149.600 C 198.595 149.600,215.983 160.181,215.629 160.753 C 215.418 161.095,208.760 165.247,200.833 169.980 C 192.906 174.714,186.583 178.849,186.781 179.169 C 187.600 180.494,197.579 184.800,199.832 184.800 C 203.200 184.800,208.208 182.516,216.951 176.993 C 221.048 174.405,229.800 169.118,236.400 165.244 C 254.748 154.474,260.910 150.642,260.910 150.000 C 260.910 149.358,254.748 145.526,236.400 134.756 C 229.800 130.882,221.048 125.595,216.951 123.007 C 203.065 114.235,200.194 113.676,191.600 118.073 M171.720 159.729 L 163.039 165.005 168.477 168.159 C 174.789 171.821,174.115 171.950,184.525 165.095 L 191.346 160.603 186.441 157.501 C 180.434 153.704,182.032 153.462,171.720 159.729 M131.200 202.286 C 131.200 249.799,130.769 246.828,138.174 250.365 C 141.014 251.721,146.096 254.593,149.469 256.746 C 152.841 258.899,161.900 264.325,169.600 268.805 C 177.300 273.284,184.140 277.395,184.800 277.939 C 185.460 278.484,187.710 279.905,189.800 281.098 L 193.600 283.267 193.600 240.014 L 193.600 196.760 191.800 196.069 C 190.810 195.689,188.074 194.078,185.720 192.489 C 183.365 190.900,181.295 189.600,181.120 189.600 C 180.944 189.600,180.800 196.800,180.800 205.600 C 180.800 214.400,180.641 221.600,180.448 221.600 C 180.254 221.600,176.285 219.602,171.628 217.159 C 163.996 213.156,158.654 210.037,147.000 202.779 L 143.200 200.412 143.195 184.006 L 143.190 167.600 140.995 166.351 C 139.788 165.665,137.090 164.101,135.000 162.875 L 131.200 160.648 131.200 202.286 M259.477 166.278 C 254.569 169.205,247.017 173.730,242.694 176.333 C 238.372 178.937,232.307 182.312,229.218 183.834 C 226.128 185.356,221.170 188.169,218.201 190.084 C 215.231 191.999,211.361 194.301,209.601 195.200 L 206.400 196.833 206.400 240.092 L 206.400 283.352 210.200 281.150 C 212.290 279.939,215.440 277.983,217.200 276.804 C 218.960 275.625,226.160 271.316,233.200 267.229 C 240.240 263.141,248.063 258.435,250.584 256.770 C 253.105 255.105,257.853 252.398,261.135 250.754 C 269.379 246.625,268.854 250.028,268.610 202.278 L 268.400 160.957 259.477 166.278 M155.200 184.276 C 155.200 193.670,154.859 193.078,163.000 197.820 L 168.000 200.732 168.000 191.635 L 168.000 182.538 162.330 179.269 C 154.893 174.982,155.200 174.766,155.200 184.276 " stroke="none"></path></g></svg>
                                    </a>
                                </div>
                                <div class="option col-xs-6 col-sm-4">
                                    <span>Adicionar Producto</span> 
                                    <a href="#"> 
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0, 0, 400,400"><g id="svgg"><path id="path0" d="M171.690 38.286 C 166.147 41.298,161.915 50.523,163.192 56.808 C 163.741 59.509,164.310 62.773,164.456 64.063 C 164.602 65.352,150.401 88.730,132.898 116.016 L 101.073 165.625 64.965 165.625 C 23.218 165.625,20.599 166.278,14.365 178.241 C 6.056 194.184,16.585 210.918,34.935 210.932 C 42.178 210.937,42.483 211.110,41.580 214.708 C 39.889 221.445,58.878 330.534,63.556 340.958 C 67.909 350.656,75.388 358.391,83.821 361.914 C 91.467 365.109,310.143 365.089,317.793 361.893 C 324.864 358.939,333.204 350.280,337.472 341.464 C 341.332 333.491,359.348 232.189,359.365 218.359 L 359.375 210.938 366.016 210.932 C 383.402 210.916,393.712 193.738,385.635 178.241 C 379.401 166.278,376.782 165.625,335.035 165.625 L 298.927 165.625 267.102 116.016 C 249.599 88.730,235.398 65.352,235.544 64.063 C 235.690 62.773,236.259 59.509,236.808 56.808 C 240.158 40.317,216.645 28.427,204.567 40.505 L 200.000 45.072 195.433 40.505 C 190.204 35.276,179.132 34.242,171.690 38.286 M202.657 66.110 C 204.655 69.384,211.356 73.436,214.801 73.453 C 217.238 73.464,224.708 84.000,247.203 119.156 L 276.437 164.844 238.219 165.262 C 217.198 165.492,182.802 165.492,161.781 165.262 L 123.563 164.844 152.797 119.156 C 175.292 84.000,182.762 73.464,185.199 73.453 C 188.644 73.436,195.345 69.384,197.343 66.110 C 198.030 64.984,199.226 64.063,200.000 64.063 C 200.774 64.063,201.970 64.984,202.657 66.110 M122.731 233.822 L 126.563 237.956 126.563 276.911 L 126.563 315.865 121.995 320.433 C 117.040 325.388,114.158 325.992,108.882 323.182 C 101.835 319.429,101.664 318.342,101.612 276.882 L 101.563 237.358 105.398 233.523 C 110.620 228.301,117.728 228.423,122.731 233.822 M177.205 234.495 L 181.250 239.302 181.227 276.292 C 181.202 316.477,180.666 319.457,172.788 323.234 C 168.933 325.083,167.617 325.038,163.150 322.908 C 155.246 319.139,154.398 313.955,154.971 272.909 L 155.469 237.224 159.853 233.456 C 166.003 228.169,172.194 228.540,177.205 234.495 M231.368 233.717 C 236.768 239.460,238.128 309.863,232.999 318.153 C 229.418 323.941,223.355 325.880,217.548 323.096 C 209.990 319.472,209.423 316.216,209.398 276.292 L 209.375 239.302 213.420 234.495 C 218.685 228.238,225.923 227.924,231.368 233.717 M285.102 233.398 C 291.326 239.622,293.371 309.529,287.584 318.248 C 282.526 325.870,274.941 326.744,268.630 320.433 L 264.063 315.865 264.063 278.168 C 264.063 233.010,264.828 230.155,277.024 229.811 C 279.663 229.736,282.861 231.156,285.102 233.398 " stroke="none"></path></g></svg>
                                    </a>
                                </div>
                            </div>



                            <div class="row pb-5" id="addArticle">
                                <div class="photo">
                                    <div class="prevPhoto">
                                        <label for="foto" style="cursor:auto"></label>
                                    </div>
                                    <div class="upimg">
                                        <input type="file" name="foto" id="foto" value="inventario/" disabled="disabled">
                                    </div>
                                    <div id="form_alert"></div>
                                    </div>
                                </div>
                            <div class="row" id="addService">
                                <div class="detail">
                                    <div class="col-sm-8 form-group">
                                        <label for="nombreProducto">Nombre de Producto:</label>
                                        <div class="inline">
                                            <input list="nombreProducto" class="form-control" onchange="loadProducto(this.value)"
                                                name="nombreProducto" placeholder="Selecione de la lista">
                                            <datalist id="nombreProducto">
                                            <?php foreach($producto_Query as $producto) { ?> 
                                                <option value="<?php echo $producto['descripcion']?>">
                                                    <?php echo $producto['clase']?> &#8594; Existencia: <?php echo $producto['existencia']?>
                                                </option>
                                            <?php } ?>
                                            </datalist>
                                            <?php if(Puede("productos","c")) { ?>
                                            <a href="producto.php" class="btn dark" data-toggle="tooltip" title="Nuevo">
                                                <span class="material-icons btn-icon">library_add</span>
                                            </a>
                                            <? } ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 form-group">
                                        <label for="cantidad">Cantidad:</label>
                                        <input type="number" onkeyup="resetMax(this)" class="form-control" name="cantidad" id="cantidad" min="1">
                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <label for="detalle">Especificaciones:</label>
                                        <textarea class="form-control" name="detalle" id="detalle" row="2"></textarea>
                                    </div>
                                </div>
                                <div class="row option">
                                </div>
                            </div>



                        </div>
                    </div>
                </div>
            </div>


            <form action="#" method="post">
                
                <hr>
                <div class="panel panel-warning">
                
                    <div class="panel-footer inline" style="text-align:right; display:block;">
                        <a href="#" class="btn light"><span class="material-icons btn-icon">playlist_add</span></a>
                        <a href="#" class="btn light"><span class="material-icons btn-icon">delete</span></a>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <th><input type="checkbox" name="all" id="all"></th>
                                    <th>Codigo</th>
                                    <th>Producto</th>
                                    <th>Detalle</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>Total</th>
                                </tr>
                                <tr id="filaRegistro_0">
                                    <td><input type="checkbox" name="check_0" id="check_0"></td>
                                    <td>001</td>
                                    <td>Tazas Termicas Personalizables</td>
                                    <td>Imagen de novios con corazones rojos</td>
                                    <td>12</td>
                                    <td>RD $350.00</td>
                                    <td>RD $4,200.00</td>
                                </tr>
                                <tr id="filaRegistro_0">
                                    <td><input type="checkbox" name="check_1" id="check_1"></td>
                                    <td>002</td>
                                    <td>1</td>
                                    <td>Placas Conmemorativas de Piedra Volcanica</td>
                                    <td>Aniversario de bodas</td>
                                    <td>RD $750.00</td>
                                    <td>RD $750.00</td>
                                </tr>
                                <tr id="filaRegistro_0">
                                    <td><input type="checkbox" name="check_2" id="check_2"></td>
                                    <td>003</td>
                                    <td>T-Shirt Blancos Small</td>
                                    <td>Diseño de logo para iglesia</td>
                                    <td>200</td>
                                    <td>RD $250.00</td>
                                    <td>RD $50,000.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="panel-footer">
                        Cantidad total de Articulos
                        <a href="#" class="btn light"><span class="material-icons btn-icon">save</span>Guardar</a>
                        <a href="#" class="btn light"><span class="material-icons btn-icon">save</span>Facturar</a>
                    </div
                </div>
            </form>
        </div>

    </section>
    <?php include 'includes/footer.php' ?>
</body>
</html>