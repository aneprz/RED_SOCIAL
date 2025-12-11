<?php
    session_start();
    $id = intval($_SESSION['id']);
// Simulamos algunos seguidores
$seguidores = [
    ['username' => 'juan123', 'foto' => 'fotos/juan.jpg'],
    ['username' => 'maria88', 'foto' => 'fotos/maria.jpg'],
    ['username' => 'pepe_rock', 'foto' => 'fotos/pepe.jpg'],
    ['username' => 'luisita', 'foto' => 'fotos/luisita.jpg'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguidores</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_tablas_perfil.css">
</head>
<body>
    <div class="tabla-seguidores">
        <h2>Seguidores</h2>
        <table>
            <tbody>
                    <?php
                        include '../../BD/conexiones.php';
                        $query = "SELECT foto_perfil, username FROM usuarios join seguidores on id=seguidor_id where $id = seguido_id";
                        $result = mysqli_query($conexion, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['foto_perfil']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td><button>Seguir</button></td>";
                                echo "</tr>";
                            }
                        }
                    ?>
                </tbody>
        </table>
    </div>
</body>
</html>
