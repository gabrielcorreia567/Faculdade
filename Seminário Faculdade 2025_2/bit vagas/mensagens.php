<?php
session_start();
require_once "./config/conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["status" => "error", "message" => "Usuário não logado."]);
    exit;
}

$id_usuario = $_SESSION["usuario_id"];

$sql = "SELECT id, mensagem, tipo, data_envio FROM mensagens 
        WHERE id_usuario = ? 
        AND TIMESTAMPDIFF(SECOND, data_envio, NOW()) <= 10 
        ORDER BY data_envio DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$mensagens = [];
while ($msg = $resultado->fetch_assoc()) {
    $mensagens[] = $msg;
}

echo json_encode($mensagens);
