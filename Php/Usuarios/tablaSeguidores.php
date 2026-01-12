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
    <title>Seguidores</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_tablas_perfil.css">
</head>
<body>
<?php include __DIR__ . '/../Templates/navBar.php'; ?>

<div class="tabla-seguidores">
    <h2>Seguidores</h2>
    <table>
        <tbody>
            <?php
            include '../../BD/conexiones.php';
            
            $query = "SELECT u.foto_perfil, u.username, u.id 
                      FROM usuarios u
                      JOIN seguidores s ON u.id = s.seguidor_id
                      WHERE s.seguido_id = $id";
            $result = mysqli_query($conexion, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $usuario_id = intval($row['id']);
                    $check = "SELECT 1 FROM seguidores WHERE seguidor_id = $id AND seguido_id = $usuario_id";
                    $res_check = mysqli_query($conexion, $check);
                    $accion = (mysqli_num_rows($res_check) > 0) ? 'suprimir' : 'seguir';
                    $texto_boton = ($accion === 'suprimir') ? 'Suprimir' : 'Seguir';

                    echo "<tr>";
                    echo "<td><img src='" . htmlspecialchars($row['foto_perfil']) . "' width='50' alt='Foto de perfil'></td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>
                            <form method='post' action='procesarSeguidores.php'>
                                <input type='hidden' name='id_usuario' value='$usuario_id'>
                                <input type='hidden' name='accion' value='$accion'>
                                <input type='hidden' name='pagina_origen' value='" . basename($_SERVER['PHP_SELF']) . "'>
                                <button type='submit'>$texto_boton</button>
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
