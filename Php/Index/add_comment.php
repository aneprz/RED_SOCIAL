<?php
session_start();
if(!isset($_SESSION['id'])) {
    echo json_encode(['success'=>false,'error'=>'No logueado']);
    exit;
}

require '../../BD/conexiones.php'; // PDO

$post_id = intval($_POST['post_id'] ?? 0);
$texto = trim($_POST['texto'] ?? '');
$usuario_id = $_SESSION['id'];

if($post_id <= 0 || $texto === ''){
    echo json_encode(['success'=>false,'error'=>'Datos inválidos']);
    exit;
}

// Insertar comentario
$stmt = $pdo->prepare("
    INSERT INTO comentarios (post_id, usuario_id, texto, fecha)
    VALUES (:post_id, :usuario_id, :texto, NOW())
");
$stmt->execute([
    'post_id' => $post_id,
    'usuario_id' => $usuario_id,
    'texto' => $texto
]);

$comment_id = $pdo->lastInsertId();

// Obtener usuario y foto EXACTAMENTE igual que get_post.php
$stmt2 = $pdo->prepare("
    SELECT u.username AS usuario, u.foto_perfil
    FROM usuarios u
    WHERE u.id = :id
");
$stmt2->execute(['id' => $usuario_id]);
$user = $stmt2->fetch(PDO::FETCH_ASSOC);

// Ajustar ruta de la foto (MISMA lógica)
$user['foto_perfil'] = !empty($user['foto_perfil'])
    ? $user['foto_perfil']
    : '/Media/foto_default.png';

echo json_encode([
    'success' => true,
    'comment_id' => $comment_id,
    'usuario' => $user['usuario'],
    'foto_perfil' => $user['foto_perfil']
]);
