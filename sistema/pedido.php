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
        $id_usuario = $_SESSION['idUser'];
        $location = "inventario/";
        
        $cliente_id = '';
        $cliente_nombre = '';
        $cliente_telefono = '';
        $cliente_porciento = '';
        $cliente_direccion = '';
        
        $pedido_id = '';
        $pedido_tabla = array();
        $pedido_estatus = '';
        $pedido_estado_pedido = '';
        $pedido_estado_color = '';
        $pedido_estado_proceso = '';
        $pedido_comentario = '';
        
        $fecha_creado = '';
        $fecha_procesado = '';
        $fecha_entrega = '';
        $dias_restantes = '';
        
        $bloqueado = '';

        if (empty($_GET)  && Puede('ventas','c')) {
            $titulo = 'Nuevo Pedido';
			$titulo_header = '<span class="material-icons title-icon">add_shopping_cart</span>Registro de Pedido';
		    
            
        } else if (!empty($_GET)) {
            if (!(Puede('ventas','c'))) {
				header ('location: lista_pedidos.php');
			}
			
			//Cargar Datos
			$idpedido = $_GET['verPedido'];
			
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
                    $cliente_id = $data['idcliente'];
                    $cliente_nombre = $data['nombre'];
                    $cliente_telefono = $data['celular'];
                    $cliente_porciento = $data['descuento'];
                    $cliente_direccion = $data['direccion'];
                    
                    $pedido_id = $data['id'];
                    
                    foreach ($queryMov as $i => $tabledata) {
                    	$pedido_tabla[$i]['No.'] = $i + 1;
                        $pedido_tabla[$i]['Id'] = $tabledata['codproducto'];
                        $pedido_tabla[$i]['Codigo'] = $tabledata['codbarra'];
                        $pedido_tabla[$i]['Descripcion'] = $tabledata['descripcion'];
                        $pedido_tabla[$i]['Cant.'] = $tabledata['cantidad'];
                        $pedido_tabla[$i]['Precio'] = $tabledata['precio'];
                    }
                        
                    $pedido_estatus = $data['estatus'];
                    $pedido_estado_pedido = $data['estado'];
                    $pedido_estado_color = $data['color'];
                    $pedido_estado_proceso = $data['estatusProceso'];
                    $pedido_comentario = $data['comentario'];
                    
                    $fecha_creado = $data['fecha'];
                    $fecha_procesado = $data['fecha_registro'];
                    $fecha_entrega = $data['fecha_entrega'];
                    $dias_restantes = $data['dias'];
                    
                    $bloqueado = ($pedido_estado_pedido != 'Sin procesar') ? 'disabled' : '' ;
                }
            }
        }
        
        //Modificar datos del pedido
        if(!empty($_GET['verPedido']) && (Puede('ventas','v'))) {
        	$titulo = 'Detalle del pedido';
			$titulo_header = '<span class="material-icons title-icon">add_shopping_cart</span>Ver detalles del pedido';
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
        let objTableData = <?php echo json_encode($pedido_tabla); ?>;
        let objProductos = [];
	let objPermisos = [];
        const moneda = "RD $";
        
        var cliente_id = '';
        var cliente_nombre = '';
        var cliente_telefono = '';
        var cliente_porciento = '';
        var cliente_direccion = '';
        
        let pedido_id = '';
        let pedido_tabla = [];
        let pedido_estatus = '';
        let pedido_estado_proceso = '';
        let pedido_estatus_color = '';
        let pedido_estado_orden = '';
        
        var fecha_creado = '';
        var fecha_procesado = '';
        var fecha_entrega = '';
        var fecha_iniciado = '';
        var fecha_finalizado = '';
        var fecha_entregado = '';     

        var posCliente = 0;
        var total = 0;

        function loadCarrusel(estado) {
        	
	    const contenedor = document.getElementById("opcionesBtn");
	    contenedor.innerHTML = '';
	    estado = (estado == "") ? "Nuevo" : estado;
        
	    const carrusel = [
		['Nuevo', <?php echo Puede('ventas','c'); ?>, '<button type="submit"  class="btn dark col-xs-12 my-5" form="pedidoForm" ><span class="material-icons btn-icon">save</span>Guardar</button>'],
		['Nuevo', <?php echo Puede('ventas','c'); ?>, '<button type="button" id="btnclean" onclick="confirm_service_module(this)" class="btn btn-secondary col-xs-12 my-5"><span class="material-icons btn-icon">clear</span>Limpiar</button>'],
		['Sin procesar', <?php echo Puede('ventas','c'); ?>, '<button type="button"  id="btnprocess" onclick="confirm_service_module(this)" class="btn dark col-xs-12 my-5"><span class="material-icons btn-icon">save</span>Procesar</button>'],
		['Sin procesar', <?php echo Puede('ventas','c'); ?>, '<button type="button" id="btnclean" onclick="confirm_service_module(this)" class="btn btn-secondary col-xs-12 my-5"><span class="material-icons btn-icon">clear</span>Limpiar</button>'],
		['Sin procesar', <?php echo Puede('ventas','d'); ?>, '<button type="button" id="btnnull" onclick="confirm_service_module(this)" class="btn btn-warning  col-xs-12 my-5"><span class="material-icons btn-icon">delete</span>Anular Pedido</button>'],
		['Procesado', <?php echo Puede('ventas','e'); ?>, '<button type="button" id="btnedit" onclick="confirm_service_module(this)" class="btn btn-danger  col-xs-12 my-5"><span class="material-icons btn-icon">edit</span>Quitar procesamiento</button>'],
		['Procesado', 1, '<button type="button"  id="btnprint" onclick="confirm_service_module(this)" class="btn btn-info  col-xs-12 my-5"><span class="material-icons btn-icon">print</span>Imprimir</button>'],
		['Procesado', <?php echo Puede('ventas','d'); ?>, '<button type="button" id="btnnull" onclick="confirm_service_module(this)" class="btn btn-warning  col-xs-12 my-5"><span class="material-icons btn-icon">delete</span>Anular Pedido</button>'],
		['Procesado', <?php echo Puede('cobros','v'); ?>, '<button type="button" id="btncash" onclick="confirm_service_module(this)" class="btn btn-success  col-xs-12 my-5"><span class="material-icons btn-icon">attach_money</span>Avance Pedido</button>'],
		['Procesado', <?php echo Puede('cobros','c'); ?>, '<button type="button" id="btnpayment" onclick="confirm_service_module(this)" class="btn btn-info  col-xs-12 my-5"><span class="material-icons btn-icon">attach_money</span>Ver Pagos</button>'],
	    ];
			
	    for (i in carrusel) {
		if (carrusel[i][0] == estado && carrusel[i][1]){
			contenedor.innerHTML += carrusel[i][2];
		}
	    }
        }
        
        function confirm_service_module(target) {
        	switch(target.id) {
        	    case "btnprocess": /* Procesar pedido guardado */
                    if (confirm("¿Esta seguro que desea procesar el pedido? Una vez procesado no podra ser modificado")) {
					    updateStatus(1);    
		        	}
		            break;        
		        case "btnclean": /* Limpiar campos pedido sin procesar */
	                if (confirm("¿Esta seguro que desea borrar todo lo registrado?")) {
					    clearAll();    
		        	}
		            break;
		        case "btnedit": //Quitar procesamiento
	                if (confirm("¿Esta seguro que desea quitar el procesamiento al pedido actual?")) {
					    document.querySelector("div.panel-body").appendChild(tableConstructor(objTableData));
					
		        	}
		            break;
				case "btnprint": //Imprimir pedido procesado
	                if (confirm("¿Desea imprimir este pedido?")) {
					    addItem_kk();
		        	}
		            break;
				case "btnnull": // Anular pedido procesado
	                if (confirm("¿Esta seguro que desea anular este pedido?, esta operación es irreversible.")) {
					    loadClientes();
		        	}
		            break;
				case "btncash": // Aplicar pago
	                if (confirm("¿Desea aplicar un avance del pendiente a este pedido?")) {
					    loadPedido(3);
		        	}
		            break;
		        case "btnpayment": // Aplicar pago
	                if (confirm("¿Desea aplicar un avance del pendiente a este pedido?")) {
					    
		        	}
		            break;
		        default:
		            
        	}
        }
        
        function updateStatus(nueva) {
        	
        }
        
        function loadTabla() {
        	if (Object.keys(objTableData).length > 0) {
	        	for(data of objTableData) {
	            	insertRow(data);
	            }
        	}    
        }
        
        // Métodos Async y Await para capturar php resp
        function fetchData(target, datos) {
	  return new Promise((resolve, reject) => {
	    $.ajax({
	      url: 'api/fetch_array.php',
	      type: 'POST',
		dataType: 'json',
	      data: { fetch: target, data: datos },
	
	      success: function (resp) {
		resolve(resp)
	      },
	      error: function (error) {
		reject(error)
	      },
	    })
	  })
	}
		
	function loadClientes(alerta) {
	    fetchData('nombreCliente', '')
		.then( (resp) => {
		    if(!resp.success) {
			    alert("Error: " + resp.data);
		    } else {
			let options = '';
			for(let cliente of resp.data) {
				options = options + '<option value="' + cliente['nombre'] + '">' +
										'Tipo: ' + cliente['tipo'] + '</option>';
			}
		
			document.querySelector("#nombreCliente").innerHTML = options;
			if (alerta) alert("Datos actualizados correctamente!");
		    }
		}).catch((error) => {
			alert(error);
		})
        }
        
        
        function loadProductos(alerta) {
	    fetchData('codproducto', '')
		.then( (resp) => {
		    if(!resp.success) {
			    alert("Error: " + resp.data);
		    } else {
			    objProductos = resp.data;
			    let options = '';
			    for(let producto of resp.data) {
				    options = options + '<option value="' + producto['codigoBarra'] + '">' +
								    producto['clase'] + ' -> ' + producto['descripcion'] + '</option>';
			    }
		    
			    document.querySelector("#codproducto").innerHTML = options;
			    if (alerta) alert("Datos actualizados correctamente!");
		    }
		}).catch((error) => {
		    alert(error);
		})
        }
        
        function loadPedido(idPedido) {
	    fetchData('datosPedido', idPedido)
		.then( (resp) => {
		    if(!resp.success) {
			    alert("Error: " + resp.data);
		    } else {
			    objTableData = resp.data["dataTabla"];
			    alert("Nombre: " + resp.data["nombre"] +
					    " Desc Prod: " + resp.data["dataTabla"][0]['Descripcion']);
		    }
		}).catch((error) => {
		    alert(error);
		})
        }
        
        function tableConstructor(datos) {
        	let headers = Object.keys(datos[0]);
	        const editable = (<?php echo json_encode($bloqueado); ?> == "") ? true : false;
	        
			let container = document.createElement('div');
			let tbl = document.createElement('table');
			let thd = tbl.createTHead();
			let tbdy = document.createElement('tbody');
			
			container.classList.add("table-responsive");
			tbl.classList.add("table", "table-striped", "table-bordered", "dark");
			tbl.setAttribute("id", "listaPedido2");
			thd.style.fontWeight = "700";
			
			/* CREATE TABLE HEADER */
			var row = thd.insertRow(0);    
			for( let [i, header] of headers.entries()) {
				let cell = row.insertCell(i);
				cell.innerHTML = header;
				if (header == "Id") {
					cell.style.display = "none";
				}
			}
			
			/* CREATE TABLE BODY */
			for(let [i, regs] of datos.entries()){
				let tr = document.createElement('tr');
				for(let [j, reg] of headers.entries()) {
					let td = document.createElement('td');
					let dat = document.createTextNode(regs[reg]);
					td.appendChild(dat);
					tr.appendChild(td);
					if (reg == "Id") {
						td.style.display = "none";
					}
				}
				tbdy.appendChild(tr);
			}
			
			tbl.appendChild(thd);
			tbl.appendChild(tbdy);
			
			container.appendChild(tbl);
			return container;
        }
        
        function addItem_kk() {
	    
        }
        
        function insertRow(datos) {   
        	let bloqueado = <?php echo json_encode($bloqueado); ?>;
		const bloquear = [];
		bloquear[0] = (bloqueado == "") ? '<td><input type="checkbox"></td>' : '' ;
		bloquear[1] = (bloqueado == "") ? '<td>'+
						    '<a type="button" class="btn light isolate-btn"><span class="material-icons">mode</span></a>'+
						    '<a type="button" class="btn light isolate-btn"><span class="material-icons">delete_outline</span></a>'+
							'</td>' : '' ;
		var posicion = String('000' + datos['No.']).slice(-3);
		var codproducto = datos['Id'];
		var codBarra = datos['Codigo'];
		var descr = datos['Descripcion'];
		var cant = datos['Cant.'];
		var price = moneda + datos['Precio'];
		var total = moneda + (datos['Cant.'] * datos['Precio']);

            let newRow = '<tr class="align-middle">'+
		                                bloquear[0] +
		                                '<td>' + posicion + '</td>'+
                                        '<td style="display:none">' + codproducto + '</td>' +
		                                '<td>' + codBarra + '</td>'+
		                                '<td>' + descr + '</td>'+
		                                '<td>' + cant + '</td>'+
		                                '<td class="text-nowrap">' + price + '</td>'+
		                                '<td class="text-nowrap total-cell">' + total + '</td>'+
		                                bloquear[1] +
		                            '</tr>';
            if($("#listaPedido tbody").find('tr:last') == null){
                $("#listaPedido tbody").find('tr:last').after(newRow);
            } else {
                $("#listaPedido tbody").append(newRow);
            }
            calTotals();
            clearProductField();
        }
        
        function checkNombre(data) {

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
                    posCliente = index;
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
            } else if(value < 0) {
                objeto.value = 0;
            }
            calTotals();     
        }

        function addItem(){
            var posicion = $("#posField").html();
            var codBarra = $("#codField > input").val();
            var descr = ($("#descField a").html() == null) ? $("#descField > input").val() : $("#descField a").html() ;
            var cant = $("#qtyField > input").val();
            var price = $("#priceField > input").val();
            var total = $("#totalField b").html();
            var codproducto = 0;
			for(let [i, productos] of objProduct.entries()) {
				if (productos['codigoBarra'] == codBarra) { 
					codproducto = productos['codproducto'];
				}
			}
            
        }

        function clearAll(){
            clearProductField();
            $("#listaPedido tbody").empty();
            document.getElementById("pedidoForm").reset();   
            update();
        }

        function clearProductField(){
            $("#modal_additem input").val('');
            $("#descField").html('');
            $("#qtyField > input").attr("max", "1");
            $("#totalField > b").html('RD $0.00');
        
        }

        function calTotals(){
            var totalCells = $("#listaPedido .total-cell");
            var subtotal = 0;
            var descuento = 0;
            total = 0;
            totalCells.each(function(){
                var val = $(this).html();
                val = _formatDouble(val);
                subtotal += val;
            });

            descuento = $("input[name='porciento']").val();
            descuento = (descuento/100) * subtotal;

            total = subtotal - descuento;

            $("#subtotal").html("RD $" + _formatCurrency(subtotal));
            $("#descuento").html("RD $" + _formatCurrency(descuento));
            $("#total").html("RD $" + _formatCurrency(total));

        }

        function validarPedido(pedido) {
        	if (confirm("¿Esta seguro que desea guardar el pedido?")) {
        	
	           let idCliente = 0;
	           for(let [i, clientes] of objClient.entries()) {
	                if (clientes['nombre'] == pedido.nombreCliente.value) { 
	                    idCliente = clientes['id'];
	                }
	            }
	
	            let cliente = {
	                idcliente: idCliente,
	                nombre: pedido.nombreCliente.value,
	                celular: pedido.celular.value,
	                porciento: pedido.porciento.value,
	                direccion: (pedido.dirEntrega.value != "") ? pedido.dirEntrega.value : "Local MVGraphics, recepción"
	            };
	
	            let detalle = {
	                comentario: $("#comentario").val(),
	                fecha_registro: pedido.f_registro.value,
	                fecha_entrega: pedido.f_entrega.value,
	                total: total
	            };
	            
	            // Loop through grabbing everything
				let productos = [];
				let $headers = $("#listaPedido thead tr th").not(":first").not(":last");
				let $pedidos = $("#listaPedido tbody tr").each(function(index) {
				  $cells = $(this).find("td").not(":first").not(":last");
				  productos[index] = {};
				  $cells.each(function(cellIndex) {
				    productos[index][$($headers[cellIndex]).html()] = $(this).html();
				  });  
				});
				
				// Let's put this in the object like you want and convert to JSON (Note: jQuery will also do this for you on the Ajax request)
				let pedidoObj = {};
	            pedidoObj.cliente = cliente;
				pedidoObj.productos = productos;
	            pedidoObj.detalle = detalle;
				let pedidoJSON = JSON.stringify(pedidoObj);
	            
	            $.ajax({
	                method: 'POST',
	                url: 'api/pedido.php',
	                data: { adicionar: pedidoJSON, estado: "Sin procesar" },
	                dataType: 'json'
	            })
	            .done(function(msg) {
	                if (msg.success) {
	                    alert('Satisfactorio: ' + msg.data + ' pedido: ' + msg.idpedido + ' - ' + msg.updating);
	                    window.location.replace("pedido.php?verPedido="+ msg.idpedido);
	                }
	                else {
	                    alert('Falló: ' + msg.data);
	                }
	            })
	            .fail(function(msg) {
	                alert('Falla: ' + msg);
	            });
	            clearAll();
	        }
	    }

        var _formatCurrency = function(amount) {
          return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d)+\.)/g, ","); 
        };

        var _formatDouble = function(amount) {
          return Number(amount.replace(/[^0-9.-]+/g,""));
        };

        var _ultimoPedido = function() {
            var val = [];
            val = $("#listaPedido tbody tr:last").find("td:nth-child(2)").html();
            if(val) {
                return Number(val);
            }            
        }
        
        function closeModal(modal) {
            modalForm = modal.querySelector("form");      
			if (typeof(modalForm) != undefined && modalForm != null) { modalForm.reset(); }
			
            modal.classList.remove("show");
                setTimeout(function(){
                    modal.style.display = "none";
                }, 1000);
        }

        $(document).ready(function(){
            $("#fields").ready(function(){
                var lastPos = _ultimoPedido();
                $("#posField").html();
            });
            $('input[type=checkbox]').change(function() {
                var name = $(this).attr("name");
                if(name == "useClientDirection") {
                    if($(this).is(':checked')) {
                        if( posCliente > 0) {
                            $("#dirEntrega").val(objClient[posCliente]['direccion']);
                        }
                    } else {
                        $("#dirEntrega").val('');
                    }
                }
            });

        });

        //LISTENERS
        window.addEventListener( "load", function() {
            loadCarrusel(<?php echo json_encode($pedido_estado_pedido); ?>);
            loadClientes(false);
            loadProductos(false);
            loadTabla();
            loadListeners();
            
        });

        window.onclick = function(event) {
            if (event.target == document.querySelector(".modal")) {
	            closeModal(event.target);
            }
        }

        function loadListeners() {
            document.querySelector("#btn_addItem").addEventListener( "click", function() {
                const modal = document.querySelector(this.getAttribute("target"));

                modal.style.display = "block";
                setTimeout(function(){
                    modal.classList.add("show");
                }, 10);
                
            });

            document.querySelectorAll(".modal_close").forEach(modal_close => 
                modal_close.addEventListener("click", function() {
                    closeModal(this.closest(".modal"));
                })
            );
            
            document.querySelectorAll("#modal_additem input").forEach(input_add =>
            	input_add.addEventListener("change", () => {
                    if(input_add.id == "codproductoInput") {
                        let location = <?php echo json_encode($location);?>;
                        let pos = "nada";

                        for(let [i, nombre] of objProductos.entries()) {
                            if (nombre['codigoBarra'] == input_add.value) { 
                                pos = i;
                            }
                        }

                        if(pos != "nada") {
                            if(objProductos[pos]['clase'] == "Servicio"){
                                document.querySelector("#descField").innerHTML = 
                                        "<input class='form-control' name='descproducto' style='margin: 10px 0 10px 0;' required>";
                            } else {
                                document.querySelector("#descField").innerHTML = "<h4>" + objProductos[pos]['descripcion'] + "</h4>";
                            }
                            document.querySelector(".product-img > img").setAttribute("src", location + objProductos[pos]['foto']);
                            document.querySelector("#qtyField").setAttribute("max", objProductos[pos]['existencia']);
                            document.querySelector("#qtyField").value = 1;
                            document.querySelector("#priceField").value = "RD $" + objProductos[pos]['precio'];
                            
                        } else {
                            clearProductField();
                        }
                    } 

                    let subtotal = document.querySelector("#priceField").value;
                    subtotal = subtotal.substring(moneda.length, subtotal.length);
                    let total = subtotal * document.querySelector("#qtyField").value;
                    total = _formatCurrency(total);
                    total = moneda + total;
                    document.querySelector("#addItem_form #totalField").innerHTML = total;
            	})
            );
           
try{ 
            document.querySelectorAll(".modal-footer button:not([type='reset'])").forEach(btn =>
                btn.addEventListener( "click", () => {
                    alert("Hola " + btn.form.codproducto.value);
                    let datos = document.querySelector("#addItem_form");
                    var codBarra = datos.codproducto.value;
                    var descField = datos.descField.value;
                    var qtyField = datos.qtyField.value;
                    var priceField = datos.priceField.value;
                    var totalField = datos.totalField.innerText;

                    switch(addBtn.id) {
                        case "addContinue":
                            alert(codBarra + descField);
                            break;
                        case "addExit":
                            alert(datos.priceField);
                            break;
                        default:
                    }
                })
            );
			
			document.querySelector("#addItem_form").addEventListener( "reset", (e) => {
                if(document.querySelector("#codproductoInput").value != '') {
                    if(!confirm("Esta seguro de que quiere limpiar todos los campos?")){
                        e.preventDefault;
                    }
                    document.querySelector("#descField").innerHTML = "";
                    document.querySelector(".product-img > img").setAttribute("src", "inventario/def_product.png");
                    document.querySelector("#addItem_form #totalField").innerHTML = moneda + "0.00";
                }
            });

} catch(e) { alert(e) }

        }
        
    </script>

