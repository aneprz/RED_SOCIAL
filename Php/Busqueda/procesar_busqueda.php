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

        // Borrar notificaciones asociadas
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
        // A. ¿Ya lo sigo?
        $check_seg = mysqli_query($conexion, "SELECT * FROM seguidores WHERE seguidor_id = $mi_id AND seguido_id = $id_destino");
        
        // B. ¿Ya hay una solicitud PENDIENTE?
        $check_sol = mysqli_query($conexion, "SELECT * FROM solicitudes_seguimiento WHERE solicitante_id = $mi_id AND receptor_id = $id_destino AND estado = 'pendiente'");

        if (mysqli_num_rows($check_seg) == 0 && mysqli_num_rows($check_sol) == 0) {
            
            // LIMPIEZA: Si había una solicitud vieja rechazada/aceptada, la borramos
            mysqli_query($conexion, "DELETE FROM solicitudes_seguimiento WHERE solicitante_id = $mi_id AND receptor_id = $id_destino");

            if ($es_privada == 1) {
                // CASO A: PRIVADA -> Crear Solicitud Pendiente
                // Intentamos insertar con fecha. Si falla, insertamos sin fecha.
                $sql = "INSERT INTO solicitudes_seguimiento (solicitante_id, receptor_id, estado, fecha) VALUES ($mi_id, $id_destino, 'pendiente', NOW())";
                
                if(!mysqli_query($conexion, $sql)){
                     // Fallback por si la tabla no tiene columna 'fecha'
                     $sql = "INSERT INTO solicitudes_seguimiento (solicitante_id, receptor_id, estado) VALUES ($mi_id, $id_destino, 'pendiente')";
                     mysqli_query($conexion, $sql);
                }

                // Notificación
                $sql_n = "INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES ($id_destino, $mi_id, 'follow_request', NOW())";
                mysqli_query($conexion, $sql_n);

            } else {
                // CASO B: PÚBLICA -> Seguir Directamente
                $sql = "INSERT INTO seguidores (seguidor_id, seguido_id, fecha) VALUES ($mi_id, $id_destino, NOW())";
                
                // Fallback por si la tabla seguidores no tiene 'fecha'
                if(!mysqli_query($conexion, $sql)){
                    $sql = "INSERT INTO seguidores (seguidor_id, seguido_id) VALUES ($mi_id, $id_destino)";
                    mysqli_query($conexion, $sql);
                }

                // Notificación de follow
                $sql_n = "INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES ($id_destino, $mi_id, 'follow', NOW())";
                mysqli_query($conexion, $sql_n);
            }
        }
    }
}

$busqueda_orig = isset($_POST['busqueda']) ? urlencode($_POST['busqueda']) : '';
header("Location: busqueda.php?busqueda=$busqueda_orig");
exit();
?>