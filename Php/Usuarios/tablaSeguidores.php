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
                        $query = "SELECT foto_perfil, username, id FROM usuarios join seguidores on id=seguidor_id where $id = seguido_id";
                        $result = mysqli_query($conexion, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td><img src='" . htmlspecialchars($row['foto_perfil']) . "' width='50' alt='Foto de perfil'></td>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>
                                        <form method='post' action='procesarSeguidores.php'>
                                            <input type='hidden' name='id_usuario' value='" . $row['id'] . "'>
                                            <input type='hidden' name='accion' value='seguir'>
                                            <button type='submit'>Seguir</button>
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
