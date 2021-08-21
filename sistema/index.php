<?php 
session_start();
	if (empty($_SESSION['active'])) {

		header('location: ../');

	} else {
		include "../conexion.php";
		include "includes/accesos.php"; 
	}

 ?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include 'includes/script.php' ?>
	<title>Ventana Principal</title>
</head>
<body>
	<?php include 'includes/header.php' ?>
    <section class="container">

        <div class="panel">
            <div class="panel-heading">
                <div class="jumbotron">
                    <h1>Bienvenido/a <small style="display:block;"><?php echo $_SESSION['nombre'];?></small></h1>
                </div>
                <hr>
            </div>
            <div class="panel-body">
                <object type="text/html" style="width: -webkit-fill-available; height:100vh;" data="https://getbootstrap.com/docs/4.0/examples/dashboard/" ></object>
            </div>
        </div>

    </section>

	<?php include 'includes/footer.php' ?>
</body>
</html>