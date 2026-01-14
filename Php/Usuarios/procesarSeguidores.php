<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

if (isset($_POST['id_usuario'], $_POST['accion'], $_POST['pagina_origen'])) {

    $id_usuario = intval($_POST['id_usuario']);
    $accion = $_POST['accion'];
    $mi_id = intval($_SESSION['id']);
    $pagina_origen = $_POST['pagina_origen'];

    if ($accion === 'quitar') {
        $query = "DELETE FROM seguidores 
                  WHERE seguidor_id = $id_usuario 
                  AND seguido_id = $mi_id";
        mysqli_query($conexion, $query);
    }

    if ($accion === 'suprimir') {
        $query = "DELETE FROM seguidores 
                  WHERE seguidor_id = $mi_id 
                  AND seguido_id = $id_usuario";
        mysqli_query($conexion, $query);
    }

    header("Location: $pagina_origen");
    exit();
}
