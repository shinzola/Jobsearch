<?php
session_start();
require_once "config/Database.php";
require_once "dao/CategoriaDAO.php";
require_once "models/Categoria.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro_categoria'] = "Requisição inválida.";
    header("Location: index.php");
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';

if ($id <= 0 || $nome === '') {
    $_SESSION['erro_categoria'] = "ID ou nome da categoria inválido.";
    header("Location: index.php");
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $categoriaDAO = new CategoriaDAO($pdo);

    // Criar objeto Categoria e preencher
    $categoria = new Categoria();
    $categoria->setId($id);
    $categoria->setNome($nome);

    // Chamar o método que espera um objeto Categoria
    $atualizado = $categoriaDAO->atualizar($categoria);

    if ($atualizado) {
        $_SESSION['sucesso'] = "Categoria atualizada com sucesso.";
    } else {
        // Pode não ter sido atualizado porque o nome é igual ao anterior
        $_SESSION['erro_categoria'] = "Nenhuma alteração detectada ou erro ao atualizar a categoria.";
    }
} catch (Exception $e) {
    error_log("editacategoria_ok.php - Erro: " . $e->getMessage());
    $_SESSION['erro_categoria'] = "Erro ao atualizar categoria.";
}

header("Location: index.php");
exit;