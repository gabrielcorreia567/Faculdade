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
            $sql_update = "UPDATE vagas_estagio SET ativa = 0 WHERE id = ? AND empresa = ?";
            $stmt2 = $conn->prepare($sql_update);
            $stmt2->bind_param("is", $id_vaga, $empresa_nome);
            if ($stmt2->execute()) {
                header("Location: painel_empresa.php?msg=Vaga desativada com sucesso");
                exit;
            } else {
                echo "Erro ao desativar a vaga.";
            }
        } else {
            echo "Senha incorreta.";
        }
    } else {
        echo "Empresa não encontrada.";
    }
}
?>
