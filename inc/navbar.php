<?php require_once "./inc/header.php"; ?>



<nav class="navbar" role="navigation" aria-label="main navigation">

    

        <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navbarBasicExample" class="navbar-menu">
        <div class="navbar-start">
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="link">Administradores</a>

                <div class="navbar-dropdown">
                    <a href="index.php?vista=user_new" class="navbar-item">Nuevo</a>
                    <a href="index.php?vista=user_list" class="navbar-item">Lista</a>
                    <a href="index.php?vista=user_search" class="navbar-item">Buscar</a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="link">Categorías</a>

                <div class="navbar-dropdown">
                    <a href="index.php?vista=category_new" class="navbar-item">Nueva</a>
                    <a href="index.php?vista=category_list" class="navbar-item">Lista</a>
                    <a href="index.php?vista=category_search" class="navbar-item">Buscar</a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="link">Productos</a>

                <div class="navbar-dropdown">
                    <a href="index.php?vista=product_new" class="navbar-item">Nuevo</a>
                    <a href="index.php?vista=product_list" class="navbar-item">Lista</a>
                    <a href="index.php?vista=product_category" class="navbar-item">Por categoría</a>
                    <a href="index.php?vista=product_search" class="navbar-item">Buscar</a>
                </div>
            </div>


            <div class="navbar-item has-dropdown is-hoverable">
                <a href="index.php?vista=pedidos_admin" class="link">Pedidos</a>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a href="index.php?vista=ventas" class="link">ventas</a>
            </div>


            <div class="navbar-item has-dropdown is-hoverable">
                <a href="index.php?vista=home" class="link">Inicio</a>
            </div>

        </div>

        

        

        <div class="navbar-end">
            <div class="navbar-item">
                <div class="buttons">
                    <a href="index.php?vista=user_update&user_id_up=<?php echo $_SESSION['id']; ?>" class="button is-primary is-rounded custom">
                        Mi cuenta
                    </a>

                    <a href="index.php?vista=logout" class="button is-rounded custom-color">
                        Salir
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

