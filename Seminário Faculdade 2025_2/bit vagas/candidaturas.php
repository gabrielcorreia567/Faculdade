<?php
session_start();
require_once "./config/conexao.php";

if (!isset($_SESSION["empresa_nome"])) {
    header("Location: login.php");
    exit;
}

$empresa_nome = $_SESSION["empresa_nome"];
$id_vaga = $_GET["id_vaga"] ?? null;

if (!$id_vaga) {
    header("Location: painel_empresa.php");
    exit;
}

$sql_vaga = "SELECT * FROM vagas_completa WHERE id = ? AND empresa = ?";
$stmt_vaga = $conn->prepare($sql_vaga);
$stmt_vaga->bind_param("is", $id_vaga, $empresa_nome);
$stmt_vaga->execute();
$result_vaga = $stmt_vaga->get_result();

if ($result_vaga->num_rows === 0) {
    echo "<p class='erro'>❌ Vaga não encontrada ou não pertence à sua empresa.</p>";
    exit;
}

$mostrar_detalhes = isset($_GET["id"]);
$id_candidatura = $_GET["id"] ?? null;

if ($mostrar_detalhes && $id_candidatura) {
    $sql = "SELECT * FROM candidaturas WHERE id = ? AND id_vaga = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_candidatura, $id_vaga);
    $stmt->execute();
    $candidatura = $stmt->get_result()->fetch_assoc();
} else {
    $sql = "SELECT id, nome, data_candidatura FROM candidaturas WHERE id_vaga = ? ORDER BY data_candidatura DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_vaga);
    $stmt->execute();
    $candidaturas = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Candidaturas</title>
    <link rel="stylesheet" href="css/painel_empresa.css">
</head>
<body>
<header>
    <nav>
        <h3>BitVagas</h3>
        <ul class="menu">
            <li><a href="painel_empresa.php">Voltar ao Painel</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Candidaturas da vaga #<?php echo htmlspecialchars($id_vaga); ?></h1>

    <?php if (!$mostrar_detalhes): ?>
        <?php if ($candidaturas->num_rows > 0): ?>
            <section class="vagas-listagem">
                <?php while ($cand = $candidaturas->fetch_assoc()): ?>
                    <article class="vaga-card">
                        <h2><?php echo htmlspecialchars($cand['nome']); ?></h2>
                        <p><strong>Data da candidatura:</strong> <?php echo date('d/m/Y H:i', strtotime($cand['data_candidatura'])); ?></p>
                        <br>
                        <a href="candidaturas.php?id_vaga=<?php echo $id_vaga; ?>&id=<?php echo $cand['id']; ?>" class="btn-editar">Ver informações</a>
                    </article>
                <?php endwhile; ?>
            </section>
        <?php else: ?>
            <p class="erro">Nenhuma candidatura encontrada.</p>
        <?php endif; ?>

    <?php else: ?>
        <?php if ($candidatura): ?>
            <article class="vaga-card">
                <h2><?php echo htmlspecialchars($candidatura['nome']); ?></h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($candidatura['email']); ?></p>
                <p><strong>Idade:</strong> <?php echo htmlspecialchars($candidatura['idade']); ?></p>
                <p><strong>Deficiência:</strong> <?php echo htmlspecialchars($candidatura['deficiencia']); ?></p>
                <p><strong>Experiência:</strong> <?php echo nl2br(htmlspecialchars($candidatura['experiencia'])); ?></p>
                <p><strong>Motivo:</strong> <?php echo nl2br(htmlspecialchars($candidatura['motivo'])); ?></p>
                <p><strong>Personalidade:</strong> <?php echo htmlspecialchars($candidatura['personalidade']); ?></p>
                <?php if (!empty($candidatura['linkedin'])): ?>
                    <p><strong>LinkedIn:</strong> <a href="<?php echo htmlspecialchars($candidatura['linkedin']); ?>" target="_blank">Ver Perfil</a></p>
                <?php endif; ?>
                <?php if (!empty($candidatura['github'])): ?>
                    <p><strong>GitHub:</strong> <a href="<?php echo htmlspecialchars($candidatura['github']); ?>" target="_blank">Ver Perfil</a></p>
                <?php endif; ?>
                <br>
                <div class="acoes">
                    <a href="candidaturas.php?id_vaga=<?php echo $id_vaga; ?>" class="btn-limpar">Voltar</a>
                    <button type="button" class="btn-destaque" onclick="mostrarMensagem('aceitar')">Aceitar</button>
                    <button type="button" class="btn-desativar" onclick="mostrarMensagem('rejeitar')">Rejeitar</button>
                </div>

                <form id="formMensagem" method="POST" action="enviar_mensagem.php" style="display:none; margin-top:20px;">
        <input type="hidden" name="id" value="<?php echo $candidatura['id']; ?>"> 
        <input type="hidden" name="tipo" id="tipo" value="">
        <label for="mensagem"><strong>Mensagem da Empresa:<strong></label>
        <textarea name="mensagem" id="mensagem" rows="4" placeholder="Escreva sua mensagem..."></textarea>

        <button type="submit" class="btn-destaque">Enviar Mensagem</button>
        <button type="button" class="btn-limpar" onclick="cancelarMensagem()">Cancelar</button>
            </form>

            </article>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script>
const empresa_nome = <?php echo json_encode($empresa_nome); ?>;

function mostrarMensagem(tipo) {
    const form = document.getElementById('formMensagem');
    const textarea = document.getElementById('mensagem');
    const campoTipo = document.getElementById('tipo');

    form.style.display = 'block';
    campoTipo.value = tipo;

    if (tipo === 'aceitar') {
        textarea.value = `Parabéns! Sua candidatura foi aceita. Nós da ${empresa_nome} Entraremos em contato em breve!`;
        textarea.readOnly = true;
    } else {
        textarea.value = "Não dessa vez, mas agradecemos seu interesse. Desejamos sucesso em sua busca por oportunidades! E Coletamos suas informações para uma futura oportunidade.";
        textarea.readOnly = false;
    }
}

function cancelarMensagem() {
    document.getElementById('formMensagem').style.display = 'none';
}


document.getElementById('formMensagem').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const resposta = await fetch('enviar_mensagem.php', {
        method: 'POST',
        body: formData
    });

    const dados = await resposta.json();

    const aviso = document.createElement('div');
    aviso.textContent = dados.mensagem;
    aviso.style.position = 'fixed';
    aviso.style.top = '20px';
    aviso.style.right = '20px';
    aviso.style.padding = '10px 15px';
    aviso.style.borderRadius = '8px';
    aviso.style.fontWeight = 'bold';
    aviso.style.color = '#fff';
    aviso.style.background = dados.status === 'sucesso' ? '#4CAF50' : '#e74c3c';
    document.body.appendChild(aviso);

    setTimeout(() => aviso.remove(), 4000);

    if (dados.status === 'sucesso') {
        e.target.style.display = 'none';
    }
});
</script>

</body>
</html>
