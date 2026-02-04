<?php

session_start();
include '../../BD/conexiones.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Datos insuficientes']);
    exit;
}

$mi_id = $_SESSION['id'];
$post_id = intval($_POST['post_id']);

// 1. Verificar que la publicación existe y es mía
$stmt = $conexion->prepare("SELECT imagen_url FROM publicaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $post_id, $mi_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    $imagen = $row['imagen_url'];

    // 2. Eliminar de la Base de Datos
    $stmtDelete = $conexion->prepare("DELETE FROM publicaciones WHERE id = ?");
    $stmtDelete->bind_param("i", $post_id);

    if ($stmtDelete->execute()) {
        // 3. Eliminar el archivo físico de la carpeta uploads
        $ruta_archivo = __DIR__ . '/../Crear/uploads/' . $imagen;
        
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al borrar en BD']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Publicación no encontrada o sin permiso']);
}
?>