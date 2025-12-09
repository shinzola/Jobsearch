<?php
session_start();

require_once 'config/Database.php';
require_once 'models/Usuario.php';
require_once 'dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit();
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$linkedin = trim($_POST['linkedin'] ?? '');
$senha = $_POST['senha'] ?? '';

$erros = [];

// Validações básicas
if (empty($nome)) {
    $erros[] = "O nome é obrigatório.";
}

if (empty($email)) {
    $erros[] = "O email é obrigatório.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Email inválido.";
}

if (empty($senha)) {
    $erros[] = "A senha é obrigatória.";
} elseif (strlen($senha) < 6) {
    $erros[] = "A senha deve ter no mínimo 6 caracteres.";
}

// Validar imagem
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    $erros[] = "A imagem de perfil é obrigatória.";
}

if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    $_SESSION['form_data'] = $_POST;
    header('Location: cadastro.php');
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro ao conectar ao banco de dados.");
    }

    $usuarioDAO = new UsuarioDAO($db);

    // Verificar email já cadastrado
    $usuarioExistente = $usuarioDAO->buscarPorEmail($email);
    if ($usuarioExistente) {
        $_SESSION['erros'] = ["Este email já está cadastrado."];
        $_SESSION['form_data'] = $_POST;
        header('Location: cadastro.php');
        exit();
    }

    // Hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // 🔴 PROCESSAR UPLOAD DA IMAGEM
    $imagemTemp = $_FILES['imagem']['tmp_name'];
    $imagemNome = $_FILES['imagem']['name'];
    $imagemTipo = $_FILES['imagem']['type'];
    $imagemTamanho = $_FILES['imagem']['size'];
    $imagemErro = $_FILES['imagem']['error'];

    // Validar tipo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($imagemTipo, $tiposPermitidos)) {
        $_SESSION['erros'] = ["Tipo de imagem não permitido. Use JPG, PNG ou GIF."];
        $_SESSION['form_data'] = $_POST;
        header('Location: cadastro.php');
        exit();
    }

    // Validar tamanho (5MB)
    if ($imagemTamanho > 5 * 1024 * 1024) {
        $_SESSION['erros'] = ["A imagem é muito grande. Tamanho máximo: 5MB."];
        $_SESSION['form_data'] = $_POST;
        header('Location: cadastro.php');
        exit();
    }

    // 🗂️ CRIAR PASTA DE UPLOADS SE NÃO EXISTIR
    $pastaUploads = __DIR__ . '/uploads/usuarios';
    if (!file_exists($pastaUploads)) {
        mkdir($pastaUploads, 0777, true);
    }

    // Gerar nome único para o arquivo
    $extensao = pathinfo($imagemNome, PATHINFO_EXTENSION);
    $nomeArquivo = uniqid('user_', true) . '.' . $extensao;
    $caminhoCompleto = $pastaUploads . '/' . $nomeArquivo;

    // Mover arquivo para a pasta
    if (!move_uploaded_file($imagemTemp, $caminhoCompleto)) {
        throw new Exception("Erro ao fazer upload da imagem.");
    }

    // Caminho relativo para salvar no banco
    $caminhoRelativo = 'uploads/usuarios/' . $nomeArquivo;

    // Criar objeto Usuario
    $usuario = new Usuario($db);
    $usuario->setNome($nome);
    $usuario->setEmail($email);
    $usuario->setSenha($senhaHash);
    $usuario->setLinkedin($linkedin);
    $usuario->setImagem($caminhoRelativo); // 🔴 Salvar caminho ao invés de base64
    $usuario->setAdministrador(0);

    // Salvar no banco
    $idUsuario = $usuarioDAO->criar($usuario);

    if ($idUsuario) {
        $_SESSION['sucesso'] = "Cadastro realizado com sucesso!";
        $_SESSION['usuario_id'] = $idUsuario;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_imagem'] = $caminhoRelativo;
        $_SESSION['usuario_admin'] = false;

        header('Location: login.php');
        exit();
    } else {
        // Se falhar, deletar a imagem
        if (file_exists($caminhoCompleto)) {
            unlink($caminhoCompleto);
        }
        throw new Exception("Erro ao inserir usuário no banco.");
    }

} catch (Exception $e) {
    $_SESSION['erros'] = ["Erro ao processar cadastro: " . $e->getMessage()];
    $_SESSION['form_data'] = $_POST;
    header('Location: cadastro.php');
    exit();
}
?>