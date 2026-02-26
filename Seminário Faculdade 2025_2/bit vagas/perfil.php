<?php
session_start();
require_once "./config/conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["usuario_id"];
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["foto_perfil"])) {
    $diretorio = "uploads/";
    if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);

    $arquivo = $_FILES["foto_perfil"];
    if ($arquivo["error"] === 0) {
        $ext = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
        $permitidos = ["jpg", "jpeg", "png", "gif"];

        if (in_array($ext, $permitidos)) {
            $novo_nome = "perfil_" . $id_usuario . "." . $ext;
            $caminho_final = $diretorio . $novo_nome;

            move_uploaded_file($arquivo["tmp_name"], $caminho_final);

            $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil=? WHERE id=?");
            $stmt->bind_param("si", $caminho_final, $id_usuario);
            $stmt->execute();

            $mensagem = "<p style='color:green;text-align:center;'>📸 Foto de perfil atualizada!</p>";
        } else {
            $mensagem = "<p style='color:red;text-align:center;'>❌ Formato inválido. Use JPG, PNG ou GIF.</p>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar"])) {
    function limpar($dado) { return htmlspecialchars(trim($dado)); }

    $campos = [
        "nome", "telefone", "email", "endereco",
        "area_interesse", "competencias_tecnicas", "experiencia"
    ];

    foreach ($campos as $campo) {
        $$campo = limpar($_POST[$campo] ?? "");
    }

    $sql = "UPDATE usuarios SET nome=?, telefone=?, email=?, endereco=?, area_interesse=?, competencias_tecnicas=?, experiencia=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $nome, $telefone, $email, $endereco, $area_interesse, $competencias_tecnicas, $experiencia, $id_usuario);

    if ($stmt->execute()) {
        $mensagem = "<p style='color:green;text-align:center;'>✅ Perfil atualizado com sucesso!</p>";
    } else {
        $mensagem = "<p style='color:red;text-align:center;'>❌ Erro ao atualizar: {$stmt->error}</p>";
    }
}

if (isset($_POST["excluir"])) {
    $conn->query("DELETE FROM candidaturas WHERE id_usuario = $id_usuario");
    $conn->query("DELETE FROM usuarios WHERE id = $id_usuario");
    session_destroy();
    header("Location: index.html");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

$sqlCandidaturas = "SELECT v.titulo_vaga, v.empresa, v.modalidade, v.tipo_contrato, c.data_candidatura
                    FROM candidaturas c
                    JOIN vagas_completa v ON c.id_vaga = v.id
                    WHERE c.id_usuario = ?";
$stmtC = $conn->prepare($sqlCandidaturas);
$stmtC->bind_param("i", $id_usuario);
$stmtC->execute();
$candidaturas = $stmtC->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Meu Perfil - BitVagas</title>
<link rel="stylesheet" href="css/perfil.css">
<style>
body {
    background-color: #0d1117;
    color: #c9d1d9;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}
header {
    background-color: #161b22;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h2 {
    color: #58a6ff;
}
main {
    max-width: 900px;
    margin: 30px auto;
    background: #161b22;
    border-radius: 10px;
    padding: 25px 40px;
    box-shadow: 0 0 10px #000;
}
input, textarea {
    width: 100%;
    background: #0d1117;
    border: 1px solid #30363d;
    color: #c9d1d9;
    border-radius: 5px;
    padding: 8px;
}
label { font-weight: bold; margin-top: 10px; display: block; }
button {
    background-color: #238636;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
}
button:hover { background-color: #2ea043; }
.excluir {
    background-color: #da3633;
    margin-left: 10px;
}
.excluir:hover { background-color: #f85149; }
.vagas {
    margin-top: 30px;
    background-color: #0d1117;
    border: 1px solid #30363d;
    border-radius: 8px;
    padding: 15px;
}
.vaga-item {
    border-bottom: 1px solid #30363d;
    padding: 10px 0;
}
.vaga-item:last-child { border-bottom: none; }

.foto-perfil {
    text-align: center;
    margin-bottom: 20px;
}
.foto-perfil img {
    width: 150px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #30363d;
    object-fit: cover;
}
</style>
<script>

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const inputs = form.querySelectorAll("input, textarea");
    const botao = document.querySelector("button[name='atualizar']");
    const valoresIniciais = {};

    inputs.forEach(input => valoresIniciais[input.name] = input.value);

    form.addEventListener("input", () => {
        let alterou = false;
        inputs.forEach(input => {
            if (valoresIniciais[input.name] !== input.value) alterou = true;
        });
        botao.style.display = alterou ? "inline-block" : "none";
    });

    botao.style.display = "none";
});
</script>
</head>
<body>
<header>
    <h2>BitVagas</h2>
    <a href="vagas.php" style="color:#58a6ff;text-decoration:none;">Voltar</a>
</header>

<main>
    <h1>Meu Perfil</h1>
    <?php echo $mensagem; ?>

    <div class="foto-perfil">
        <img src="<?php echo !empty($usuario['foto_perfil']) ? htmlspecialchars($usuario['foto_perfil']) : 'img/default.jpg'; ?>" alt="Foto de perfil">
        <br>
        <form method="POST" enctype="multipart/form-data" style="margin-top:10px;">
            <input type="file" name="foto_perfil" accept="image/*">
            <button type="submit">Enviar Foto</button>
        </form>
    </div>

    <form method="POST" action="">
        <label>Nome completo</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>">

        <label>Telefone</label>
        <input type="text" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>">

        <label>Endereço</label>
        <input type="text" name="endereco" value="<?php echo htmlspecialchars($usuario['endereco']); ?>">

        <label>Área de interesse</label>
        <input type="text" name="area_interesse" value="<?php echo htmlspecialchars($usuario['area_interesse']); ?>">

        <label>Competências técnicas</label>
        <textarea name="competencias_tecnicas"><?php echo htmlspecialchars($usuario['competencias_tecnicas']); ?></textarea>

        <label>Experiência</label>
        <textarea name="experiencia"><?php echo htmlspecialchars($usuario['experiencia']); ?></textarea>

        <button type="submit" name="atualizar">Atualizar Perfil</button>
        <button type="submit" name="excluir" class="excluir" onclick="return confirm('Tem certeza que deseja excluir sua conta? Essa ação é irreversível.')">Excluir Conta</button>
    </form>

    <div class="vagas">
        <h2>Minhas Candidaturas</h2>
        <?php if ($candidaturas->num_rows > 0): ?>
            <?php while ($vaga = $candidaturas->fetch_assoc()): ?>
                <div class="vaga-item">
                    <strong><?php echo htmlspecialchars($vaga['titulo_vaga']); ?></strong><br>
                    <small><?php echo htmlspecialchars($vaga['empresa']); ?> • <?php echo htmlspecialchars($vaga['modalidade']); ?> • <?php echo htmlspecialchars($vaga['tipo_contrato']); ?></small><br>
                    <em>Candidatado em <?php echo date("d/m/Y H:i", strtotime($vaga['data_candidatura'])); ?></em>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma candidatura encontrada.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
