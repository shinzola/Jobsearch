<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Segurança: apenas admins logados podem acessar
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true ||
    !isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if (!isset($_GET['idVagas']) || !is_numeric($_GET['idVagas'])) {
    echo json_encode(['success' => false, 'message' => 'ID da vaga inválido']);
    exit;
}

$idVaga = (int) $_GET['idVagas'];
require_once "config/Database.php";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    // Ajuste nomes conforme seu esquema: tabela usuarios, colunas nome,email,imagem,linkedin
    $sql = "SELECT u.nome, u.email, u.imagem, u.linkedin
            FROM inscricao_vagas iv
            INNER JOIN usuario u ON iv.idUsuario = u.id
            WHERE iv.idVagas = :idVaga
            ORDER BY u.nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idVaga', $idVaga, PDO::PARAM_INT);
    $stmt->execute();
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'candidatos' => $candidatos]);
} catch (Exception $e) {
    error_log("listar_candidatos.php - Erro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar candidatos']);
}