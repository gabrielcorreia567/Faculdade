<?php
session_start();
require_once "./config/conexao.php";

$mensagem_status = "";
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["usuario_id"])) {

        $id_usuario      = $_SESSION["usuario_id"];
        $id_vaga         = filter_var(isset($_POST["id_vaga"]) ? $_POST["id_vaga"] : 0, FILTER_SANITIZE_NUMBER_INT); 
        
        $nome            = trim(isset($_POST["nome"]) ? $_POST["nome"] : '');
        $email           = trim(isset($_POST["email"]) ? $_POST["email"] : '');
        $idade           = filter_var(isset($_POST["idade"]) ? $_POST["idade"] : 0, FILTER_SANITIZE_NUMBER_INT);
        $deficiencia     = trim(isset($_POST["deficiencia"]) ? $_POST["deficiencia"] : '');
        $experiencia     = trim(isset($_POST["experiencia"]) ? $_POST["experiencia"] : '');
        $motivo          = trim(isset($_POST["motivo"]) ? $_POST["motivo"] : '');
        $personalidade   = trim(isset($_POST["personalidade"]) ? $_POST["personalidade"] : '');
        
        $linkedin        = filter_var(trim(isset($_POST["linkedin"]) ? $_POST["linkedin"] : ''), FILTER_SANITIZE_URL);
        $github          = filter_var(trim(isset($_POST["github"]) ? $_POST["github"] : ''), FILTER_SANITIZE_URL);

        if (empty($nome) || empty($email) || empty($motivo) || empty($idade) || empty($deficiencia)) {
            $mensagem_status = "<p class='erro'>Preencha todos os campos obrigatórios.</p>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $mensagem_status = "<p class='erro'>O email fornecido não é válido.</p>";
        } elseif ($linkedin && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
             $mensagem_status = "<p class='erro'>A URL do LinkedIn não é válida.</p>";
        } elseif ($github && !filter_var($github, FILTER_VALIDATE_URL)) {
             $mensagem_status = "<p class='erro'>A URL do GitHub não é válida.</p>";
        } else {
            
            $sql = "INSERT INTO candidaturas 
                    (id_usuario, id_vaga, nome, email, idade, deficiencia, experiencia, motivo, personalidade, linkedin, github, data_candidatura)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "iississssss",
                $id_usuario,
                $id_vaga,
                $nome,
                $email,
                $idade,
                $deficiencia,
                $experiencia,
                $motivo,
                $personalidade,
                $linkedin,
                $github
            );

            if ($stmt->execute()) {
                $mensagem_status = "<p class='sucesso'>✅ Candidatura enviada com sucesso!</p>";
            } else {
                error_log("Erro ao inserir candidatura: " . $stmt->error);
                $mensagem_status = "<p class='erro'>❌ Erro ao enviar candidatura. Tente novamente.</p>";
            }
            $stmt->close();
        }

    } else {
        $mensagem_status = "<p class='erro'>Você precisa estar logado para se candidatar.</p>";
    }
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    if (empty($mensagem_status)) {
        $mensagem_status = "<p class='erro'>Vaga inválida ou não encontrada.</p>";
    }
    $id_vaga_get = 0; 
} else {
    $id_vaga_get = (int) $_GET['id'];
}


