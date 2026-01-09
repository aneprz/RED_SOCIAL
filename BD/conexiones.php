<?php
// Evitar múltiples inclusiones
if (!isset($pdo) && !isset($conexion)) {

    $host = "18.209.250.204";
    $db   = "salsagram_db";
    $user = "php";
    $pass = "";
    $charset = "utf8mb4";

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
        die("❌ Error de conexión PDO a la base de datos");
        // En desarrollo podrías usar:
        // die("Error PDO: " . $e->getMessage());
    }

    // ----------------------
    // Conexión MySQLi
    // ----------------------
    $conexion = mysqli_connect($host, $user, $pass, $db);

    if (!$conexion) {
        die("❌ Error de conexión MySQLi: " . mysqli_connect_error());
    }

}
?>
