<?php
session_start();
require_once "./config/conexao.php";

if (!isset($_SESSION["empresa_nome"])) {
    header("Location: login.php");
    exit;
}

$empresa_nome = $_SESSION["empresa_nome"];
$id_empresa = $_SESSION["empresa_id"] ?? null;

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo_vaga = trim($_POST['titulo_vaga']);
    $descricao_vaga = trim($_POST['descricao_vaga']);
    $linguagem = trim($_POST['linguagem']);
    $requisitos = trim($_POST['requisitos']);
    $beneficios = trim($_POST['beneficios']);
    $tipo_contrato = $_POST['tipo_contrato'] ?? 'Estágio';
    $carga_horaria = trim($_POST['carga_horaria']);
    $localidade = trim($_POST['localidade']);
    $modalidade = $_POST['modalidade'] ?? 'Presencial';
    $remunerado = isset($_POST['remunerado']) ? 1 : 0;
    $salario = $remunerado ? floatval($_POST['salario']) : 0;

    if (!$titulo_vaga || !$descricao_vaga || !$linguagem || !$localidade) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        $sql = "INSERT INTO vagas_completa 
                (titulo_vaga, empresa, descricao_vaga, linguagem, requisitos, beneficios, tipo_contrato, carga_horaria, localidade, modalidade, remunerado, salario) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssd",
            $titulo_vaga,
            $empresa_nome,
            $descricao_vaga,
            $linguagem,
            $requisitos,
            $beneficios,
            $tipo_contrato,
            $carga_horaria,
            $localidade,
            $modalidade,
            $remunerado,
            $salario
        );

        if ($stmt->execute()) {
            $sucesso = "Vaga criada com sucesso!";
        } else {
            $erro = "Erro ao criar a vaga: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Vaga - BitVagas</title>
    <link rel="stylesheet" href="css/controle_vaga.css">
</head>
<body>
<header>
    <nav>
        <h3>BitVagas</h3>
        <ul class="menu">
            <li><a href="painel_empresa.php">Painel</a></li>
        </ul>
        <p>Olá, <?php echo htmlspecialchars($empresa_nome); ?>!</p>
    </nav>
</header>

<main>
    <h1>Criar Nova Vaga</h1>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <p class="sucesso"><?php echo htmlspecialchars($sucesso); ?></p>
    <?php endif; ?>

    <form method="POST" class="vaga-form">
        <label>Título da Vaga *</label>
        <input type="text" name="titulo_vaga" required>

        <label>Descrição *</label>
        <textarea name="descricao_vaga" rows="4" required></textarea>

        <label>Linguagem *</label>
        <input type="text" name="linguagem" required>

        <label>Requisitos</label>
        <textarea name="requisitos" rows="3"></textarea>

        <label>Benefícios</label>
        <textarea name="beneficios" rows="3"></textarea>

        <label>Tipo de Contrato</label>
        <select name="tipo_contrato">
            <option value="Estágio">Estágio</option>
            <option value="CLT">CLT</option>
            <option value="PJ">PJ</option>
            <option value="Temporário">Temporário</option>
        </select>

        <label>Carga Horária</label>
        <input type="text" name="carga_horaria">

        <label>Localidade *</label>
        <input type="text" name="localidade" required>

        <label>Modalidade</label>
        <select name="modalidade">
            <option value="Presencial">Presencial</option>
            <option value="Híbrido">Híbrido</option>
            <option value="Remoto">Remoto</option>
        </select>

        <label>
            <input type="checkbox" name="remunerado" id="remunerado"> Remunerado
        </label>

        <div id="salario-container" style="display:none;">
            <label>Salário</label>
            <input type="number" step="0.01" name="salario">
        </div>

        <button type="submit" class="btn-destaque" href="painel_empresa.php">Criar Vaga</button>
        <a href="painel_empresa.php" class="btn-limpar">Cancelar</a>
    </form>
</main>

<footer>
    <p>&copy; 2025 BitVagas - Todos os direitos reservados.</p>
</footer>

<script>
    const remuneradoCheckbox = document.getElementById('remunerado');
    const salarioContainer = document.getElementById('salario-container');

    remuneradoCheckbox.addEventListener('change', function() {
        if (this.checked) {
            salarioContainer.style.display = 'block';
        } else {
            salarioContainer.style.display = 'none';
            salarioContainer.querySelector('input').value = '';
        }
    });
</script>
</body>
</html>
