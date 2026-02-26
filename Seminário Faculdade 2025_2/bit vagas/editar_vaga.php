<?php
session_start();
require_once "./config/conexao.php";

if (!isset($_SESSION["empresa_nome"])) {
    header("Location: login.php");
    exit;
}

$empresa_nome = $_SESSION["empresa_nome"];
$id_empresa = $_SESSION["empresa_id"] ?? null;

$id_vaga = $_GET['id'] ?? null;

if (!$id_vaga) {
    header("Location: painel_empresa.php");
    exit;
}

$erro = "";
$sucesso = "";

$stmt = $conn->prepare("SELECT * FROM vagas_completa WHERE id = ? AND empresa = ? LIMIT 1");
$stmt->bind_param("is", $id_vaga, $empresa_nome);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: painel_empresa.php");
    exit;
}

$vaga = $resultado->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo_vaga = trim($_POST['titulo_vaga'] ?? '');
    $descricao_vaga = trim($_POST['descricao_vaga'] ?? '');
    $linguagem = trim($_POST['linguagem'] ?? '');
    $requisitos = trim($_POST['requisitos'] ?? '');
    $beneficios = trim($_POST['beneficios'] ?? '');
    $tipo_contrato = $_POST['tipo_contrato'] ?? 'Estágio';
    $carga_horaria = trim($_POST['carga_horaria'] ?? '');
    $localidade = trim($_POST['localidade'] ?? '');
    $modalidade = $_POST['modalidade'] ?? 'Presencial';
    $remunerado = isset($_POST['remunerado']) ? 1 : 0;
    $salario = $remunerado ? floatval($_POST['salario']) : 0;
    $email_contato = trim($_POST['email_contato'] ?? '');
    $site_empresa = trim($_POST['site_empresa'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if (empty($titulo_vaga) || empty($descricao_vaga) || empty($linguagem) || empty($localidade)) {
        $erro = "Preencha todos os campos obrigatórios!";
    } else {
        $sql = "UPDATE vagas_completa SET 
                    titulo_vaga = ?, descricao_vaga = ?, linguagem = ?, requisitos = ?, beneficios = ?, 
                    tipo_contrato = ?, carga_horaria = ?, localidade = ?, modalidade = ?, 
                    remunerado = ?, salario = ?, email_contato = ?, site_empresa = ?, telefone = ?
                WHERE id = ? AND empresa = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $erro = "Erro ao preparar a query. Causa: " . $conn->error; 
} else {
        $stmt->bind_param(
            "sssssssssidsssis",
            $titulo_vaga,
            $descricao_vaga,
            $linguagem,
            $requisitos,
            $beneficios,
            $tipo_contrato,
            $carga_horaria,
            $localidade,
            $modalidade,
            $remunerado,
            $salario,
            $email_contato,
            $site_empresa,
            $telefone,
            $id_vaga,
            $empresa_nome
        );

        if ($stmt->execute()) {
            $sucesso = "Vaga atualizada com sucesso!";
            $vaga = array_merge($vaga, $_POST);
        } else {
            $erro = "Erro ao atualizar a vaga. Tente novamente.";
        }
      }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Vaga - BitVagas</title>
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
        <h1>Editar Vaga</h1>

        <?php if ($erro) echo "<p class='erro'>$erro</p>"; ?>
        <?php if ($sucesso) echo "<p class='sucesso'>$sucesso</p>"; ?>

        <form method="POST" class="vaga-form">
            <label for="titulo_vaga">Título da Vaga*:</label>
            <input type="text" id="titulo_vaga" name="titulo_vaga" required value="<?= htmlspecialchars($vaga['titulo_vaga']) ?>">

            <label for="descricao_vaga">Descrição*:</label>
            <textarea id="descricao_vaga" name="descricao_vaga" required><?= htmlspecialchars($vaga['descricao_vaga']) ?></textarea>

            <label for="linguagem">Linguagem*:</label>
            <input type="text" id="linguagem" name="linguagem" required value="<?= htmlspecialchars($vaga['linguagem']) ?>">

            <label for="requisitos">Requisitos:</label>
            <textarea id="requisitos" name="requisitos"><?= htmlspecialchars($vaga['requisitos']) ?></textarea>

            <label for="beneficios">Benefícios:</label>
            <textarea id="beneficios" name="beneficios"><?= htmlspecialchars($vaga['beneficios']) ?></textarea>

            <label for="tipo_contrato">Tipo de Contrato:</label>
            <select id="tipo_contrato" name="tipo_contrato">
                <?php
                $tipos = ['Estágio', 'CLT', 'PJ', 'Temporário'];
                foreach ($tipos as $tipo) {
                    $selected = ($vaga['tipo_contrato'] === $tipo) ? "selected" : "";
                    echo "<option value='$tipo' $selected>$tipo</option>";
                }
                ?>
            </select>

            <label for="carga_horaria">Carga Horária:</label>
            <input type="text" id="carga_horaria" name="carga_horaria" value="<?= htmlspecialchars($vaga['carga_horaria']) ?>">

            <label for="localidade">Localidade*:</label>
            <input type="text" id="localidade" name="localidade" required value="<?= htmlspecialchars($vaga['localidade']) ?>">

            <label for="modalidade">Modalidade:</label>
            <select id="modalidade" name="modalidade">
                <?php
                $modalidades = ['Presencial', 'Híbrido', 'Remoto'];
                foreach ($modalidades as $mod) {
                    $selected = ($vaga['modalidade'] === $mod) ? "selected" : "";
                    echo "<option value='$mod' $selected>$mod</option>";
                }
                ?>
            </select>

            <label>
                <input type="checkbox" name="remunerado" value="1" <?= $vaga['remunerado'] ? "checked" : "" ?>>
                Remunerado
            </label>

            <label for="salario">Salário:</label>
            <input type="number" step="0.01" id="salario" name="salario" value="<?= htmlspecialchars($vaga['salario']) ?>">

            <label for="email_contato">Email de Contato:</label>
            <input type="email" id="email_contato" name="email_contato" value="<?= htmlspecialchars($vaga['email_contato']) ?>">

            <label for="site_empresa">Site da Empresa:</label>
            <input type="url" id="site_empresa" name="site_empresa" value="<?= htmlspecialchars($vaga['site_empresa']) ?>">

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($vaga['telefone']) ?>">

            <button type="submit" class="btn-destaque">Atualizar Vaga</button>
            <a href="painel_empresa.php" class="btn-limpar">Voltar</a>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 BitVagas - Todos os direitos reservados.</p>
    </footer>
</body>
</html>
