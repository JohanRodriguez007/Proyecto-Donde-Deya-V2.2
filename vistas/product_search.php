<?php
ob_start(); // Inicia la captura de salida

require_once "./modelo/Utils.php"; // Incluir archivo de utilidades
$conn = Utils::conexion(); // Instanciar la conexión

if (isset($_POST['modulo_buscador'])) {
    require_once "./php/buscador.php";
}

if (isset($_GET['product_id_del'])) {
    require_once "./php/producto_eliminar.php";
}

if (!isset($_GET['page'])) {
    $pagina = 1;
} else {
    $pagina = (int) $_GET['page'];
    if ($pagina <= 1) {
        $pagina = 1;
    }
}

$categoria_id = (isset($_GET['category_id'])) ? $_GET['category_id'] : 0;

$pagina = Utils::limpiar_cadena($pagina);
$url = "index.php?vista=product_search&page="; /* <== */
$registros = 15;
$busqueda = $_SESSION['busqueda_producto'] ?? ''; /* <== */

?>

<div class="container is-fluid mb-6">
    <h1 class="title">Productos</h1>
    <h2 class="subtitle">Buscar producto</h2>
</div>

<div class="container pb-6 pt-6">
    <?php if (!isset($_SESSION['busqueda_producto']) || empty($_SESSION['busqueda_producto'])) { ?>
        <div class="columns">
            <div class="column">
                <form action="" method="POST" autocomplete="off">
                    <input type="hidden" name="modulo_buscador" value="producto">
                    <div class="field is-grouped">
                        <p class="control is-expanded">
                            <input class="input is-rounded" type="text" name="txt_buscador" placeholder="¿Qué estás buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}" maxlength="30">
                        </p>
                        <p class="control">
                            <button class="button is-info" type="submit">Buscar</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    <?php } else { ?>
        <div class="columns">
            <div class="column">
                <form class="has-text-centered mt-6 mb-6" action="" method="POST" autocomplete="off">
                    <input type="hidden" name="modulo_buscador" value="producto">
                    <input type="hidden" name="eliminar_buscador" value="producto">
                    <p>Estás buscando <strong>“<?php echo $_SESSION['busqueda_producto']; ?>”</strong></p>
                    <br>
                    <button type="submit" class="button is-danger is-rounded">Eliminar búsqueda</button>
                </form>
            </div>
        </div>

        <?php
        # Paginador producto #
        require_once "./php/producto_lista.php";
        ?>
    <?php } ?>
</div>

<?php require_once "./inc/footer_V2.php"; ?>
<?php ob_end_flush(); // Finaliza y envía la salida almacenada ?>
