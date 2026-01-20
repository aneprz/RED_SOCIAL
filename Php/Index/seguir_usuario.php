<?php
session_start();
if(!isset($_SESSION['id'])){
    echo json_encode(['error'=>'No session']);
    exit;
}

require '../../BD/conexiones.php'; // Ajusta la ruta a tu PDO

$usuario_id = $_SESSION['id'];
$seguir_id = intval($_POST['seguir_id'] ?? 0);

if($seguir_id === 0 || $seguir_id === $usuario_id){
    echo json_encode(['error'=>'ID inválido']);
    exit;
}

// Insertar seguimiento
$stmt = $pdo->prepare("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES (:seguidor, :seguido)");
$stmt->execute(['seguidor' => $usuario_id, 'seguido' => $seguir_id]);

echo json_encode(['success' => true]);
