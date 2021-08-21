<?php
    $usuario_actual = $_SESSION['idUser'];
    $h_query = mysqli_query($conn, "SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol, r.idrol, u.estatus
                                    FROM usuario u INNER JOIN rol r ON u.rol = r.idrol
                                    WHERE u.idusuario LIKE '$usuario_actual'");

    while ($get_user = mysqli_fetch_array($h_query)) {
        $_SESSION['idUser'] = $get_user['idusuario'];
        $_SESSION['nombre'] = $get_user['nombre'];
        $_SESSION['email'] = $get_user['email'];
        $_SESSION['user'] = $get_user['usuario'];
        $_SESSION['idrol'] = $get_user['idrol'];
        $_SESSION['rol'] = $get_user['rol'];
    }
    
    // Acceso de Perfiles de Usuario
    function Conceder($componente,$privilegio) {
        global $respuesta;
        $estado = false;
        
        for($i=0; $i <= count($respuesta); $i++) {
            if ($respuesta[$i]['a'] == $componente && $respuesta[$i][$privilegio] > 0){
                $estado = true;
            }
        }

        return $estado;
    }

 ?>

<nav class="navbar dark-static navbar-sticky-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar top-bar"></span>
        <span class="icon-bar middle-bar"></span>
        <span class="icon-bar bottom-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg" class="brand_logo" viewBox="0, 0, 400,354.35897435897436"><g id="svgg"><path id="path0" d="M176.213 0.812 C 33.151 15.177,-45.467 155.583,27.542 266.325 C 62.328 319.088,133.519 356.151,196.458 354.265 L 201.890 354.103 180.730 244.700 C 169.092 184.528,159.466 135.193,159.339 135.065 C 158.719 134.446,149.557 133.747,146.438 134.081 L 142.935 134.456 143.260 138.308 C 143.439 140.426,144.194 145.077,144.936 148.644 C 145.679 152.210,149.337 170.936,153.065 190.256 L 159.842 225.385 153.617 230.000 C 120.804 254.327,118.339 256.071,117.505 255.545 C 116.289 254.778,115.629 252.479,114.147 243.846 C 110.135 220.468,92.533 134.241,91.680 133.785 C 91.189 133.522,87.257 133.371,82.941 133.448 L 75.093 133.590 85.766 188.974 C 91.635 219.436,96.579 245.565,96.751 247.040 C 97.180 250.708,96.546 251.265,74.872 266.253 C 54.100 280.618,56.289 280.338,54.116 268.900 C 53.320 264.710,45.698 224.705,37.178 180.000 C 28.658 135.295,21.712 98.703,21.741 98.685 C 21.771 98.667,59.040 99.630,104.562 100.824 L 187.329 102.995 191.134 104.875 C 195.778 107.171,199.713 110.972,201.726 115.106 C 202.979 117.682,206.512 135.084,225.772 233.550 C 238.189 297.033,248.446 349.085,248.565 349.220 C 249.485 350.263,271.746 344.321,281.014 340.559 C 341.279 316.092,384.800 267.293,396.594 210.965 C 414.582 125.050,359.865 40.290,267.057 10.305 C 255.406 6.541,233.967 1.759,233.015 2.712 C 232.573 3.153,269.003 185.847,269.958 187.980 C 270.447 189.074,271.958 190.332,274.945 192.133 C 277.302 193.554,285.767 198.675,293.755 203.512 C 309.151 212.836,310.839 213.540,311.790 211.038 C 312.160 210.066,310.093 198.394,303.005 161.422 C 297.908 134.832,293.704 112.297,293.664 111.343 C 293.576 109.290,327.275 83.077,330.001 83.077 C 331.940 83.077,331.082 79.032,348.739 171.506 C 357.783 218.873,365.512 259.196,365.916 261.112 C 366.443 263.619,366.502 264.981,366.127 265.968 L 365.606 267.339 349.598 266.981 C 340.793 266.783,331.293 266.388,328.486 266.102 L 323.381 265.582 278.998 238.113 C 235.070 210.925,232.721 209.404,231.679 207.468 C 231.384 206.922,221.981 160.076,210.781 103.366 L 190.419 0.256 186.876 0.177 C 184.928 0.133,180.129 0.419,176.213 0.812 " /></g></svg>
      </a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li class="active light"><a class="dark" href="index.php"><span class="material-icons btn-icon">home</span>Inicio</a></li>

        <!--//// Categoria de Registro (venta, compras, ect) \\\\ -->
        <?php if(Conceder('registros','v')) { ?>
            <li class="dropdown">
                <a class="dropdown-toggle dark" data-toggle="dropdown" href="#"><span class="material-icons btn-icon">app_registration</span>Registros
                <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php if(Conceder('ventas','v')) { ?>
                        <li><a class="light" href="lista_pedido.php"><span class="material-icons btn-icon">add_shopping_cart</span>Pedido</a></li>
                        <li><a class="light" href="facturacion.php"><span class="material-icons btn-icon">request_page</span>Facturacion</a></li>
                    <?php } if(Conceder('compras','c')) {?>
                        <li><a class="light" href="#"><span class="material-icons btn-icon">monetization_on</span>Compras</a></li>
                    <?php } ?>
                </ul>
            </li>

        <!--//// Categoria de Clientes (ver, registro, editar, deshabilitar, etc.) \\\\ -->
        <?php } if(Conceder('clientes','v')) { ?>
            <li class="dropdown">
                <a class="dropdown-toggle dark" data-toggle="dropdown" href="#"><span class="material-icons btn-icon">groups</span>Clientes
                <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php if(Conceder('clientes','c')) { ?>
                        <li><a class="light" href="cliente.php"><span class="material-icons btn-icon">person_add</span>Nuevo Cliente</a></li>
                    <?php } ?>
                    <li><a class="light" href="lista_cliente.php"><span class="material-icons btn-icon">people_alt</span>Lista de Clientes</a></li>
                </ul>
            </li>
        
        <!--//// Categoria de Productos (ver, inventario, registro, editar, deshabilitar, etc.) \\\\ -->
        <?php } if(Conceder('productos','v')) { ?>
            <li class="dropdown">
                <a class="dropdown-toggle dark" data-toggle="dropdown" href="#"><span class="material-icons btn-icon">widgets</span>Inventario
                <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php if(Conceder('productos','c')) { ?>
                        <li><a class="light" href="producto.php"><span class="material-icons btn-icon">library_add</span>Nuevo Producto</a></li>
                    <?php } ?>
                    <li><a class="light" href="lista_producto.php"><span class="material-icons btn-icon">category</span>Lista de Productos</a></li>
                    <li><a class="light" href="categoria.php"><span class="material-icons btn-icon">dashboard</span>Categorias</a></li>
                    <li><a class="light" href="inventario.php"><span class="material-icons btn-icon">list_alt</span>Hacer Inventario</a></li>
                </ul>
            </li>

        <!--//// Categoria de Proveedores (ver, editar, registro, deshabilitar, etc.) \\\\ -->
        <?php } if(Conceder('proveedores','v')) { ?>
            <li class="dropdown">
                <a class="dropdown-toggle dark" data-toggle="dropdown" href="#"><span class="material-icons btn-icon">store_mall_directory</span>Proveedores
                <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php if(Conceder('proveedores','c')) { ?>
                        <li><a class="light" href="proveedor.php"><span class="material-icons btn-icon">add_business</span>Nuevo Proveedor</a></li>
                    <?php } ?>
                    <li><a class="light" href="lista_proveedor.php"><span class="material-icons btn-icon">business</span>Lista de Proveedores</a></li>
                </ul>
            </li>

        <!--//// Categoria de Usuarios (ver, editar, registro, perfiles, deshabilitar, etc.) \\\\ -->
        <?php } if(Conceder('usuarios','v')) { ?>
            <li class="dropdown">
                <a class="dropdown-toggle dark" data-toggle="dropdown" href="#"><span class="material-icons btn-icon">supervised_user_circle</span>Usuarios
                <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php if(Conceder('usuarios','c')) { ?>
                        <li><a class="light" href="usuario.php"><span class="material-icons btn-icon">add_moderator</span>Nuevo Usuario</a></li>
                    <?php } ?>
                    <li><a class="light" href="lista_usuario.php"><span class="material-icons btn-icon">admin_panel_settings</span>Lista de Usuarios</a></li>
                    <?php if(Conceder('usuarios','e')) { ?>
                        <li><a class="light" href="perfil.php"><span class="material-icons btn-icon">settings_accessibility</span>Perfiles de Usuario</a></li>
                    <?php } ?>
                </ul>
            </li>
        <? } ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li>
            <a class="dark" href="usuario.php?actualizarmiID=<?php echo $usuario_actual; ?>">
                <span class="material-icons btn-icon">manage_accounts</span>
                <div class="container">
                    <div class="marquee">
                        <span class="username"><?php echo $_SESSION['nombre']; ?></span>
                        <span class="userrol"><?php echo $_SESSION['rol']; ?></span>
                    </div>
                </div>
            </a>
        </li>
        <li><a class="dark" href="salir.php"><span class="glyphicon glyphicon-log-out"></span> Salir</a></li>
      </ul>
    </div>
  </div>
</nav>