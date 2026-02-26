<?php
session_start();
require_once "./config/conexao.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_SESSION["empresa_id"])) {
        echo json_encode(["status" => "erro", "mensagem" => "Acesso negado."]);
        exit;
    }

    $id_empresa = $_SESSION["empresa_id"];
    $id_candidatura = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $mensagem = trim($_POST["mensagem"] ?? "");
    $tipo = $_POST["tipo"] ?? "info";


    $sqlCandidatura = "SELECT id_usuario FROM candidaturas WHERE id = ?";
    $stmtCandidatura = $conn->prepare($sqlCandidatura);
    $stmtCandidatura->bind_param("i", $id_candidatura);
    $stmtCandidatura->execute();
    $resultado = $stmtCandidatura->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["status" => "erro", "mensagem" => "Candidatura não encontrada."]);
        exit;
    }

    $row = $resultado->fetch_assoc();
    $id_usuario = $row["id_usuario"];

    $sql = "INSERT INTO mensagens (id_candidatura, id_usuario, id_empresa, tipo, mensagem) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $id_candidatura, $id_usuario, $id_empresa, $tipo, $mensagem);

    if ($stmt->execute()) {
        echo json_encode(["status" => "sucesso", "mensagem" => "Mensagem enviada com sucesso!"]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao enviar mensagem."]);
    }
}
?>

