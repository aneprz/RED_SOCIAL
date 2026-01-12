<?php
// Evitar múltiples inclusiones
if (!isset($pdo) && !isset($conexion)) {

    $host = "18.209.250.204";
    $db   = "salsagram_db";
    $user = "php";
    $pass = "";
    $charset = "utf8mb4";

    // $host = "localhost";
    // $db   = "salsagram_db";
    // $user = "root";
    // $pass = "";
    // $charset = "utf8mb4";

    // ----------------------
    // Conexión PDO
    // ----------------------
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die("Error de conexión PDO a la base de datos");
    }

    // ----------------------
    // Conexión MySQLi
    // ----------------------
    $conexion = mysqli_connect($host, $user, $pass, $db);

    if (!$conexion) {
        die("Error de conexión MySQLi: " . mysqli_connect_error());
    }
}

// ----------------------
// Función utilitaria
// ----------------------
if (!function_exists('obtenerFotoPerfil')) {
    function obtenerFotoPerfil($usuarioId) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $usuarioId]);
        $row = $stmt->fetch();

        if ($row && !empty($row['foto_perfil'])) {
            return $row['foto_perfil'];
        } else {
            return '/Media/foto_default.png'; // ruta por defecto
        }
    }
}
?>
