<?php
require __DIR__ . "/../../BD/conexiones.php";


if (!isset($_GET['id'])) {
    echo 0;
    exit;
}

$idPost = (int) $_GET['id'];

$stmt = $conexion->prepare("
    SELECT COUNT(*) AS total
    FROM likes
    WHERE post_id = ?
");
$stmt->bind_param("i", $idPost);
$stmt->execute();

$total = $stmt->get_result()->fetch_assoc()['total'];

echo $total;
