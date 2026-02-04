<?php
require "../../../BD/conexiones.php";
session_start();

$idUsu = $_SESSION['id'];
$tipoChat = $_POST['tipo_chat'] ?? '';
$usuariosSeleccionados = $_POST['usuarios'] ?? [];
$nombreGrupo = $_POST['nombre_grupo'] ?? '';
$token = $_POST['chat_token'] ?? '';

// Verificar token anti-doble envío
if (!$token || $token !== ($_SESSION['nuevo_chat_token'] ?? '')) {
    die("Formulario inválido o ya enviado.");
}
unset($_SESSION['nuevo_chat_token']); // invalidar token para evitar reenvío

// Evitar duplicados en la selección de usuarios
$usuariosSeleccionados = array_unique($usuariosSeleccionados);

// Determinar si es grupo
$es_grupo = ($tipoChat === 'grupo') ? 1 : 0;

// Validaciones
if ($es_grupo === 0 && count($usuariosSeleccionados) !== 1) {
    die("Debes seleccionar exactamente un usuario para chat individual.");
}
if ($es_grupo === 1 && count($usuariosSeleccionados) < 1) {
    die("Debes seleccionar al menos un usuario para chat grupal.");
}

try {
    $pdo->beginTransaction();

    if ($es_grupo === 0) {
        // Chat individual
        $otroUsuario = $usuariosSeleccionados[0];

        // Verificar si ya existe chat individual
        $sql = $pdo->prepare("
            SELECT uc1.chat_id
            FROM usuarios_chat uc1
            JOIN usuarios_chat uc2 ON uc1.chat_id = uc2.chat_id
            JOIN chats c ON c.id = uc1.chat_id
            WHERE uc1.usuario_id = ? AND uc2.usuario_id = ? AND c.es_grupo = 0
            LIMIT 1
        ");
        $sql->execute([$idUsu, $otroUsuario]);
        $chatExistente = $sql->fetch(PDO::FETCH_ASSOC);

        if ($chatExistente) {
            $pdo->commit();
            header("Location: ../chats.php?chat_id=" . $chatExistente['chat_id']);
            exit;
        }

        // Crear chat individual
        $sql = $pdo->prepare("INSERT INTO chats (es_grupo, nombre_grupo) VALUES (0, NULL)");
        $sql->execute();
        $chat_id = $pdo->lastInsertId();

        // Asociar usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios_chat (chat_id, usuario_id) VALUES (?, ?)");
        $stmt->execute([$chat_id, $idUsu]);
        $stmt->execute([$chat_id, $otroUsuario]);

    } else {
        // Chat grupal
        // Quitar al creador si estaba incluido
        $usuariosSeleccionados = array_filter($usuariosSeleccionados, fn($u) => $u != $idUsu);

        // Incluir al creador y ordenar IDs
        $idsGrupo = $usuariosSeleccionados;
        $idsGrupo[] = $idUsu;
        sort($idsGrupo);

        // Buscar chats grupales existentes
        $sql = $pdo->prepare("
            SELECT c.id
            FROM chats c
            JOIN usuarios_chat uc ON c.id = uc.chat_id
            WHERE c.es_grupo = 1
            GROUP BY c.id
        ");
        $sql->execute();
        $chats = $sql->fetchAll(PDO::FETCH_ASSOC);

        $chatExistenteId = null;
        foreach ($chats as $chat) {
            $sql2 = $pdo->prepare("SELECT usuario_id FROM usuarios_chat WHERE chat_id = ? ORDER BY usuario_id");
            $sql2->execute([$chat['id']]);
            $usuariosChat = $sql2->fetchAll(PDO::FETCH_COLUMN);
            sort($usuariosChat);

            if ($usuariosChat === $idsGrupo) {
                $chatExistenteId = $chat['id'];
                break;
            }
        }

        if ($chatExistenteId) {
            $pdo->commit();
            header("Location: ../chats.php?chat_id=" . $chatExistenteId);
            exit;
        }

        // Crear chat grupal
        $sql = $pdo->prepare("INSERT INTO chats (es_grupo, nombre_grupo) VALUES (1, ?)");
        $sql->execute([$nombreGrupo]);
        $chat_id = $pdo->lastInsertId();

        // Insertar usuarios en el chat
        $stmt = $pdo->prepare("INSERT INTO usuarios_chat (chat_id, usuario_id) VALUES (?, ?)");
        foreach ($idsGrupo as $u_id) {
            $stmt->execute([$chat_id, $u_id]);
        }
    }

    $pdo->commit();
    header("Location: ../chats.php?chat_id=$chat_id");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error al crear el chat: " . $e->getMessage());
}
