<?php
session_start();
include '../../BD/conexiones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$id = $_SESSION['id'] ?? '';
if (!$id) {
    return;
}

$foto_perfil = $_POST['foto_perfil'] ?? '';
$nuevousu  = $_POST['nuevousu'] ?? '';
$biografia = $_POST['biografia'] ?? '';


/* ========= VALIDAR USUARIO ========= */
if (!empty($nuevousu)) {

    $nuevousu = mysqli_real_escape_string($conexion, $nuevousu);

    $consulta = mysqli_query(
        $conexion,
        "SELECT id FROM usuarios WHERE username='$nuevousu' AND id != $id"
    );

    if (mysqli_num_rows($consulta) > 0) {
        echo "<script>
            alert('Nombre en uso');
            history.back();
        </script>";
        exit;
    }

    mysqli_query(
        $conexion,
        "UPDATE usuarios SET username='$nuevousu' WHERE id=$id"
    );

    $_SESSION['username'] = $nuevousu;
}


/* ========= BIOGRAFÍA ========= */
if (!empty($biografia)) {
    $biografia = mysqli_real_escape_string($conexion, $biografia);
    $biografia = mb_substr($biografia, 0, 150, 'UTF-8');

    mysqli_query(
        $conexion,
        "UPDATE usuarios SET bio='$biografia' WHERE id=$id"
    );

    $_SESSION['biografia'] = $biografia;
}


/* ========= FOTO PERFIL ========= */
if (!empty($foto_perfil)) {
    if (filter_var($foto_perfil, FILTER_VALIDATE_URL)) {

        $foto_perfil = mysqli_real_escape_string($conexion, $foto_perfil);

        mysqli_query(
            $conexion,
            "UPDATE usuarios SET foto_perfil='$foto_perfil' WHERE id=$id"
        );

        $_SESSION['foto_perfil'] = $foto_perfil;

    } else {
        echo "<script>
            alert('Imagen no válida');
            history.back();
        </script>";
        exit;
    }
}

header("Location: perfil.php");
exit;
