<?php
session_start();
require_once 'config/Database.php';
require_once 'dao/VagasDAO.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true || !isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) {
    header('Location: index.php');
    exit;
}

// Verifica se o ID foi passado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erro'] = "ID da vaga não informado.";
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $vagaDAO = new VagasDAO($pdo);

    // Tenta deletar a vaga
    if ($vagaDAO->deletar($id)) {
        $_SESSION['sucesso'] = "Vaga excluída com sucesso.";
    } else {
        $_SESSION['erro'] = "Erro ao excluir a vaga.";
    }
} catch (Exception $e) {
    $_SESSION['erro'] = "Erro inesperado: " . $e->getMessage();
}

header('Location: index.php');
exit;