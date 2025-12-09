<?php
session_start();

require_once 'config/Database.php';
require_once 'models/Usuario.php';
require_once 'dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

$erros = [];

if (empty($email)) {
    $erros[] = "O email é obrigatório.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Email inválido.";
}

if (empty($senha)) {
    $erros[] = "A senha é obrigatória.";
}

if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    $_SESSION['form_data'] = ['email' => $email];
    header('Location: login.php');
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro ao conectar ao banco de dados.");
    }

    $usuarioDAO = new UsuarioDAO($db);
    $usuario = $usuarioDAO->buscarPorEmail($email);

    if (!$usuario) {
        $_SESSION['erro'] = "Email ou senha incorretos.";
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: login.php');
        exit();
    }

    $senhaHash = $usuario->getSenha();

    if (empty($senhaHash)) {
        $_SESSION['erro'] = "Erro interno: senha não encontrada para este usuário.";
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: login.php');
        exit();
    }

    if (!password_verify($senha, $senhaHash)) {
        $_SESSION['erro'] = "Email ou senha incorretos.";
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: login.php');
        exit();
    }

    // Login OK
    $_SESSION['usuario_id']       = $usuario->getId();
    $_SESSION['usuario_nome']     = $usuario->getNome();
    $_SESSION['usuario_email']    = $usuario->getEmail();
    $_SESSION['usuario_imagem']   = $usuario->getImagem();
    $_SESSION['usuario_linkedin'] = $usuario->getLinkedin();
    $_SESSION['usuario_admin']    = $usuario->getAdministrador();
    $_SESSION['usuario_logado']   = true;

    $_SESSION['sucesso'] = "Login realizado com sucesso!";

    header('Location: index.php');
    exit();

} catch (Exception $e) {
    $_SESSION['erro'] = "Erro ao processar login: " . $e->getMessage();
    $_SESSION['form_data'] = ['email' => $email];
    header('Location: login.php');
    exit();
}