<?php
require '../../BD/conexiones.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

$usuario_id = $_SESSION['id'] ?? null;
$q = $_GET['q'] ?? '';

if (!$usuario_id || strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $conexion->prepare("
    SELECT u.id, u.username, u.foto_perfil
    FROM seguidores s
    JOIN usuarios u ON u.id = s.seguido_id
    WHERE s.seguidor_id = ?
    AND u.username LIKE CONCAT(?, '%')
    LIMIT 10
");

$stmt->bind_param("is", $usuario_id, $q);
$stmt->execute();

$result = $stmt->get_result();
$usuarios = [];

while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
