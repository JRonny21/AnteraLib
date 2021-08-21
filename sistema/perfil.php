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

        //Filtro de Busqueda
        $filtro = '';

        if(!empty($_POST['save'])){
            echo "Registro guardado"; exit;
        }

	}

 ?>

<!doctype html>
<html lang="es">
<head>
	<?php include 'includes/script.php' ?>
	<title>Perfiles de Usuario</title>
</head>
<body>
	<?php include 'includes/header.php' ?>

	<div id="container">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
                <h1 class="light-static"><span class="material-icons title-icon">settings_accessibility</span>Perfiles de Usuario</h1>
            </div>  
            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-4">
                <form method="get" action="perfil.php" class="float-right"> 
                    <div class="row">
                        <div class="col-xs-12 col-md-10">
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
    <div class="container">
		<div class="flex_box">
            <div class="profile_card_container">
                <div class="profile_card">
                    <div class="col-xs-12 col-md-12">
                        <div class="profile_card_header">
                            <span class="profile_card_title">Clientes</span>
                            <span class="profile_card_options">♀ # ~ </span>
                        </div>
                    </div>
                    <div class="row">
                        <table class="table table-striped">
                            <tr>
                                <td class="profile_access">Administrador:</td>
                                <td class="profile_permit">ver crea edita elimina</td>
                            </tr>
                            <tr>
                                <td class="profile_access">Supervisor:</td>
                                <td class="profile_permit">ver edita </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 text-center col-sm-4 col-md-4 col-lg-4">
                <a type="button" href="perfil.php" class="btn light "><span class="material-icons btn-icon">refresh</span>Restablecer</a>
            </div>
            <form action="" method="post" class="col-xs-12 text-center col-sm-4 col-md-4 col-lg-4">
                <input type="hidden" name="save" id="save" value="all">
                <button type="submit" class="btn dark"><span class="material-icons btn-icon">save</span>Guardar Cambios</button>
            </form>
        </div>
    </div>

	<?php include 'includes/footer.php' ?>
</body>
</html>