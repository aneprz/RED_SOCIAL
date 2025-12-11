<?php
session_start();
include '../../BD/conexiones.php';

if (!isset($_SESSION['id'])) {
    echo "Error: no has iniciado sesión.";
    exit();
}

if (isset($_POST['id_usuario']) && isset($_POST['accion'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $accion = $_POST['accion'];
    $seguidor_id = $_SESSION['id'];

    if ($accion === 'suprimir') {
        $query = "DELETE FROM seguidores WHERE seguidor_id = $seguidor_id AND seguido_id = $id_usuario";
        if (!mysqli_query($conexion, $query)) {
            echo "Error al eliminar: " . mysqli_error($conexion);
            exit();
        }
        header("Location: tablaSeguidos.php");
        exit();

    } elseif ($accion === 'seguir') {
        $check = "SELECT * FROM seguidores WHERE seguidor_id = $seguidor_id AND seguido_id = $id_usuario";
        $res = mysqli_query($conexion, $check);
        if (!$res) {
            echo "Error en la consulta: " . mysqli_error($conexion);
            exit();
        }

        if (mysqli_num_rows($res) == 0) {
            $query = "INSERT INTO seguidores (seguidor_id, seguido_id) VALUES ($seguidor_id, $id_usuario)";
            if (!mysqli_query($conexion, $query)) {
                echo "Error al seguir: " . mysqli_error($conexion);
                exit();
            }
        }

        header("Location: tablaSeguidores.php");
        exit();
    } 
} else {
    echo "Datos incompletos.";
    exit();
}
?>
