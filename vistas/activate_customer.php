<?php require_once "./inc/header.php"; ?>
<?php
// Incluir el archivo Utils para la conexión a la base de datos
require_once './modelo/Utils.php';

// Establecer la conexión a la base de datos usando la clase Utils
$conn = Utils::conexion();

// Obtener parámetros de la URL
$email = isset($_GET['email']) ? $_GET['email'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Verificar que ambos parámetros están presentes
if (empty($email) || empty($token)) {
    die('Parámetros inválidos');
}

// Decodificar el email y el token
$email = urldecode($email);
$token = urldecode($token);

// Preparar consulta para verificar el usuario con el email y el token
$miConsulta = $conn->prepare('
    SELECT * FROM usuario
    WHERE usuario_email = :email AND usuario_token = :token AND usuario_activo = 0
');
$miConsulta->execute([
    'email' => $email,
    'token' => $token
]);
$usuario = $miConsulta->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Activar la cuenta
    $miActualizar = $conn->prepare('
        UPDATE usuario
        SET usuario_activo = 1
        WHERE usuario_email = :email AND usuario_token = :token
    ');
    $miActualizar->execute([
        'email' => $email,
        'token' => $token
    ]);
    
    // Notificación de activación exitosa
    echo '
        <div class="notification is-success">
            <strong>¡Estimado usuario, su cuenta se ha activado con éxito!</strong><br>
        </div>
        <a href="index.php?vista=tienda" class="button is-primary mt-3">Regresar a la tienda</a>
    ';

} else {
    // Notificación de error
    echo '
        <div class="notification is-danger">
            <strong>Parámetros inválidos o cuenta ya activada</strong><br>
        </div>
        <a href="index.php?vista=tienda" class="button is-primary mt-3">Regresar a la tienda</a>
    ';
}
?>

<?php require_once "./inc/footer.php"; ?>