</head>
<body>
	<?php include 'includes/header.php' ?>

    <section>
        <div class="header light-static">
			<h1><?php echo $titulo_header; ?></h1>
			<hr>
			<div class="alert">
				<?php echo isset($alert) ? $alert : ''; ?>
			</div>
        </div>
        <form id="pedidoForm" action="javascript:;" onsubmit="validarPedido(this)"></form>
        <div class="row">
            <div class="col-sm-8">
                <div class="client card-1">
                    <h3>Datos del Cliente</h3>
                    <div class="form-group">
                        <label for="nombreCliente">Nombre del Cliente</label>
                        <div class="inline">
                            <a href="javascript:loadClientes(true);" class="btn dark" data-toggle="tooltip" title="Actualizar Lista">
                                <span class="material-icons btn-icon">autorenew</span>
                            </a>
                            <input list="nombreCliente" onchange="checkNombre(this.value)" class="form-control"
                                name="nombreCliente" placeholder="Selecione de la lista" form="pedidoForm" required
								value="<?php echo $cliente_nombre; ?>" <?php echo $bloqueado; ?> >
                            <datalist id="nombreCliente">
                            </datalist>
                            <?php if(Puede("clientes","c")) { ?>
                            <a href="cliente.php" class="btn dark" data-toggle="tooltip" title="Crear">
                                <span class="material-icons btn-icon">person_add</span>
                            </a>
                            <? } ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label for="celular">Celular:</label>
                            <input type="text" class="form-control" name="celular" data-type="celular" form="pedidoForm"
							value="<?php echo $cliente_telefono; ?>" <?php echo $bloqueado; ?> >
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="porciento">% Descuento:</label>
                            <input type="number" onkeyup="resetMax(this)" class="form-control" name="porciento" min="0" form="pedidoForm"
							value="<?php echo $cliente_porciento; ?>" <?php echo $bloqueado; ?> >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="DirEntrega">Direccion de Entrega</label>
                        <div style="display: inline-flex; float: right; margin-bottom: 5px;">
                            <input type="checkbox" class="vcenter" name="useClientDirection" form="pedidoForm">
                                <label style="margin-block-start: auto; margin:auto auto 0px 5px;"> Usar direccion del cliente</label>
                            </input>
                        </div>
                        <input type="text" class="form-control" name="dirEntrega" id="dirEntrega" placeholder="Entregar en MVGraphics (cambiar en caso contrario)" form="pedidoForm"
						value="<?php echo $cliente_direccion; ?>" <?php echo $bloqueado; ?> >
                    </div>
                </div>
            <hr>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <button id="btn_addItem" target="#modal_additem" class="btn dark"><span class="material-icons btn-icon">add</span>Añadir</button>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dark" id="listaPedido">
                                <thead>    
                                    <tr>
                                    <?php if($bloqueado == "") {?>
                                        <th><input id="checkAll" type="checkbox"></th>
                                    <?php } ?>
                                        <th>No.</th>
                                        <th style="display:none">Id</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Cant.</th>
                                        <th>Precio</th>
                                        <th>Total</th>
                                    <?php if($bloqueado == "") {?>
                                        <th>Acciones</th>
                                    <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                                <tfoot>
                                    
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="row text-center card-1">
                    <div class="col-sm-12" id="numPedido">
                        <h3>Pedido: <b><i>sin procesar</i></b></h3>
                    </div>
                    <hr>
                    <div class="col-sm-12 py-5">
                        <span><b>Fecha de Registro</b><span>
                        <input type="date" id="f_registro" value="<?php echo date('Y-m-d'); ?>" form="pedidoForm" required>
                    </div>
                    <div class="col-sm-12 py-5">
                        <span>Fecha de Entrega<span>
                        <input type="date" id="f_entrega" form="pedidoForm" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d'). ' + 7 days')); ?>" required>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="card-1" id="estadoPedido">
                        Estatus del pedido:
                        <br><b><i>sin procesar</i></b>
                    </div>
                    <br>
                    <div class="card-1" id="estadoProceso">
                        Estatus en  produccion:
                        <br><b><i>sin procesar</i></b>
                    </div>
                </div>
                <hr>
                <div class="card-1">
                    <label for="comentario">Comentarios del pedido <i>(opcional)</i>:</label>
                    <textarea class="form-control" name="comentario" id="comentario" form="pedidoForm" rows="4" cols="50"></textarea>
                </div>
                <hr>
                <div class="row">
                    <div class="row">
                        <div class="col-xs-6" style="text-align:right;">Subtotal:</div>
                        <div class="col-xs-6" id="subtotal">RD $0.00</div>
                    </div>
                    <div class="row" style="font-style: oblique; color: #940000;">
                        <div class="col-xs-6" style="text-align:right;">Descuento:</div>
                        <div class="col-xs-6" id="descuento">RD $0.00</div>
                    </div>
                    <div class="row" style="font-weight: bold;">
                        <div class="col-xs-6" style="text-align:right;">Total:</div>
                        <div class="col-xs-6" id="total">RD $0.00</div>
                    </div>
                </div>
                <hr>
                <div id="opcionesBtn" class="row text-center">
                </div>
                    <hr>
                    <button type="button" onclick="window.history.back()" class="btn btn-danger col-xs-12 my-5"><span class="material-icons btn-icon">cancel</span>Retroceder</button>
            </div>
        </div>
    </section>

    <!-- Modal para añadir articulo -->
    <div id="modal_additem" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
            <div class="modal-header">
                <a href="javascript:loadProductos(true);" class="btn dark" data-toggle="tooltip" title="Actualizar Lista">
                    <span class="material-icons btn-icon">autorenew</span>
                </a>
                <span>Adicionando artículos</span>
                <span class="modal_close">&times;</span>
            </div>

            <!-- Modal content -->
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="product-img">
                            <img src="inventario/def_product.png" alt="Preview">
                        </div>
                    </div>
                    <form method="POST" id="addItem_form" class="col-sm-6">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="codproducto">Codigo</label>
                                <input list="codproducto" id="codproductoInput" class="form-control" name="codproducto" required>
	                                <datalist id="codproducto">
	                                    <option value="codigoBarra">
	                                        ClaseProducto -> Descripcion
	                                </datalist>
                            </div>
                            <div id="descField"></div>
                            <div class="form-group">
                            	<label for="qtyField">Cantidad</label>
	                            <input type="number" id="qtyField" class="form-control" name="cantidad" min="1" max="1" style="min-width: 80px;">
                            </div>
                            <div class="form-group">
                            	<label for="priceField">Precio Sugerido</label>
                            	<input type="text" id="priceField" class="form-control" data-type="currency" name="costo" style="min-width: 150px;">
                            </div>
                            <hr>
	                        <div class="form-group">
	                        	<span id="totalField" class="text-nowrap" style="font-weight: bold; font-size:20px">RD $0.00</span>
	                        </div>
                          </div>
                    </form>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button id='addContinue' form="addItem_form" class="btn dark">Adicionar y continuar</button>
                <button id='addExit' form="addItem_form" class="btn dark">Adicionar y Salir</button>
                <button type='reset' form="addItem_form" class="btn light">Limpiar</button>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php' ?>
</body>
</html>