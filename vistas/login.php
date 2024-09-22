<?php require_once "./inc/header.php"; ?>



<div class="main-container">

	<form class="box_login" action="" method="POST" autocomplete="off">
		<h5 class="box_login_title">Iniciar Sesión</h5>

		<div class="field">
			<label class="label">Usuario</label>
			<div class="control">
			    <input class="input" type="text" name="login_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required >
			</div>
		</div>

		<div class="field">
		  	<label class="label">Contraseña</label>
		  	<div class="control">
		    	<input class="input" type="password" name="login_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required >
		  	</div>
		</div>

		<p class="texto">
			<button type="submit" class="btn">Iniciar sesion</button>
		</p>

		<?php
            if (isset($_POST['login_usuario']) && isset($_POST['login_clave'])) {
                require_once "./modelo/Utils.php"; // Cargar Utils para usar la función de conexión
                require_once "./php/iniciar_sesion.php"; // Incluir el archivo que maneja el inicio de sesión

                // Establecer la conexión a la base de datos
                $dbConnection = Utils::conexion();
                // Llamar al archivo de inicio de sesión
            }
        ?>
    </form>
	</form>


</div>

<?php require_once "./inc/footer.php"; ?>