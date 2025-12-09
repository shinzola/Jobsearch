<?php
session_start();
require_once 'config/Database.php';
require_once 'dao/CategoriaDAO.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    
    // Validação
    if (empty($nome)) {
        $_SESSION['erro'] = "O nome da categoria é obrigatório.";
        header("Location: index.php");
        exit();
    }
    
    if (strlen($nome) > 100) {
        $_SESSION['erro'] = "O nome da categoria não pode ter mais de 100 caracteres.";
        header("Location: index.php");
        exit();
    }
    
    try {
        $db = new Database();
        $categoriaDAO = new CategoriaDAO($db->getConnection());
        
        // Cria a categoria
        $categoria = new Categoria();
        $categoria->setNome($nome);
        
        if ($categoriaDAO->criar($categoria)) {
            $_SESSION['sucesso'] = "Categoria '{$nome}' adicionada com sucesso!";
        } else {
            $_SESSION['erro'] = "Erro ao adicionar a categoria.";
        }
    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro: " . $e->getMessage();
    }
    
    header("Location: index.php");
    exit();
}

// Se não for POST, redireciona
header("Location: index.php");
exit();
?>