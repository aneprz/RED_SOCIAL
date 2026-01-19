<?php
session_start();
if(!isset($_SESSION['id'])){
    echo json_encode(['error'=>'No session']);
    exit;
}

require __DIR__ . '/../../BD/conexiones.php'; // ruta a tu PDO

$post_id = intval($_GET['id'] ?? 0);
$usuario_id = intval($_SESSION['id']); // <-- Usuario actual

// Post
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

// ===== TOTAL DE LIKES =====
$stmtLikes = $pdo->prepare("SELECT COUNT(*) AS total FROM likes WHERE post_id = :id");
$stmtLikes->execute(['id' => $post_id]);
$total_likes = $stmtLikes->fetchColumn() ?? 0;

// ===== SI EL USUARIO YA DIO LIKE =====
$stmtUserLike = $pdo->prepare("SELECT COUNT(*) AS liked FROM likes WHERE post_id = :id AND usuario_id = :uid");
$stmtUserLike->execute(['id' => $post_id, 'uid' => $usuario_id]);
$liked = $stmtUserLike->fetchColumn() > 0;

// Comentarios con foto de perfil
$stmtC = $pdo->prepare("
    SELECT c.id, c.texto, u.username AS usuario, u.foto_perfil
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.post_id = :id
    ORDER BY c.id ASC
");
$stmtC->execute(['id'=>$post_id]);
$comentarios = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Ajustar ruta de la foto de perfil
foreach($comentarios as &$c){
    $c['foto_perfil'] = !empty($c['foto_perfil']) ? $c['foto_perfil'] : '/Media/foto_default.png';
}
unset($c);

$post['comentarios'] = $comentarios;

// 🔹 AGREGAR TOTAL DE LIKES Y SI EL USUARIO YA DIO LIKE
$post['total_likes'] = $total_likes;
$post['liked'] = $liked;

header('Content-Type: application/json');
echo json_encode($post);
