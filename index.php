<?php
session_start();

$nombreUsu=$_SESSION['username'];

if (isset($_SESSION['username'])) {
    echo "Hola, " . $_SESSION['username'] . ". Aquí va lo exclusivo para iniciados.";
} else {
    echo "Contenido para forasteros. Inicia sesión si quieres ver lo bueno.";
}


?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="/Estilos/estilos.css">
    <link rel="icon" type="image/png" href="/Media/logo.png">
  </head>
  <body>
    <?php include __DIR__ . '/Php/Templates/navBar.php';?>
    <?php include __DIR__ . '/Php/Templates/footer.php';?>
  </body>
</html>
