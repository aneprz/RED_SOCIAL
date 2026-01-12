<?php 
session_start();
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

include 'procesar_busqueda.php';
?>
