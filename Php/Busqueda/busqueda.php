<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

$id = $_SESSION['id'] ?? 0;

// Obtener término de búsqueda si existe
$busqueda = $_GET['busqueda'] ?? '';

// Consulta segura con prepared statements
$stmt = mysqli_prepare(
    $conexion,
    "SELECT username FROM usuarios WHERE id != ? AND username LIKE ? ORDER BY username ASC"
);

$busqueda_param = "%$busqueda%";
mysqli_stmt_bind_param($stmt, "is", $id, $busqueda_param);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$usuarios = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $fila['username'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Usuarios</title>
    <link rel="stylesheet" href="../../Estilos/estilos_busqueda.css">
</head>
<body>

<?php include __DIR__ . '/../Templates/navBar.php';?>

<div class="table-container">

    <!-- BUSCADOR -->
    <form method="get" action="">
        <input 
            type="text" 
            name="busqueda" 
            placeholder="Buscar usuario..." 
            value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Buscar</button>
    </form>

    <!-- TABLA DE RESULTADOS -->
    <table class="user-table">
        <thead>
        </thead>
        <tbody>
            <?php if(!empty($usuarios)): ?>
                <?php foreach($usuarios as $index => $nombre): ?>
                    <tr>
                        <td> </td>
                        <td><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" style="text-align:center;">No se encontraron usuarios</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php include __DIR__ . '/../Templates/footer.php';?>
</body>
</html>
