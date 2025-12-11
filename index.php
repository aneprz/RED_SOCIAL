<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="/Estilos/estilos.css">
  </head>
  <body>
    <?php include __DIR__ . '/Php/Templates/navBar.php';?>
    <?php include __DIR__ . '/Php/Templates/footer.php';?>
  </body>
</html>
