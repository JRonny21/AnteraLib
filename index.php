<script type="text/javascript">
    
</script>

<?php 

	$respuesta = '';
    $zoom_out_rotating = '';
    $shrink = '';
	session_start();
	if (!empty($_SESSION['active'])) {

		header('location: sistema/');

	} else {

		if (!empty($_POST)) {

			if (empty($_POST['usuario']) || empty($_POST['clave'])) {
				$respuesta = 'Ingrese su usuario y Contraseña';
			} else {

				require_once 'sistema/conexion.php';

				$user = mysqli_real_escape_string($conn,$_POST['usuario']);
				$pass = md5(mysqli_real_escape_string($conn,$_POST['clave']));

				$query = mysqli_query($conn, "SELECT u.idusuario, u.nombre, u.correo, u.usuario, u.clave, u.estatus, r.idrol, r.rol
								                FROM usuario u INNER JOIN rol r ON u.rol = r.idrol
								                WHERE u.usuario= '$user' AND u.clave = '$pass'");

				mysqli_close($conn);
				$result = mysqli_num_rows($query);

				if ($result > 0) {

					$data = mysqli_fetch_array($query);

					if ($data['estatus'] == 'activo') {
                        $zoom_out_rotating = 'zoom-out-rotating';
                        $shrink = 'shrink';
                        $respuesta = "Bienvenido Profesor!";

                        $stay = $_POST['permanecer'];
                        if($stay == "on"){
                            $_SESSION['stay'] = true;
                        } else {
                            $_SESSION['stay'] = false;
                        }
                        $_SESSION['last_login_timestamp'] = time();  
						$_SESSION['active'] = true;
						$_SESSION['idUser'] = $data['idusuario'];
						$_SESSION['nombre'] = $data['nombre'];
						$_SESSION['email'] = $data['email'];
						$_SESSION['user'] = $data['usuario'];
						$_SESSION['idrol'] = $data['idrol'];
						$_SESSION['rol'] = $data['rol'];

                        header("refresh:1; url=sistema/");
					} else {
                        $respuesta = '¡Usuario desactivado! Contacte al administrador';				
						session_destroy();
					}

				} else {
                    $respuesta = 'El usuario o la clave son incorrectos';			
					session_destroy();
				}
			}
		}			
	}
 ?>

<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="noindex, nofollow"> 

        <title>Login | MV Graphics</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script type="text/javascript" async="" src="https://ssl.google-analytics.com/ga.js"></script><script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
        <link media="all" type="text/css" rel="stylesheet" href="sistema/css/login.css">

    </head>

<!--Coded with love by Mutiullah Samim-->
<body>
	<div class="container h-100">
		<div class="d-flex justify-content-center h-100">
			<div class="user_card open <?php echo $shrink; ?>">
				<div class="d-flex justify-content-center">
					<div id="logoBrand" class="brand_logo_container <?php echo $zoom_out_rotating; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg" class="brand_logo" viewBox="0, 0, 400,354.35897435897436"><g id="svgg"><path id="path0" d="M176.213 0.812 C 33.151 15.177,-45.467 155.583,27.542 266.325 C 62.328 319.088,133.519 356.151,196.458 354.265 L 201.890 354.103 180.730 244.700 C 169.092 184.528,159.466 135.193,159.339 135.065 C 158.719 134.446,149.557 133.747,146.438 134.081 L 142.935 134.456 143.260 138.308 C 143.439 140.426,144.194 145.077,144.936 148.644 C 145.679 152.210,149.337 170.936,153.065 190.256 L 159.842 225.385 153.617 230.000 C 120.804 254.327,118.339 256.071,117.505 255.545 C 116.289 254.778,115.629 252.479,114.147 243.846 C 110.135 220.468,92.533 134.241,91.680 133.785 C 91.189 133.522,87.257 133.371,82.941 133.448 L 75.093 133.590 85.766 188.974 C 91.635 219.436,96.579 245.565,96.751 247.040 C 97.180 250.708,96.546 251.265,74.872 266.253 C 54.100 280.618,56.289 280.338,54.116 268.900 C 53.320 264.710,45.698 224.705,37.178 180.000 C 28.658 135.295,21.712 98.703,21.741 98.685 C 21.771 98.667,59.040 99.630,104.562 100.824 L 187.329 102.995 191.134 104.875 C 195.778 107.171,199.713 110.972,201.726 115.106 C 202.979 117.682,206.512 135.084,225.772 233.550 C 238.189 297.033,248.446 349.085,248.565 349.220 C 249.485 350.263,271.746 344.321,281.014 340.559 C 341.279 316.092,384.800 267.293,396.594 210.965 C 414.582 125.050,359.865 40.290,267.057 10.305 C 255.406 6.541,233.967 1.759,233.015 2.712 C 232.573 3.153,269.003 185.847,269.958 187.980 C 270.447 189.074,271.958 190.332,274.945 192.133 C 277.302 193.554,285.767 198.675,293.755 203.512 C 309.151 212.836,310.839 213.540,311.790 211.038 C 312.160 210.066,310.093 198.394,303.005 161.422 C 297.908 134.832,293.704 112.297,293.664 111.343 C 293.576 109.290,327.275 83.077,330.001 83.077 C 331.940 83.077,331.082 79.032,348.739 171.506 C 357.783 218.873,365.512 259.196,365.916 261.112 C 366.443 263.619,366.502 264.981,366.127 265.968 L 365.606 267.339 349.598 266.981 C 340.793 266.783,331.293 266.388,328.486 266.102 L 323.381 265.582 278.998 238.113 C 235.070 210.925,232.721 209.404,231.679 207.468 C 231.384 206.922,221.981 160.076,210.781 103.366 L 190.419 0.256 186.876 0.177 C 184.928 0.133,180.129 0.419,176.213 0.812 " /></g></svg>
					</div>
				</div>
				<div class="d-flex justify-content-center form_container">
					<form method="POST" action="">
						<div class="input-group mb-3">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-user"></i></span>
							</div>
							<input type="text" name="usuario" class="form-control input_user" value="" placeholder="usuario" required>
						</div>
						<div class="input-group mb-2">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-key"></i></span>
							</div>
							<input type="password" name="clave" class="form-control input_pass" value="" placeholder="contraseña" required>
						</div>
						<div class="form-group">
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input" name="permanecer" id="customControlInline">
								<label class="custom-control-label" for="customControlInline">Mantener sesión activa</label>
							</div>
						</div>
							<div class="d-flex justify-content-center mt-3 login_container">
                            <input type="submit" class="btn login_btn" value="Acceder">
                        </div>
					</form>
				</div>
		
				<div class="mt-4">
					<div class="d-flex justify-content-center links">
						<a href="#">¿Olvidaste tu contraseña?</a>
					</div>
				</div>
                <div class="mt-4">
                    <div class="d-flex justify-content-center links">
                        <span id="add_err" style="font-weight: bold; color: red;"><?php echo $respuesta; ?></span>    
                    </div>
			</div>
		</div>
	</div>
    <footer class="credito">
        © 2020 Copyright:
        <span class="span">Version del sistema: 0.1.2 / supported by: mipropia.com <br> JRonny Tecno-desarrollo (829) 776-6614</span></a>
     </footer>
</body>
</html>