<?php
require 'Procesamiento/procesamientos.php';

$fotos = obtenerFotos();

    session_start();
    if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Explorar</title>

<style>
    .grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 4px;
    }

    .grid img {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
    }
</style>
</head>
<body>

<div class="grid">
    <?php foreach ($fotos as $foto): ?>
        <img src="<?= htmlspecialchars($foto['imagen_url']) ?>" alt="foto">
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../Templates/navBar.php'; ?>
<?php require_once __DIR__ . '/../Templates/footer.php'; ?>

</body>
</html>
