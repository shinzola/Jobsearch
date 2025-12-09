<?php
session_start();
require_once 'config/Database.php';
require_once 'dao/CategoriaDAO.php';

if (!isset($_POST['id'])) {
    $_SESSION['erro_categoria'] = "ID da categoria não fornecido.";
    header("Location: index.php");
    exit;
}

$id = (int) $_POST['id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $categoriaDAO = new CategoriaDAO($pdo);

    $excluido = $categoriaDAO->excluir($id);

    if ($excluido) {
        $_SESSION['sucesso_categoria'] = "Categoria excluída com sucesso.";
    } 
} catch (Exception $e) {
    $_SESSION['erro_categoria'] = "Erro ao excluir categoria: " . $e->getMessage();
}

header("Location: index.php");
exit;
?>