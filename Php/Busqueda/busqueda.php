<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';
$id = intval($_SESSION['id']);

$busqueda = $_GET['busqueda'] ?? '';
// Proteger contra inyección SQL básica en el LIKE
$busqueda_safe = mysqli_real_escape_string($conexion, $busqueda); 
$busqueda_param = "%$busqueda_safe%";

// 1. Buscar Usuarios
$sql_users = "SELECT id, username, foto_perfil FROM usuarios WHERE id != $id AND username LIKE '$busqueda_param' ORDER BY username ASC";
$resultado = mysqli_query($conexion, $sql_users);

$usuarios = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $fila;
}

// 2. Obtener lista de a quién SIGO
$seguidores_actuales = [];
$res = mysqli_query($conexion, "SELECT seguido_id FROM seguidores WHERE seguidor_id = $id");
while ($row = mysqli_fetch_assoc($res)) {
    $seguidores_actuales[] = $row['seguido_id'];
}

// 3. Obtener lista de a quién he SOLICITADO (Pendientes)
$solicitados_actuales = [];
$res_sol = mysqli_query($conexion, "SELECT receptor_id FROM solicitudes_seguimiento WHERE solicitante_id = $id AND estado = 'pendiente'");
while ($row = mysqli_fetch_assoc($res_sol)) {
    $solicitados_actuales[] = $row['receptor_id'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Usuarios</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_busqueda.css">
    <link rel="icon" type="image/png" href="/Media/logo.png">
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
                        <td>
                            <form action="usuarioAjeno.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                <button type="submit" style="border:none; background:none; padding:0; cursor:pointer;">
                                    <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" width="50" alt="Foto de perfil">
                                </button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($usuario['username']) ?></td>
                        <td>
                            <form method="post" action="procesar_busqueda.php">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                <input type="hidden" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">

                                <?php if (in_array($usuario['id'], $seguidores_actuales)): ?>
                                    <input type="hidden" name="accion" value="suprimir">
                                    <button type="submit" class="btn-siguiendo">Siguiendo</button>

                                <?php elseif (in_array($usuario['id'], $solicitados_actuales)): ?>
                                    <input type="hidden" name="accion" value="suprimir">
                                    <button type="submit" class="btn-pendiente" style="background-color: #95a5a6;">Pendiente</button>

                                <?php else: ?>
                                    <input type="hidden" name="accion" value="seguir">
                                    <button type="submit" class="btn-seguir">Seguir</button>
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