$sql = "SELECT * FROM vagas_completa WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_vaga_get);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    if (empty($mensagem_status)) {
        $mensagem_status = "<p class='erro'>Vaga não encontrada.</p>";
    }
    $vaga = []; 
    $pode_exibir_conteudo = false;
} else {
    $vaga = $result->fetch_assoc();
    $pode_exibir_conteudo = true;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pode_exibir_conteudo ? htmlspecialchars($vaga['empresa'] . ' - Vaga Completa') : 'Vaga Não Encontrada'; ?></title>
    <link rel="stylesheet" href="css/vagas.css">
    <link rel="stylesheet" href="css/candidatar.css">
    <style>
header {
    background-color: #0a0f1c;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    border-bottom: 1px solid #1f2937;
}

.btn-voltar {
    background: linear-gradient(90deg, #007bff, #00b3ff);
    color: #fff;
    font-weight: bold;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
}

.btn-voltar:hover {
    background: linear-gradient(90deg, #00b3ff, #007bff);
    transform: scale(1.05);
}
</style>
</head>
<body>
    <header>
        <h1>BitVagas - Detalhes da Vaga</h1>
    <a href="vagas.php" class="btn-voltar">Voltar para as vagas</a>
    </header>
<main>
    <?php echo $mensagem_status; ?>
    
    <?php if ($pode_exibir_conteudo): ?>
    <div class="container">
        <h1><?php echo htmlspecialchars($vaga['titulo_vaga']); ?></h1>
        <div class="vaga-info">
            <div>
                <label>Empresa:</label>
                <p><?php echo htmlspecialchars($vaga['empresa']); ?></p>
            </div>
            <div>
                <label>Linguagem requisitada:</label>
                <p><?php echo htmlspecialchars($vaga['linguagem']); ?></p>
            </div>
            <div>
                <label>Tipo de contrato:</label>
                <p><?php echo htmlspecialchars($vaga['tipo_contrato']); ?></p>
            </div>
            <div>
                <label>Modalidade:</label>
                <p><?php echo htmlspecialchars($vaga['modalidade']); ?></p>
            </div>
            <div>
                <label>Localidade:</label>
                <p><?php echo htmlspecialchars($vaga['localidade']); ?></p>
            </div>
            <div>
                <label>Carga horária:</label>
                <p><?php echo htmlspecialchars($vaga['carga_horaria']); ?></p>
            </div>
            <div>
                <label>Remuneração:</label>
                <p><?php echo $vaga['remunerado'] ? "Sim" : "Não"; ?></p>
            </div>
            <?php if ($vaga['remunerado']): ?>
            <div>
                <label>Salário:</label>
                <p>R$ <?php echo number_format($vaga['salario'], 2, ',', '.'); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="descricao">
            <h2>Sobre a vaga</h2>
            <p><?php echo nl2br(htmlspecialchars($vaga['descricao_vaga'])); ?></p>

            <h2>Requisitos</h2>
            <p><?php echo nl2br(htmlspecialchars($vaga['requisitos'])); ?></p>

            <h2>Benefícios</h2>
            <p><?php echo nl2br(htmlspecialchars($vaga['beneficios'])); ?></p>
        </div>

        <div class="descricao">
            <h2>Informações da empresa</h2>
            <p><strong>Site:</strong> <a href="<?php echo htmlspecialchars($vaga['site_empresa']); ?>" target="_blank"><?php echo htmlspecialchars($vaga['site_empresa']); ?></a></p>
            <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($vaga['email_contato']); ?>"><?php echo htmlspecialchars($vaga['email_contato']); ?></a></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($vaga['telefone']); ?></p>
            <p><strong>Data de publicação:</strong> <?php echo date("d/m/Y H:i", strtotime($vaga['data_postagem'])); ?></p>
            <button class="btn-destaque"><a href="empresa.php?nome=<?php echo urlencode($vaga['empresa']); ?>" target="_blank">Visitar Site da Empresa</a></button>
        </div>

        <?php if (isset($_SESSION["usuario_id"])): ?>
        <div class="descricao">
            <h2> Entrevista Interativa</h2>
            <form method="POST" action="candidatar.php?id=<?php echo htmlspecialchars($vaga['id']); ?>" class="formulario-candidatura">
                <input type="hidden" name="id_vaga" value="<?php echo htmlspecialchars($vaga['id']); ?>">

                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required>

                <label for="email">Email para Contato</label>
                <input type="email" id="email" name="email" required>

                <label for="idade">Idade</label>
                <input type="number" id="idade" name="idade" min="15" max="70" required>

                <label for="deficiencia">Possui alguma deficiência?</label>
                <select id="deficiencia" name="deficiencia" required>
                    <option value="">Selecione</option>
                    <option value="Não">Não</option>
                    <option value="Sim">Sim</option>
                </select>

                <label for="experiencia">Você já teve alguma experiência profissional?</label>
                <textarea id="experiencia" name="experiencia" placeholder="Conte sobre seus estágios, freelas ou experiências pessoais com tecnologia..."></textarea>

                <label for="motivo">Por que você se interessou por esta vaga?</label>
                <textarea id="motivo" name="motivo" placeholder="Fale o que te motivou a se candidatar a essa oportunidade..." required></textarea>

                <label for="personalidade">Como você se descreveria em uma frase?</label>
                <input type="text" id="personalidade" name="personalidade" placeholder="Ex: Sou curioso e gosto de resolver problemas complexos.">

                <label for="linkedin">LinkedIn (opcional)</label>
                <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/seu-perfil">

                <label for="github">GitHub (opcional)</label>
                <input type="url" id="github" name="github" placeholder="https://github.com/seuusuario">

                <button type="submit">Enviar candidatura</button>
            </form>
        </div>
        <?php else: ?>
            <a href="login.php" class="btn-candidatar">Faça login para se candidatar</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

</body>
</html>