<?php
session_start();
require_once "./config/conexao.php";

$empresa = null;
$mensagem = "";

if (isset($_GET["nome"]) && !empty(trim($_GET["nome"]))) {
    $nome_empresa_visualizar = trim($_GET["nome"]);
    
    $stmt = $conn->prepare("SELECT * FROM empresa WHERE nome = ?");
    $stmt->bind_param("s", $nome_empresa_visualizar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $empresa = $resultado->fetch_assoc();
    $stmt->close();
    
    if (!$empresa) {
        $mensagem = "<p style='color:red;text-align:center;'>❌ Empresa não encontrada ou o nome é inválido.</p>";
    }
    
} else {
    $mensagem = "<p style='color:red;text-align:center;'>❌ Nome da empresa não fornecido.</p>";
}

$candidato_logado = isset($_SESSION["usuario_id"]); 
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Perfil da Empresa: <?php echo $empresa ? htmlspecialchars($empresa['nome']) : 'Detalhes'; ?></title>
<link rel="stylesheet" href="css/empresa.css">
</head>
<body>
<header>
    <h2>Detalhes da Empresa</h2>
        <a href="vagas.php" class="btn-voltar">Voltar para Vagas</a> 
</header>

<main>
    <h1>Perfil de: <?php echo $empresa ? htmlspecialchars($empresa['nome']) : 'Empresa'; ?></h1>
    <?php echo $mensagem; ?>

    <?php if ($empresa): ?>
        <label>Razão Social</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['razao_social']); ?></div>

        <label>Nome Fantasia</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['nome']); ?></div>

        <label>CNPJ</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['cnpj']); ?></div>

        <label>Inscrição Estadual</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['inscricao_estadual']); ?></div>

        <label>Ano de Fundação</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['ano_fundacao']); ?></div>

        <label>Email Corporativo</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['email']); ?></div>

        <label>Telefone Comercial</label>
        <div class="dado-visualizacao"><?php echo htmlspecialchars($empresa['telefone_comercial']); ?></div>

        <label>Descrição</label>
        <div class="dado-visualizacao"><?php echo nl2br(htmlspecialchars($empresa['descricao'])); ?></div>
    
    <?php endif; ?>
</main>
</body>
</html>