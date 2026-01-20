<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

if (isset($_POST['id_usuario']) && isset($_POST['accion'])) {
    $id_destino = intval($_POST['id_usuario']);
    $accion = $_POST['accion'];
    $mi_id = intval($_SESSION['id']);

    if ($id_destino === $mi_id) {
        $busqueda_orig = isset($_POST['busqueda']) ? urlencode($_POST['busqueda']) : '';
        header("Location: busqueda.php?busqueda=$busqueda_orig");
        exit();
    }

    if ($accion === 'suprimir') {
        // --- DEJAR DE SEGUIR / CANCELAR SOLICITUD ---
        $query = "DELETE FROM seguidores WHERE seguidor_id = $mi_id AND seguido_id = $id_destino";
        mysqli_query($conexion, $query);

        $query_sol = "DELETE FROM solicitudes_seguimiento WHERE solicitante_id = $mi_id AND receptor_id = $id_destino";
        mysqli_query($conexion, $query_sol);

        // CORRECCIÓN AQUÍ: Usamos 'follow_request' para borrar, no 'solicitud'
        $query_notif = "DELETE FROM notificaciones WHERE id_usuario = $id_destino AND id_emisor = $mi_id AND tipo = 'follow_request'";
        mysqli_query($conexion, $query_notif);
        
        $query_notif_follow = "DELETE FROM notificaciones WHERE id_usuario = $id_destino AND id_emisor = $mi_id AND tipo = 'follow'";
        mysqli_query($conexion, $query_notif_follow);

    } elseif ($accion === 'seguir') {
        // --- INTENTAR SEGUIR ---

        // 1. Verificar privacidad
        $consulta_priv = mysqli_query($conexion, "SELECT privacidad FROM usuarios WHERE id = $id_destino");
        $row_priv = mysqli_fetch_assoc($consulta_priv);
        $es_privada = isset($row_priv['privacidad']) ? $row_priv['privacidad'] : 0;

        // 2. Verificar existencia
        $check_seg = mysqli_query($conexion, "SELECT * FROM seguidores WHERE seguidor_id = $mi_id AND seguido_id = $id_destino");
        $check_sol = mysqli_query($conexion, "SELECT * FROM solicitudes_seguimiento WHERE solicitante_id = $mi_id AND receptor_id = $id_destino");

        if (mysqli_num_rows($check_seg) == 0 && mysqli_num_rows($check_sol) == 0) {
            
            if ($es_privada == 1) {
                // CASO A: PRIVADA -> Solicitud
                $sql = "INSERT INTO solicitudes_seguimiento (solicitante_id, receptor_id, estado) VALUES ($mi_id, $id_destino, 'pendiente')";
                if(mysqli_query($conexion, $sql)){
                    // CORRECCIÓN AQUÍ: 'follow_request' en vez de 'solicitud'
                    $sql_n = "INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES ($id_destino, $mi_id, 'follow_request', NOW())";
                    mysqli_query($conexion, $sql_n);
                }

            } else {
                // CASO B: PÚBLICA -> Seguir
                $sql = "INSERT INTO seguidores (seguidor_id, seguido_id, fecha) VALUES ($mi_id, $id_destino, NOW())";
                if(mysqli_query($conexion, $sql)){
                    $sql_n = "INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES ($id_destino, $mi_id, 'follow', NOW())";
                    mysqli_query($conexion, $sql_n);
                }
            }
        }
    }
}

$busqueda_orig = isset($_POST['busqueda']) ? urlencode($_POST['busqueda']) : '';
header("Location: busqueda.php?busqueda=$busqueda_orig");
exit();
?>