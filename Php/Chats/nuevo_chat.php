<?php
require "../../BD/conexiones.php";
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
    <link rel="stylesheet" href="../../../Estilos/estilos_chats.css">
</head>
<body>
    <?php include __DIR__ . '../../../Php/Templates/navBar.php';?>
    <main>
        <div class="encabezadoNuevoChat">
            <a class="volver" href="chats.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/></svg></a>
            <h2>Crear Nuevo Chat</h2>
        </div>
        <form action="procesamientos/crear_chat.php" method="post">
            <label for="usuario">Selecciona un usuario:</label>
            <select name="usuario_id" id="usuario" required>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= $u['username'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Crear Chat</button>
        </form>
    </main>
    <?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>