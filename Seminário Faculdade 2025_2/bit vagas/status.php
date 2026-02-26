<?php
session_start();
require_once "./config/conexao.php";

if (!isset($_SESSION["empresa_nome"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_vaga = intval($_POST["id_vaga"]);
    $senha = $_POST["senha"];
    $empresa_nome = $_SESSION["empresa_nome"];

    $sql_verificar = "SELECT senha FROM empresa WHERE nome = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("s", $empresa_nome);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $dados = $resultado->fetch_assoc();

        if (password_verify($senha, $dados["senha"])) {
            $sql_status = "SELECT ativa FROM vagas_completa WHERE id = ? AND empresa = ?";
            $stmt_status = $conn->prepare($sql_status);
            $stmt_status->bind_param("is", $id_vaga, $empresa_nome);
            $stmt_status->execute();
            $resultado_status = $stmt_status->get_result();

            if ($resultado_status->num_rows === 1) {
                $vaga = $resultado_status->fetch_assoc();
                $nova_atividade = $vaga["ativa"] ? 0 : 1;

                $sql_update = "UPDATE vagas_completa SET ativa = ? WHERE id = ? AND empresa = ?";
                $stmt2 = $conn->prepare($sql_update);
                $stmt2->bind_param("iis", $nova_atividade, $id_vaga, $empresa_nome);

                if ($stmt2->execute()) {
                    $msg = $nova_atividade ? "Vaga ativada com sucesso" : "Vaga desativada com sucesso";
                    header("Location: painel_empresa.php?msg=" . urlencode($msg));
                    exit;
                } else {
                    echo "Erro ao atualizar o status da vaga.";
                }
            } else {
                echo "Vaga não encontrada.";
            }
        } else {
            echo "Senha incorreta.";
        }
    } else {
        echo "Empresa não encontrada.";
    }
}
?>
