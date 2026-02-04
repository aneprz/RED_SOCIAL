<?php
session_start();
if(!isset($_SESSION['id'])) exit;

include __DIR__ . '/../../BD/conexiones.php';

$user_id = (int)$_SESSION['id'];
$post_id = (int)$_POST['post_id'];

$stmt = $pdo->prepare("SELECT 1 FROM likes WHERE post_id = :p AND usuario_id = :u");
$stmt->execute(['p'=>$post_id,'u'=>$user_id]);

if($stmt->fetch()){
    $pdo->prepare("DELETE FROM likes WHERE post_id = :p AND usuario_id = :u")
        ->execute(['p'=>$post_id,'u'=>$user_id]);
    $liked = false;
} else {
    $pdo->prepare("INSERT INTO likes (post_id, usuario_id, fecha) VALUES (:p,:u,NOW())")
        ->execute(['p'=>$post_id,'u'=>$user_id]);
    $liked = true;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :p");
$stmt->execute(['p'=>$post_id]);
$total = $stmt->fetchColumn();

echo json_encode([
    'liked' => $liked,
    'total' => $total
]);
