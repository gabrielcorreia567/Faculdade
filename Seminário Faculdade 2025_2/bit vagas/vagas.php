<?php
session_start();
require_once "./config/conexao.php";

$filtro_empresa = isset($_GET['empresa']) ? "%" . trim($_GET['empresa']) . "%" : "";
$filtro_linguagem = isset($_GET['linguagem']) ? "%" . trim($_GET['linguagem']) . "%" : "";
$filtro_localidade = isset($_GET['localidade']) ? "%" . trim($_GET['localidade']) . "%" : "";
$filtro_remunerado = isset($_GET['remunerado']) ? $_GET['remunerado'] : "";

$sql = "SELECT * FROM vagas_completa WHERE ativa = 1";
$params = [];
$types = "";

if ($filtro_empresa) {
    $sql .= " AND empresa LIKE ?";
    $params[] = $filtro_empresa;
    $types .= "s";
}

if ($filtro_linguagem) {
    $sql .= " AND linguagem LIKE ?";
    $params[] = $filtro_linguagem;
    $types .= "s";
}

if ($filtro_localidade) {
    $sql .= " AND localidade LIKE ?";
    $params[] = $filtro_localidade;
    $types .= "s";
}

if ($filtro_remunerado !== "") {
    $sql .= " AND remunerado = ?";
    $params[] = (bool)$filtro_remunerado;
    $types .= "i";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado_vagas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Vagas de Estágio - BitVagas</title>
<link rel="stylesheet" href="css/vagas.css">
<style>
.notificacao {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #222;
    color: #fff;
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s ease;
    z-index: 9999;
    width: 300px;
    font-family: 'Poppins', sans-serif;
}
.notificacao.show { opacity: 1; transform: translateY(0); }
.notificacao .titulo { font-weight: bold; margin-bottom: 5px; font-size: 1rem; }
.notificacao .mensagem { font-size: 0.9rem; line-height: 1.4; }
.notificacao.aceite { background: #007f3f; }
.notificacao.rejeicao { background: #a40000; }
.notificacao.info { background: #004aad; }

#btnNotificacoes {
    position: fixed;
    bottom: 25px;
    left: 25px;
    background: #004aad;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 26px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    transition: 0.3s;
    z-index: 9998;
}
#btnNotificacoes:hover { background: #0070ff; transform: scale(1.05); }

#painelMensagens {
    position: fixed;
    bottom: 100px;
    left: 25px;
    width: 340px;
    max-height: 400px;
    background: #111;
    color: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    overflow-y: auto;
    transform: translateY(20px);
    opacity: 0;
    pointer-events: none;
    transition: all 0.4s ease;
    font-family: 'Poppins', sans-serif;
    padding: 10px;
}
#painelMensagens.show {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
.mensagem-card {
    background: #1e1e1e;
    margin-bottom: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    box-shadow: inset 0 0 5px rgba(255,255,255,0.1);
}
.mensagem-card.aceite { border-left: 5px solid #00c853; }
.mensagem-card.rejeicao { border-left: 5px solid #d32f2f; }
.mensagem-card.outro { border-left: 5px solid #2979ff; }
.mensagem-card small { display: block; color: #aaa; font-size: 0.75rem; margin-top: 4px; }
</style>
</head>
<body>
<header>
    <nav>
        <h3>BitVagas</h3>
        <ul class="menu">
            <li><a href="index.html">Home</a></li>
        </ul>
        <?php if (isset($_SESSION["usuario_nome"])): ?>
            <a href="perfil.php" class="btn-destaque">Olá, <?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?>!</a>
        <?php else: ?>
            <a href="login.php" class="btn-destaque">Perfil</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <h1>Buscar Vagas de Estágio</h1>

    <section class="filtro-container">
        <form method="GET" class="filtro-form">
            <input type="text" name="empresa" placeholder="Empresa" 
                value="<?php echo isset($_GET['empresa']) ? htmlspecialchars(trim($_GET['empresa'])) : ''; ?>">
            <input type="text" name="linguagem" placeholder="Linguagem de Programação" 
                value="<?php echo isset($_GET['linguagem']) ? htmlspecialchars(trim($_GET['linguagem'])) : ''; ?>">
            <input type="text" name="localidade" placeholder="Localidade" 
                value="<?php echo isset($_GET['localidade']) ? htmlspecialchars(trim($_GET['localidade'])) : ''; ?>">
            <select name="remunerado">
                <option value="">Remuneração</option>
                <option value="1" <?php echo (isset($_GET['remunerado']) && $_GET['remunerado'] === "1") ? "selected" : ""; ?>>Remunerado</option>
                <option value="0" <?php echo (isset($_GET['remunerado']) && $_GET['remunerado'] === "0") ? "selected" : ""; ?>>Não Remunerado</option>
            </select>
            <button type="submit" class="btn-destaque">Filtrar</button>
            <a href="vagas.php" class="btn-limpar">Limpar</a>
        </form>
    </section>

    <section class="vagas-listagem" style="background-color: var(--cor-fundo-principal); border: none; box-shadow: none;">
        <?php if ($resultado_vagas->num_rows > 0): ?>
            <?php while($vaga = $resultado_vagas->fetch_assoc()): ?>
                <article class="vaga-card">
                    <h2><?php echo htmlspecialchars($vaga['empresa']); ?></h2>
                    <p><strong>Linguagem:</strong> <?php echo htmlspecialchars($vaga['linguagem']); ?></p>
                    <p><strong>Localidade:</strong> <?php echo htmlspecialchars($vaga['localidade']); ?></p>

                    <?php if ($vaga['remunerado']): ?>
                        <p><strong>Remunerado:</strong> Salário: R$ <?php echo number_format($vaga['salario'], 2, ',', '.'); ?></p>
                    <?php else: ?>
                        <p><strong>Remunerado:</strong> Não</p>
                    <?php endif; ?>

                    <?php if (isset($_SESSION["usuario_id"])): ?>
                        <a href="candidatar.php?id=<?php echo $vaga['id']; ?>" class="btn-detalhes" style="margin-top: 1rem; display: block; text-align: center;">Candidatar-se</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-detalhes">Faça Login para Candidatar-se</a>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="erro" style="text-align:center; color: var(--cor-texto-principal); margin-top: 1rem;">Nenhuma vaga encontrada com os filtros aplicados.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2025 BitVagas - Todos os direitos reservados.</p>
</footer>

<button id="btnNotificacoes">🔔</button>
<div id="painelMensagens"></div>

<script>
function mostrarNotificacao(texto, tipo) {
    const notif = document.createElement('div');
    notif.classList.add('notificacao', 'show');
    notif.classList.add(tipo || 'info');

    notif.innerHTML = `
        <div class="titulo">${
            tipo === 'aceite' ? '🎉 Sua candidatura foi aceita!' :
            tipo === 'rejeicao' ? '😔 Candidatura rejeitada' : '📩 Nova mensagem'
        }</div>
        <div class="mensagem">${texto}</div>
    `;
    document.body.appendChild(notif);

    setTimeout(() => {
        notif.classList.remove('show');
        setTimeout(() => notif.remove(), 400);
    }, 6000);
}

function verificarMensagens() {
    fetch('ver_mensagens.php')
        .then(resp => resp.json())
        .then(dados => {
            console.log('Dados recebidos:', dados);
            if (Array.isArray(dados) && dados.length > 0) {
                const painel = document.getElementById('painelMensagens');
                painel.innerHTML = '';
                dados.forEach(msg => {
                    const div = document.createElement('div');
                    div.classList.add('mensagem-card', msg.tipo || 'outro');
                    div.innerHTML = `
                        <strong>${
                            msg.tipo === 'aceite' ? '✅ Aceite' :
                            msg.tipo === 'rejeicao' ? '❌ Rejeição' : '💬 Mensagem'
                        }</strong>
                        <p>${msg.mensagem}</p>
                        <small>${new Date(msg.data_envio).toLocaleString()}</small>
                    `;
                    painel.appendChild(div);
                    mostrarNotificacao(msg.mensagem, msg.tipo);
                });
            }
        })
        .catch(err => console.error('Erro ao verificar mensagens:', err));
}

document.getElementById('btnNotificacoes').addEventListener('click', () => {
    document.getElementById('painelMensagens').classList.toggle('show');
});

setInterval(verificarMensagens, 10000);
</script>
</body>
</html>
