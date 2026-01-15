<?php
require '../../../BD/conexiones.php';

$post_id = intval($_GET['post_id']);
$last_id = intval($_GET['last_id']);

$res = $conexion->query("
  SELECT c.id, c.texto, u.username AS usuario
  FROM comentarios c
  JOIN usuarios u ON c.usuario_id = u.id
  WHERE c.post_id = $post_id AND c.id > $last_id
  ORDER BY c.id ASC
");

$comments = [];
while($row = $res->fetch_assoc()) $comments[] = $row;

echo json_encode($comments);
