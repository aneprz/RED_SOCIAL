<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil</title>
  <link rel="stylesheet" href="../../Estilos/estilos_perfil.css">
</head>
<body>
    <?php
      $nombreusu= $_SESSION['username'];
      echo $nombreusu;
    ?>
</body>
</html>

