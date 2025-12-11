<?php
require "procesamientos/conexionBBDD.php";
session_start();

$idUsu = $_SESSION['id'];

// Traer todos los usuarios excepto el actual
$sql = $pdo->prepare("SELECT id, username FROM usuarios WHERE id != :idUsu");
$sql->execute(["idUsu" => $idUsu]);
$usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo chat</title>
</head>
<body>
    <?php include __DIR__ . '../../../Php/Templates/navBar.php';?>

    <h2>Crear Nuevo Chat</h2>
    <form action="procesamientos/crear_chat.php" method="post">
        <label for="usuario">Selecciona un usuario:</label>
        <select name="usuario_id" id="usuario" required>
            <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>"><?= $u['username'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Crear Chat</button>
    </form>

    <?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>

