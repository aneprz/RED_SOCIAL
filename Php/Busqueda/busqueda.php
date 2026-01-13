<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';
$id = intval($_SESSION['id']);

$busqueda = $_GET['busqueda'] ?? '';
$busqueda_param = "%$busqueda%";

$stmt = mysqli_prepare(
    $conexion,
    "SELECT id, username, foto_perfil FROM usuarios WHERE id != ? AND username LIKE ? ORDER BY username ASC"
);
mysqli_stmt_bind_param($stmt, "is", $id, $busqueda_param);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$usuarios = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $fila;
}

$seguidores_actuales = [];
$seguidores_query = "SELECT seguido_id FROM seguidores WHERE seguidor_id = $id";
$res = mysqli_query($conexion, $seguidores_query);
while ($row = mysqli_fetch_assoc($res)) {
    $seguidores_actuales[] = $row['seguido_id'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Usuarios</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_busqueda.css">
</head>
<body>
<?php include __DIR__ . '/../Templates/navBar.php'; ?>

<div class="tabla-seguidores">

    <!-- FORMULARIO DE BÚSQUEDA -->
    <form method="get" action="">
        <input type="text" name="busqueda" placeholder="Buscar usuario..." 
               value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Buscar</button>
    </form>

    <!-- TABLA DE RESULTADOS -->
    <table>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <a href="usuarioAjeno.php?id=<?= $usuario['id'] ?>"></a>
                            <td><img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" width="50" alt="Foto de perfil"></td>
                        </a>
                        <td><?= htmlspecialchars($usuario['username']) ?></td>
                        <td>
                            <form method="post" action="procesar_busqueda.php">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                <?php if (in_array($usuario['id'], $seguidores_actuales)): ?>
                                    <input type="hidden" name="accion" value="suprimir">
                                    <button type="submit">Dejar de seguir</button>
                                <?php else: ?>
                                    <input type="hidden" name="accion" value="seguir">
                                    <button type="submit">Seguir</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center;">No se encontraron usuarios</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
