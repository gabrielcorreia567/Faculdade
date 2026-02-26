<?php
session_start();
require_once "./config/conexao.php";

$erro = "";
$sucesso = "";

function limpar($dado) {
    return trim(strip_tags($dado));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = limpar($_POST["nome"] ?? "");
    $cpf = limpar($_POST["cpf"] ?? "");
    $rg = limpar($_POST["rg"] ?? "");
    $data_nascimento = limpar($_POST["data_nascimento"] ?? "");
    $genero = limpar($_POST["genero"] ?? "");
    $estado_civil = limpar($_POST["estado_civil"] ?? "");
    $endereco = limpar($_POST["endereco"] ?? "");
    $telefone = limpar($_POST["telefone"] ?? "");
    $email = limpar($_POST["email"] ?? "");
    $senha = limpar($_POST["senha"] ?? "");
    $confirmar_senha = limpar($_POST["confirmar_senha"] ?? "");

    $instituicao = limpar($_POST["instituicao"] ?? "");
    $curso = limpar($_POST["curso"] ?? "");
    $periodo = limpar($_POST["periodo"] ?? "");
    $turno = limpar($_POST["turno"] ?? "");
    $matricula = limpar($_POST["matricula"] ?? "");
    $conclusao = limpar($_POST["conclusao"] ?? "");
    $experiencia = limpar($_POST["experiencia"] ?? "");
    $cursos_complementares = limpar($_POST["cursos_complementares"] ?? "");
    $idiomas = limpar($_POST["idiomas"] ?? "");
    $competencias_tecnicas = limpar($_POST["competencias_tecnicas"] ?? "");
    $competencias_comportamentais = limpar($_POST["competencias_comportamentais"] ?? "");
    $area_interesse = limpar($_POST["area_interesse"] ?? "");
    $modalidade = limpar($_POST["modalidade"] ?? "");
    $carga_horaria = limpar($_POST["carga_horaria"] ?? "");
    $pretensao_bolsa = limpar($_POST["pretensao_bolsa"] ?? "");
    $cidade_preferencia = limpar($_POST["cidade_preferencia"] ?? "");

    if (
        empty($nome) || empty($cpf) || empty($rg) || empty($data_nascimento) ||
        empty($genero) || empty($estado_civil) || empty($endereco) ||
        empty($telefone) || empty($email) || empty($senha)
    ) {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif (!preg_match("/^[0-9]{11}$/", $cpf)) {
        $erro = "O CPF deve conter apenas números e ter 11 dígitos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";
    } elseif (strlen($senha) < 8 || !preg_match("/[A-Za-z]/", $senha) || !preg_match("/[0-9]/", $senha)) {
        $erro = "A senha deve ter pelo menos 8 caracteres, com letras e números.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ? OR email = ?");
        $stmt->bind_param("ss", $cpf, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erro = "CPF ou e-mail já cadastrados.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios 
            (nome, cpf, rg, data_nascimento, genero, estado_civil, endereco, telefone, email, senha, 
             instituicao, curso, periodo, turno, matricula, conclusao, experiencia, cursos_complementares, 
             idiomas, competencias_tecnicas, competencias_comportamentais, area_interesse, modalidade, 
             carga_horaria, pretensao_bolsa, cidade_preferencia)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("ssssssssssssssssssssssssss",
                $nome, $cpf, $rg, $data_nascimento, $genero, $estado_civil,
                $endereco, $telefone, $email, $senha_hash, $instituicao, $curso,
                $periodo, $turno, $matricula, $conclusao, $experiencia,
                $cursos_complementares, $idiomas, $competencias_tecnicas,
                $competencias_comportamentais, $area_interesse, $modalidade,
                $carga_horaria, $pretensao_bolsa, $cidade_preferencia
            );

            if ($stmt->execute()) {
                $novo_id = $stmt->insert_id;

                $_SESSION["usuario_id"] = $novo_id;
                $_SESSION["usuario_nome"] = $nome;
                $_SESSION["usuario_email"] = $email;

                header("Location: vagas.php");
                exit;
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Registro de Candidato</title>
  <link rel="stylesheet" href="css/registro.css">
</head>
<body>
  <body>
  <h2 style="text-align:center; color:#fff;">Registro de Candidato</h2>

  <?php if ($erro) echo "<p style='color:red; text-align:center;'>$erro</p>"; ?>
  <?php if ($sucesso) echo "<p style='color:green; text-align:center;'>$sucesso</p>"; ?>

  <div class="form-container">
    <form method="POST" action="">
    
      <fieldset class="panel"> 
        <legend><h3 class="panel-wide">Dados Pessoais</h3></legend>
        <div>
          <label>Nome completo *</label>
          <input type="text" name="nome">
        </div>
        <div>
          <label>CPF *</label>
          <input type="text" name="cpf">
        </div>
        <div>
          <label>RG</label>
          <input type="text" name="rg">
        </div>
        <div>
          <label>Data de nascimento</label>
          <input type="date" name="data_nascimento">
        </div>
        <div>
          <label>Gênero</label>
          <input type="text" name="genero">
        </div>
        <div>
          <label>Estado civil</label>
          <input type="text" name="estado_civil">
        </div>
        <div class="panel-wide">
          <label>Endereço</label>
          <input type="text" name="endereco">
        </div>
        <div>
          <label>Telefone / WhatsApp</label>
          <input type="text" name="telefone">
        </div>
        <div>
          <label>E-mail *</label>
          <input type="email" name="email">
        </div>
        <div>
          <label>Senha * (A senha deve conter 8 caracteres com letras e numeros)</label>
          <input type="password" name="senha">
        </div>
        <div>
          <label>Confirmar senha *</label>
          <input type="password" name="confirmar_senha">
      </fieldset>

      <fieldset class="panel">
        <legend><h3 class="panel-wide">Dados Acadêmicos</h3></legend>
        <div>
          <label>Instituição de ensino</label>
          <input type="text" name="instituicao">
        </div>
        <div>
          <label>Curso</label>
          <input type="text" name="curso">
        </div>
        <div>
          <label>Período</label>
          <input type="text" name="periodo">
        </div>
        <div>
          <label>Turno</label>
          <input type="text" name="turno">
        </div>
        <div>
          <label>Matrícula</label>
          <input type="text" name="matricula">
        </div>
        <div>
          <label>Previsão de conclusão</label>
          <input type="date" name="conclusao">
        </div>
      </fieldset>

      <fieldset class="panel">
        <legend><h3 class="panel-wide">Experiência e Competências</h3></legend>
        <div class="panel-wide">
          <label>Experiências anteriores</label>
          <textarea name="experiencia"></textarea>
        </div>
        <div class="panel-wide">
          <label>Cursos complementares</label>
          <textarea name="cursos_complementares"></textarea>
        </div>
        <div class="panel-wide">
          <label>Idiomas</label>
          <textarea name="idiomas"></textarea>
        </div>
        <div class="panel-wide">
          <label>Competências técnicas</label>
          <textarea name="competencias_tecnicas"></textarea>
        </div>
        <div class="panel-wide">
          <label>Competências comportamentais</label>
          <textarea name="competencias_comportamentais"></textarea>
        </div>
      </fieldset>

      <fieldset class="panel">
        <legend><h3 class="panel-wide">Preferências</h3></legend>
        <div>
          <label>Área de interesse</label>
          <input type="text" name="area_interesse">
        </div>
        <div>
          <label>Modalidade</label>
          <input type="text" name="modalidade">
        </div>
        <div>
          <label>Carga horária desejada</label>
          <input type="text" name="carga_horaria">
        </div>
        <div>
          <label>Pretensão de bolsa</label>
          <input type="text" name="pretensao_bolsa">
        </div>
        <div class="panel-wide">
          <label>Cidade de preferência</label>
          <input type="text" name="cidade_preferencia">
        </div>
        <button type="submit">Registrar</button>
      </fieldset>

    </form>
  </div>
</body>
</html>