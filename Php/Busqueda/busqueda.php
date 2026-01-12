<?php 
session_start();
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

include 'procesar_busqueda.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busqueda</title>
</head>
<body>
    <?php
// Ejemplo: $usuarios ya contiene los nombres de usuario menos el tuyo
// $usuarios = ['juan', 'maria', 'pedro', 'admin'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="usuarios.css">
</head>
<body>

<div class="table-container">
    <table class="user-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre de usuario</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $index => $nombre): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

</body>
</html>