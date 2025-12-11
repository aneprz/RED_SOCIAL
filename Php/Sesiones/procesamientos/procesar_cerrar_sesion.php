<?php
    session_start();
    session_unset(); // Elimina todas las variables de sesión
    session_destroy();
    header("location: ../inicio_sesion.php");

?>