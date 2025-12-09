<?php
// ATENÇÃO: deixar display_errors ligado apenas em ambiente de desenvolvimento local
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "config/Database.php";
require_once "dao/InscricaoVagaDAO.php";
require_once "models/InscricaoVaga.php";

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    $_SESSION['erro_inscricao'] = "Você precisa estar logado para se inscrever.";
    header("Location: index.php");
    exit;
}
if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1) {
    $_SESSION['erro_inscricao'] = "Administradores não podem se inscrever em vagas.";
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro_inscricao'] = "Requisição inválida.";
    header("Location: index.php");
    exit;
}

$idVaga = isset($_POST['idVaga']) ? (int) $_POST['idVaga'] : 0;
$idUsuario = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : 0;

if ($idVaga <= 0 || $idUsuario <= 0) {
    $_SESSION['erro_inscricao'] = "Dados inválidos para inscrição.";
    header("Location: index.php");
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $inscricaoDAO = new InscricaoVagaDAO($pdo);

    $inscricao = new InscricaoVaga($pdo);
    $inscricao->setIdVagas($idVaga);
    $inscricao->setIdUsuario($idUsuario);

    $resultado = $inscricaoDAO->criar($inscricao);

    if ($resultado === false) {
        // Se criar retornasse false por já inscrito, tratamos: (no nosso DAO ele lança Exception ao falhar na query)
        $_SESSION['erro_inscricao'] = "Você já está inscrito nesta vaga.";
    } else {
        $_SESSION['sucesso_inscricao'] = "Inscrição realizada com sucesso!";
    }
} catch (Exception $e) {
    // Log completo para debug
    error_log("inscrever_vaga.php - Exception: " . $e->getMessage());
    // Mensagem amigável para o usuário (não exibir erro técnico em produção)
    $_SESSION['erro_inscricao'] = "Erro ao realizar inscrição (verifique logs).";
    // Em ambiente dev você pode mostrar a mensagem detalhada:
    // $_SESSION['erro_inscricao'] = "Erro ao realizar inscrição: " . $e->getMessage();
}

header("Location: index.php");
exit;