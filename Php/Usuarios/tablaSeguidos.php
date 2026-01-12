<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}
$id = intval($_SESSION['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Siguiendo</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_tablas_perfil.css">
</head>
<body>
<?php include __DIR__ . '/../Templates/navBar.php'; ?>

<div class="tabla-seguidores">
    <h2>Siguiendo</h2>
    <table>
        <tbody>
            <?php
            include '../../BD/conexiones.php';
            $query = "SELECT u.foto_perfil, u.username, u.id 
                      FROM usuarios u
                      JOIN seguidores s ON u.id = s.seguido_id
                      WHERE s.seguidor_id = $id";
            $result = mysqli_query($conexion, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $usuario_id = intval($row['id']);
                    echo "<tr>";
                    echo "<td><img src='" . htmlspecialchars($row['foto_perfil']) . "' width='50' alt='Foto de perfil'></td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>
                            <form method='post' action='procesarSeguidores.php'>
                                <input type='hidden' name='id_usuario' value='$usuario_id'>
                                <input type='hidden' name='accion' value='suprimir'>
                                <input type='hidden' name='pagina_origen' value='" . basename($_SERVER['PHP_SELF']) . "'>
                                <button type='submit'>Suprimir</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
