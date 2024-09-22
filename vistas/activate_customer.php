<?php require_once "./inc/header.php"; ?>
<?php
// Configuración de la base de datos
$BD = 'bd_donde_deya';
$miPDO = new PDO("mysql:host=127.0.0.1; dbname=$BD;", 'root', '');

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
$miConsulta = $miPDO->prepare('
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
    $miActualizar = $miPDO->prepare('
        UPDATE usuario
        SET usuario_activo = 1
        WHERE usuario_email = :email AND usuario_token = :token
    ');
    $miActualizar->execute([
        'email' => $email,
        'token' => $token
    ]);
    
    echo '
            <div class="notification is-info is-light">
                <strong>¡Estimado usuario, su cuenta se ha activado con éxito!</strong><br>
            </div>
        ';

} else {
    echo '
            <div class="notification is-danger is-light">
                <strong>Parametros Invalidos o cuenta ya activada</strong><br>
            </div>
        ';
}
?>

<?php require_once "./inc/footer.php"; ?>
