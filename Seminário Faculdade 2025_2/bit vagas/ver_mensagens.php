<?php
session_start();
require_once "./config/conexao.php";

header("Content-Type: application/json");

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode([]); 
    exit;
}

$id_usuario = $_SESSION["usuario_id"];

$sql = "SELECT id, id_candidatura, id_empresa, mensagem, tipo, data_envio 
        FROM mensagens 
        WHERE id_usuario = ? 
        ORDER BY data_envio DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$mensagens = [];
while ($msg = $result->fetch_assoc()) {
    $mensagens[] = $msg;
}

error_log("Debug ID Usuário: $id_usuario");
error_log(print_r($mensagens, true));

echo json_encode($mensagens);

?>