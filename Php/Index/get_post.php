<?php
session_start();
if(!isset($_SESSION['id'])){
    echo json_encode(['error'=>'No session']);
    exit;
}

require __DIR__ . '/../../BD/conexiones.php'; // ruta a tu PDO

$post_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.id, p.usuario_id, p.imagen_url, p.pie_foto, p.ubicacion, p.fecha_publicacion, p.sals,
           u.username, u.foto_perfil
    FROM publicaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.id = :id
");
$stmt->execute(['id'=>$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$post){
    echo json_encode(['error'=>'Post no encontrado']);
    exit;
}

// Comentarios
$stmtC = $pdo->prepare("
    SELECT c.id, c.texto, u.username AS usuario
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.post_id = :id
    ORDER BY c.id ASC
");
$stmtC->execute(['id'=>$post_id]);
$post['comentarios'] = $stmtC->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($post);
