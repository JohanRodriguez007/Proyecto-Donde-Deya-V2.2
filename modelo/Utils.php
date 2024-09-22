<?php

class Utils {
    
    # Conexion a la base de datos #
    public static function conexion() {
        $pdo = new PDO('mysql:host=localhost;dbname=bd_donde_deya', 'root', '');
        return $pdo;
    }

    # Verificar datos #
    public static function verificar_datos($filtro, $cadena) {
        return !preg_match("/^" . $filtro . "$/", $cadena);
    }

    # Limpiar cadenas de texto #
    public static function limpiar_cadena($cadena) {
        $cadena = trim($cadena);
        $cadena = stripslashes($cadena);
        $cadena = str_ireplace([
            "<script>", "</script>", "<script src", "<script type=",
            "SELECT * FROM", "DELETE FROM", "INSERT INTO", "DROP TABLE",
            "DROP DATABASE", "TRUNCATE TABLE", "SHOW TABLES;", "SHOW DATABASES;",
            "<?php", "?>", "--", "^", "<", "[", "]", "==", ";", "::"
        ], "", $cadena);
        return trim($cadena);
    }

    # Funcion renombrar fotos #
    public static function renombrar_fotos($nombre) {
        $nombre = str_ireplace([" ", "/", "#", "-", "$", ".", ","], "_", $nombre);
        return $nombre . "_" . rand(0, 100);
    }

    # Funcion paginador de tablas #
    public static function paginador_tablas($pagina, $Npaginas, $url, $botones) {
        $tabla = '<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';

        if ($pagina <= 1) {
            $tabla .= '<a class="pagination-previous is-disabled" disabled>Anterior</a><ul class="pagination-list">';
        } else {
            $tabla .= '<a class="pagination-previous" href="' . $url . ($pagina - 1) . '">Anterior</a><ul class="pagination-list">
                        <li><a class="pagination-link" href="' . $url . '1">1</a></li>
                        <li><span class="pagination-ellipsis">&hellip;</span>';
        }

        $ci = 0;
        for ($i = $pagina; $i <= $Npaginas; $i++) {
            if ($ci >= $botones) {
                break;
            }
            if ($pagina == $i) {
                $tabla .= '<li><a class="pagination-link is-current" href="' . $url . $i . '">' . $i . '</a></li>';
            } else {
                $tabla .= '<li><a class="pagination-link" href="' . $url . $i . '">' . $i . '</a></li>';
            }
            $ci++;
        }

        if ($pagina == $Npaginas) {
            $tabla .= '</ul><a class="pagination-next is-disabled" disabled>Siguiente</a>';
        } else {
            $tabla .= '<li><span class="pagination-ellipsis">&hellip;</span>
                        <li><a class="pagination-link" href="' . $url . $Npaginas . '">' . $Npaginas . '</a></li>
                        </ul><a class="pagination-next" href="' . $url . ($pagina + 1) . '">Siguiente</a>';
        }

        $tabla .= '</nav>';
        return $tabla;
    }
}
