<?php
session_start();
unset($_SESSION['erro_vaga']);
unset($_SESSION['sucesso']);
$usuarioLogado = isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
$nomeUsuario = $_SESSION['usuario_nome'] ?? '';
$imagemUsuario = $_SESSION['usuario_imagem'] ?? '';
$isAdmin = isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1;
require_once "config/Database.php";
require_once "dao/VagasDAO.php";
require_once "dao/CategoriaDAO.php";

try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    die("Erro ao conectar ao banco.");
}

// CATEGORIAS
try {
    $categoriaDAO = new CategoriaDAO($pdo);
    $categorias = $categoriaDAO->listarTodas();
} catch (Exception $e) {
    $categorias = []; // apenas se der erro EM CATEGORIAS
    error_log("Erro ao buscar categorias: " . $e->getMessage());
}
// VAGAS
try {
    $vagaDAO = new VagasDAO($pdo);

    $categoriaFiltro = isset($_GET['idCategoria']) && $_GET['idCategoria'] !== '' ? (int)$_GET['idCategoria'] : null;

    if ($isAdmin) {
        // Administrador vê todas as vagas, habilitadas ou não
        if ($categoriaFiltro) {
            $vagas = $vagaDAO->listarPorCategoria($categoriaFiltro, $incluirDesabilitadas = true);
        } else {
            $vagas = $vagaDAO->listarTodas($incluirDesabilitadas = true);
        }
    } else {
        // Usuário comum vê apenas vagas habilitadas
        if ($categoriaFiltro) {
            $vagas = $vagaDAO->listarPorCategoria($categoriaFiltro, $incluirDesabilitadas = false);
        } else {
            $vagas = $vagaDAO->listarHabilitadas();
        }
    }
    
    // Obter nome da categoria filtrada
    $categoriaNome = '';
    if ($categoriaFiltro) {
        foreach ($categorias as $cat) {
            if ($cat->getId() == $categoriaFiltro) {
                $categoriaNome = $cat->getNome();
                break;
            }
        }
    }
} catch (Exception $e) {
    $vagas = [];
    error_log("Erro ao buscar vagas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>JobSearch - Sistema de Vagas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --accent-color: #ec4899;
    }

    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .navbar {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .btn-login {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .hero-section {
        padding: 4rem 0;
        text-align: center;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
    }

    .search-box {
        background: rgba(30, 41, 59, 0.8);
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(10px);
    }

    .job-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(99, 102, 241, 0.3);
        border-color: var(--primary-color);
    }

    .company-logo {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid rgba(99, 102, 241, 0.3);
    }

    .company-logo-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        border: 2px solid rgba(99, 102, 241, 0.3);
    }

    .job-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
    }

    .company-name {
        color: #94a3b8;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .badge-custom {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .badge-modalidade {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .badge-location {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .badge-categoria {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .btn-apply {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-apply:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.4);
    }

    .btn-post-job {
        background: linear-gradient(135deg, var(--accent-color), #be185d);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
    }

    .filter-section {
        background: rgba(30, 41, 59, 0.6);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        backdrop-filter: blur(10px);
    }

    .stats-card {
        background: rgba(30, 41, 59, 0.6);
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .modal-content {
        background: rgba(30, 41, 59, 0.95);
        border: 1px solid rgba(99, 102, 241, 0.3);
        backdrop-filter: blur(10px);
    }

    .form-control,
    .form-select {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: #fff;
    }

    .form-control:focus,
    .form-select:focus {
        background: rgba(15, 23, 42, 0.8);
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        color: #fff;
    }

    .job-description {
        color: #cbd5e1;
        line-height: 1.6;
    }

    .contact-info {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .address-info {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .user-profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
    }

    .admin-badge {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .btn-add-category {
        background: linear-gradient(135deg, #10b981, #059669);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-add-category:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .info-section {
        background: rgba(15, 23, 42, 0.4);
        border-radius: 10px;
        padding: 1.2rem;
        border: 1px solid rgba(99, 102, 241, 0.2);
        transition: all 0.3s ease;
    }

    .info-section:hover {
        border-color: rgba(99, 102, 241, 0.4);
        background: rgba(15, 23, 42, 0.5);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(99, 102, 241, 0.3);
    }

    .info-content {
        padding-left: 0.5rem;
    }

    .info-content ul {
        padding-left: 1.2rem;
    }

    .info-content ul li {
        line-height: 1.6;
    }

    .text-primary {
        color: var(--primary-color) !important;
    }

    .no-vagas-message {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }

    .no-vagas-message i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .btn-clear-filter {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-clear-filter:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(239, 68, 68, 0.4);
        color: white;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .info-section {
            padding: 1rem;
        }

        .section-title {
            font-size: 1rem;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-briefcase-fill me-2"></i>JobSearch
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">

                    <?php if ($usuarioLogado && $isAdmin): ?>
                    <!-- Botão Anunciar Vaga (apenas para administradores) -->
                    <li class="nav-item me-2">
                        <button class="btn btn-post-job" onclick="abrirModalCriarVaga()">
                            <i class="bi bi-plus-circle me-1"></i>Anunciar Vaga
                        </button>
                    </li>

                    <!-- Botão Adicionar Categoria (apenas para administradores) -->
                    <li class="nav-item me-2">
                        <button class="btn btn-add-category" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="bi bi-tag-fill me-1"></i>Nova Categoria
                        </button>
                    </li>
                    <?php endif; ?>

                    <?php if ($usuarioLogado): ?>
                    <!-- Dropdown do Usuário Logado -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center user-dropdown" href="#"
                            id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (!empty($imagemUsuario)): ?>
                            <img src="<?php echo htmlspecialchars($imagemUsuario); ?>" alt="Perfil"
                                class="rounded-circle me-2 user-avatar" width="32" height="32">
                            <?php else: ?>
                            <i class="bi bi-person-circle me-2" style="font-size: 1.8rem;"></i>
                            <?php endif; ?>
                            <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item text-danger" href="deslogar.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <!-- Botão Login (usuário NÃO logado) -->
                    <li class="nav-item">
                        <a class="btn btn-login" href="login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>
    <?php if (isset($_SESSION['erro_inscricao'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['erro_inscricao']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['erro_inscricao']); endif; ?>

    <?php if (isset($_SESSION['sucesso_inscricao'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['sucesso_inscricao']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['sucesso_inscricao']); endif; ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Encontre Sua Próxima Oportunidade</h1>
            <p class="text-light fs-5 mb-4">Vagas próximas a você somente com um clique</p>
        </div>
    </section>

    <!-- Jobs Section -->
    <section class="py-5">
        <div class="container">
            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="index.php" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label text-light">
                                <i class="bi bi-funnel me-1"></i>Filtrar por Categoria
                            </label>
                            <select name="idCategoria" class="form-select" onchange="this.form.submit()">
                                <option value="">Todas as Categorias</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria->getId()) ?>"
                                    <?= ($categoriaFiltro !== null && (int)$categoriaFiltro === (int)$categoria->getId()) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria->getNome()) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <?php if ($categoriaFiltro): ?>
                            <a href="index.php" class="btn btn-clear-filter w-100">
                                <i class="bi bi-x-circle me-1"></i>Limpar Filtro
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <h2 class="text-light mb-4">
                <i class="bi bi-fire me-2"></i>
                <?php if ($categoriaFiltro): ?>
                Vagas em <?php echo htmlspecialchars($categoriaNome); ?>
                <span class="badge bg-secondary ms-2"><?php echo count($vagas); ?> vaga(s)</span>
                <?php else: ?>
                Todas as Vagas
                <span class="badge bg-secondary ms-2"><?php echo count($vagas); ?> vaga(s)</span>
                <?php endif; ?>
            </h2>

            <!-- Job Cards -->
            <div class="row">
                <?php if (!empty($vagas)): ?>
                <?php foreach ($vagas as $vaga): ?>
                <div class="col-lg-6">
                    <div class="job-card">
                        <div class="d-flex">
                            <!-- Imagem da Vaga -->
                            <?php if (!empty($vaga['imagem']) && file_exists($vaga['imagem'])): ?>
                            <img src="<?php echo htmlspecialchars($vaga['imagem']); ?>"
                                alt="<?php echo htmlspecialchars($vaga['empresa']); ?>" class="company-logo me-3">
                            <?php else: ?>
                            <div class="company-logo-placeholder me-3">
                                <?php echo strtoupper(substr($vaga['empresa'], 0, 2)); ?>
                            </div>
                            <?php endif; ?>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h3 class="job-title"><?php echo htmlspecialchars($vaga['nome']); ?></h3>
                                        <p class="company-name mb-1">
                                            <i
                                                class="bi bi-building me-1"></i><?php echo htmlspecialchars($vaga['empresa']); ?>
                                        </p>
                                        <p class="contact-info mb-1">
                                            <i
                                                class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($vaga['contato']); ?>
                                        </p>
                                        <p class="address-info mb-0">
                                            <i
                                                class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($vaga['endereco']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="mb-3 mt-3">
                                    <span class="badge badge-custom badge-modalidade">
                                        <i
                                            class="bi bi-laptop me-1"></i><?php echo htmlspecialchars($vaga['modalidade']); ?>
                                    </span>
                                    <span class="badge badge-custom badge-location">
                                        <i
                                            class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($vaga['cidade']); ?>
                                    </span>
                                    <?php if (!empty($vaga['categoria_nome'])): ?>
                                    <span class="badge badge-custom badge-categoria">
                                        <i
                                            class="bi bi-tag me-1"></i><?php echo htmlspecialchars($vaga['categoria_nome']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-apply" data-bs-toggle="modal" data-bs-target="#jobDetailModal"
                                    onclick="carregarDetalhesVaga(<?php echo htmlspecialchars(json_encode($vaga)); ?>)">
                                    Ver Detalhes <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <div class="no-vagas-message">
                        <i class="bi bi-inbox"></i>
                        <h4>Nenhuma vaga encontrada</h4>
                        <?php if ($categoriaFiltro): ?>
                        <p>Não há vagas disponíveis nesta categoria no momento.</p>
                        <a href="index.php" class="btn btn-clear-filter mt-3">
                            <i class="bi bi-arrow-left me-1"></i>Ver Todas as Vagas
                        </a>
                        <?php else: ?>
                        <p>Volte mais tarde para conferir novas oportunidades!</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php if (isset($_SESSION['sucesso_categoria'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['sucesso_categoria']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['sucesso_categoria']); endif; ?>

    <?php if (isset($_SESSION['erro_categoria'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['erro_categoria']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['erro_categoria']); endif; ?>
    <!-- Seção Gerenciar Categorias (apenas para admins) -->
    <?php if ($usuarioLogado && $isAdmin): ?>
    <section class="py-5">
        <div class="container">
            <h2 class="text-light mb-4">
                <i class="bi bi-tags me-2"></i>Gerenciar Categorias
            </h2>

            <?php if (!empty($categorias)): ?>
            <div class="list-group">
                <?php foreach ($categorias as $categoria): ?>
                <div
                    class="list-group-item d-flex justify-content-between align-items-center bg-dark text-light rounded mb-2">
                    <span><?= htmlspecialchars($categoria->getNome()) ?></span>
                    <div>
                        <button class="btn btn-sm btn-warning me-2 btn-editar-categoria"
                            data-id="<?= htmlspecialchars($categoria->getId()) ?>"
                            data-nome="<?= htmlspecialchars($categoria->getNome()) ?>">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir-categoria"
                            data-id="<?= htmlspecialchars($categoria->getId()) ?>"
                            data-nome="<?= htmlspecialchars($categoria->getNome()) ?>">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-light">Nenhuma categoria cadastrada.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-light">
                        <i class="bi bi-tag-fill me-2"></i>Adicionar Nova Categoria
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="categoria_ok.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nomeCategoria" class="form-label text-light">
                                <i class="bi bi-pencil me-1"></i>Nome da Categoria
                            </label>
                            <input type="text" class="form-control" id="nomeCategoria" name="nome"
                                placeholder="Ex: Tecnologia, Marketing, Design..." maxlength="100" required>
                            <div class="form-text text-light opacity-75">
                                <i class="bi bi-info-circle me-1"></i>Máximo de 100 caracteres
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-add-category">
                            <i class="bi bi-check-circle me-1"></i>Adicionar Categoria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form action="deletacategoria_ok.php" method="POST" id="formExcluirCategoria" class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-light" id="deleteCategoryModalLabel">
                        <i class="bi bi-trash me-2"></i>Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-light">
                    <input type="hidden" name="id" id="deleteCategoriaId" value="">
                    <p>Tem certeza que deseja excluir a categoria <strong id="deleteCategoriaNome"></strong>?</p>
                    <p class="text-warning"><small>Essa ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Excluir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form action="editacategoria_ok.php" method="POST" id="formEditarCategoria" class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-light" id="editCategoryModalLabel">
                        <i class="bi bi-pencil me-2"></i>Editar Categoria
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editCategoriaId" value="">
                    <div class="mb-3">
                        <label for="editCategoriaNome" class="form-label text-light">
                            <i class="bi bi-tag me-1"></i>Nome da Categoria
                        </label>
                        <input type="text" class="form-control" id="editCategoriaNome" name="nome" maxlength="100"
                            required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- ========================================== -->
    <!-- Modal - Post Job -->
    <!-- ========================================== -->
    <div class="modal fade" id="postJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-light" id="tituloModalVaga">
                        <i class="bi bi-plus-circle me-2"></i>Anunciar Nova Vaga
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Mensagens de Feedback -->


                    <form action="vaga_ok.php" method="POST" enctype="multipart/form-data" id="formVaga">
                        <!-- Campos ocultos para controle -->
                        <input type="hidden" name="modo" id="modoVaga" value="criar">
                        <input type="hidden" name="idVaga" id="idVaga" value="">

                        <div class="row g-3">
                            <!-- Nome da Vaga -->
                            <div class="col-md-12">
                                <label class="form-label text-light">
                                    <i class="bi bi-briefcase me-1"></i>Nome da Vaga *
                                </label>
                                <input type="text" name="nome" class="form-control"
                                    placeholder="Ex: Desenvolvedor Full Stack" maxlength="150"
                                    value="<?php echo isset($_SESSION['form_vaga']['nome']) ? htmlspecialchars($_SESSION['form_vaga']['nome']) : ''; ?>"
                                    required>
                            </div>

                            <!-- Empresa -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-building me-1"></i>Empresa *
                                </label>
                                <input type="text" name="empresa" class="form-control" placeholder="Nome da empresa"
                                    maxlength="100"
                                    value="<?php echo isset($_SESSION['form_vaga']['empresa']) ? htmlspecialchars($_SESSION['form_vaga']['empresa']) : ''; ?>"
                                    required>
                            </div>

                            <!-- Contato -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-telephone me-1"></i>Contato *
                                </label>
                                <input type="text" name="contato" class="form-control" placeholder="(00) 00000-0000"
                                    maxlength="100"
                                    value="<?php echo isset($_SESSION['form_vaga']['contato']) ? htmlspecialchars($_SESSION['form_vaga']['contato']) : ''; ?>"
                                    required>
                            </div>

                            <!-- Cidade -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-geo-alt me-1"></i>Cidade *
                                </label>
                                <input type="text" name="cidade" class="form-control" placeholder="Ex: São Paulo"
                                    maxlength="100"
                                    value="<?php echo isset($_SESSION['form_vaga']['cidade']) ? htmlspecialchars($_SESSION['form_vaga']['cidade']) : ''; ?>"
                                    required>
                            </div>

                            <!-- Modalidade -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-laptop me-1"></i>Modalidade *
                                </label>
                                <select name="modalidade" class="form-select" required>
                                    <option value=""
                                        <?php echo !isset($_SESSION['form_vaga']['modalidade']) ? 'selected' : ''; ?>
                                        disabled>Selecione...</option>
                                    <option value="Remoto"
                                        <?php echo (isset($_SESSION['form_vaga']['modalidade']) && $_SESSION['form_vaga']['modalidade'] == 'Remoto') ? 'selected' : ''; ?>>
                                        Remoto</option>
                                    <option value="Presencial"
                                        <?php echo (isset($_SESSION['form_vaga']['modalidade']) && $_SESSION['form_vaga']['modalidade'] == 'Presencial') ? 'selected' : ''; ?>>
                                        Presencial</option>
                                    <option value="Hibrido"
                                        <?php echo (isset($_SESSION['form_vaga']['modalidade']) && $_SESSION['form_vaga']['modalidade'] == 'Hibrido') ? 'selected' : ''; ?>>
                                        Híbrido</option>
                                </select>
                            </div>

                            <!-- Endereço -->
                            <div class="col-md-12">
                                <label class="form-label text-light">
                                    <i class="bi bi-pin-map me-1"></i>Endereço *
                                </label>
                                <input type="text" name="endereco" class="form-control"
                                    placeholder="Rua, número, bairro" maxlength="150"
                                    value="<?php echo isset($_SESSION['form_vaga']['endereco']) ? htmlspecialchars($_SESSION['form_vaga']['endereco']) : ''; ?>"
                                    required>
                            </div>

                            <!-- Categoria -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-tag me-1"></i>Categoria *
                                </label>
                                <select name="idCategoria" class="form-select" required>
                                    <option value=""
                                        <?php echo !isset($_SESSION['form_vaga']['idCategoria']) ? 'selected' : ''; ?>
                                        disabled>Selecione...</option>
                                    <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria->getId()); ?>"
                                        <?php echo (isset($_SESSION['form_vaga']['idCategoria']) && $_SESSION['form_vaga']['idCategoria'] == $categoria->getId()) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria->getNome()); ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <option value="" disabled>Nenhuma categoria cadastrada</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (empty($categorias)): ?>
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Cadastre uma categoria primeiro!
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Switch Habilitar/Desabilitar Vaga -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-toggle-on me-1"></i>Status da Vaga
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="habilitadaSwitch"
                                        name="habilitada" checked>
                                    <label class="form-check-label text-light" for="habilitadaSwitch"
                                        id="labelHabilitada">
                                        Vaga Habilitada
                                    </label>
                                </div>
                            </div>

                            <!-- Upload de Imagem -->
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-image me-1"></i>Imagem da Vaga
                                </label>
                                <input type="file" name="imagem" class="form-control"
                                    accept="image/png, image/jpeg, image/jpg, image/gif, image/webp">
                                <div class="form-text text-light opacity-75">
                                    <i class="bi bi-info-circle me-1"></i>PNG, JPG, GIF, WEBP (máx. 5MB)
                                </div>
                                <!-- Preview da imagem existente (para edição) -->
                                <div id="previewImagemExistente" class="mt-2" style="display: none;">
                                    <small class="text-light">Imagem atual:</small>
                                    <img id="imagemExistente" src="" alt="Imagem atual" class="img-fluid mt-1"
                                        style="max-height: 100px;">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" form="formVaga" class="btn btn-post-job" id="botaoEnviarVaga">
                        <i class="bi bi-check-circle me-1"></i>Publicar Vaga
                    </button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="jobDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div class="d-flex align-items-center w-100">
                        <!-- Imagem da Vaga -->
                        <div id="modalImagemContainer"></div>

                        <div class="flex-grow-1">
                            <h5 class="modal-title text-light mb-1" id="modalNomeVaga"></h5>
                            <p class="text-light mb-0" id="modalEmpresa"></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Badges de Modalidade, Cidade e Categoria -->
                    <div class="mb-4" id="modalBadges"></div>

                    <!-- Informações de Contato -->
                    <div class="info-section mb-4">
                        <h6 class="text-light section-title">
                            <i class="bi bi-info-circle me-2"></i>Informações de Contato
                        </h6>
                        <div class="info-content" id="modalContato"></div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Fechar
                    </button>
                    <?php if ($usuarioLogado && $isAdmin): ?>
                    <button type="button" class="btn btn-warning"
                        onclick='editarVagaAtual(<?php echo json_encode($vaga); ?>)'>
                        <i class="bi bi-pencil me-1"></i>Editar Vaga
                    </button>
                    <a id="btnExcluirVaga" href="#" class="btn btn-danger"
                        onclick="return confirm('Tem certeza que deseja excluir esta vaga?');">
                        <i class="bi bi-trash me-1"></i>Excluir Vaga
                    </a>
                    <button type="button" class="btn btn-info me-2" onclick="abrirModalCandidatos(vagaAtual)">
                        <i class="bi bi-people-fill me-1"></i>Ver Candidatos
                    </button>
                    <?php else: ?>
                    <form id="formInscricao" action="inscrever_vaga.php" method="POST" style="display:inline;">
                        <input type="hidden" name="idVaga" id="idVagaInput" value="">
                        <button type="submit" class="btn btn-apply">
                            <i class="bi bi-send me-1"></i>Candidatar-se
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="candidatosModal" tabindex="-1" aria-labelledby="candidatosModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="candidatosModalLabel">
                        <i class="bi bi-people-fill me-2"></i>Candidatos à Vaga
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="candidatosModalBody">
                    <p>Carregando candidatos...</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function abrirModalCandidatos(vaga) {
        const modalBody = document.getElementById('candidatosModalBody');
        modalBody.innerHTML = '<p>Carregando candidatos...</p>';

        const candidatosModal = new bootstrap.Modal(document.getElementById('candidatosModal'));
        candidatosModal.show();

        const id = vaga.id || vaga.ID || vaga['id'];
        if (!id) {
            modalBody.innerHTML = '<p>ID da vaga inválido.</p>';
            return;
        }

        fetch('listar_candidatos.php?idVagas=' + encodeURIComponent(id), {
                cache: 'no-store'
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                const text = await response.text();

                // Se não for JSON, mostramos o texto (ajuda a ver HTML de erro/stacktrace)
                if (!response.ok) {
                    console.error('Resposta não OK:', response.status, text);
                    modalBody.innerHTML =
                        `<div class="text-danger">Erro ao buscar candidatos. HTTP ${response.status}.</div><pre style="white-space:pre-wrap;color:#f8f9fa;background:#2d3748;padding:10px;border-radius:6px;">${escapeHtml(text)}</pre>`;
                    return;
                }

                if (!contentType.includes('application/json')) {
                    console.error('Content-Type inesperado:', contentType, text);
                    modalBody.innerHTML =
                        `<div class="text-danger">Resposta inesperada do servidor.</div><pre style="white-space:pre-wrap;color:#f8f9fa;background:#2d3748;padding:10px;border-radius:6px;">${escapeHtml(text)}</pre>`;
                    return;
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    console.error('JSON parse error:', err, text);
                    modalBody.innerHTML =
                        `<div class="text-danger">Erro ao interpretar resposta JSON.</div><pre style="white-space:pre-wrap;color:#f8f9fa;background:#2d3748;padding:10px;border-radius:6px;">${escapeHtml(text)}</pre>`;
                    return;
                }

                if (!data.success) {
                    modalBody.innerHTML =
                        `<div class="text-warning">Nenhum candidato encontrado ou erro: ${escapeHtml(data.message || 'erro')}</div>`;
                    return;
                }

                if (!data.candidatos || data.candidatos.length === 0) {
                    modalBody.innerHTML = '<p>Nenhum candidato inscrito nesta vaga.</p>';
                    return;
                }

                let html = '<div class="list-group">';
                data.candidatos.forEach(c => {
                    const img = c.imagem && c.imagem !== '' ? c.imagem :
                        'https://via.placeholder.com/50?text=U';
                    const linkedin = c.linkedin ?
                        `<a href="${escapeHtml(c.linkedin)}" target="_blank" class="text-info small">LinkedIn</a>` :
                        '';
                    html += `
                    <div class="list-group-item d-flex align-items-center bg-dark text-light rounded mb-2">
                        <img src="${escapeHtml(img)}" alt="${escapeHtml(c.nome)}" class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${escapeHtml(c.nome)}</h6>
                            <small>${escapeHtml(c.email || '')}</small><br/>
                            ${linkedin}
                        </div>
                    </div>`;
                });
                html += '</div>';
                modalBody.innerHTML = html;
            })
            .catch(err => {
                console.error('Erro na requisição:', err);
                modalBody.innerHTML =
                    `<div class="text-danger">Erro ao buscar candidatos (rede).</div><pre style="white-space:pre-wrap;color:#f8f9fa;background:#2d3748;padding:10px;border-radius:6px;">${escapeHtml(String(err))}</pre>`;
            });

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Botões de excluir categoria
        document.querySelectorAll('.btn-excluir-categoria').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nome = this.getAttribute('data-nome');

                document.getElementById('deleteCategoriaId').value = id;
                document.getElementById('deleteCategoriaNome').textContent = nome;

                const deleteModal = new bootstrap.Modal(document.getElementById(
                    'deleteCategoryModal'));
                deleteModal.show();
            });
        });

        // Botões de editar categoria (se tiver modal de edição)
        document.querySelectorAll('.btn-editar-categoria').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nome = this.getAttribute('data-nome');

                document.getElementById('editCategoriaId').value = id;
                document.getElementById('editCategoriaNome').value = nome;

                const editModal = new bootstrap.Modal(document.getElementById(
                    'editCategoryModal'));
                editModal.show();
            });
        });
    });
    let vagaAtual = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Reabrir modal se houver erro
        <?php if (isset($_SESSION['erro_vaga'])): ?>
        const postJobModal = new bootstrap.Modal(document.getElementById('postJobModal'));
        postJobModal.show();
        <?php endif; ?>

        // Reabrir modal se houver sucesso
        <?php if (isset($_SESSION['sucesso'])): ?>
        const postJobModal = new bootstrap.Modal(document.getElementById('postJobModal'));
        postJobModal.show();
        // Fechar automaticamente após 3 segundos
        setTimeout(() => {
            postJobModal.hide();
        }, 3000);
        <?php endif; ?>

        // Limpar dados do formulário da sessão ao fechar o modal
        const modal = document.getElementById('postJobModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                <?php unset($_SESSION['form_vaga']); ?>
            });
        }

        // Listener para o switch de habilitar/desabilitar
        const habilitadaSwitch = document.getElementById('habilitadaSwitch');
        if (habilitadaSwitch) {
            habilitadaSwitch.addEventListener('change', function() {
                document.getElementById('labelHabilitada').textContent = this.checked ?
                    'Vaga Habilitada' : 'Vaga Desabilitada';
            });
        }
    });

    function carregarDetalhesVaga(vaga) {
        // Armazenar a vaga atual
        vagaAtual = vaga;

        // Imagem
        const imagemContainer = document.getElementById('modalImagemContainer');
        if (vaga.imagem) {
            imagemContainer.innerHTML = `<img src="${vaga.imagem}" alt="${vaga.empresa}"
                    class="company-logo me-3">`;
        } else {
            const iniciais = vaga.empresa.substring(0, 2).toUpperCase();
            imagemContainer.innerHTML = `<div class="company-logo-placeholder me-3">${iniciais}</div>`;
        }
        const idVagaInput = document.getElementById('idVagaInput');
        if (idVagaInput) {
            idVagaInput.value = vaga.id || vaga.ID || vaga['id'] || '';
        }
        // Nome e Empresa
        document.getElementById('modalNomeVaga').textContent = vaga.nome;
        document.getElementById('modalEmpresa').innerHTML = `<i
                    class="bi bi-building me-1"></i>${vaga.empresa}`;

        // Badges
        const badgesHtml = `
                <span class="badge badge-custom badge-modalidade">
                    <i class="bi bi-laptop me-1"></i>${vaga.modalidade}
                </span>
                <span class="badge badge-custom badge-location">
                    <i class="bi bi-geo-alt me-1"></i>${vaga.cidade}
                </span>
                ${vaga.categoria_nome ? `<span class="badge badge-custom badge-categoria">
                    <i class="bi bi-tag me-1"></i>${vaga.categoria_nome}
                </span>` : ''}
                `;
        document.getElementById('modalBadges').innerHTML = badgesHtml;
        const btnExcluir = document.getElementById('btnExcluirVaga');
        if (btnExcluir) {
            btnExcluir.href = 'excluir_ok.php?id=' + encodeURIComponent(vaga.id || vaga.ID || vaga['id']);
        } // Contato
        const contatoHtml = `
                <p class="text-light mb-2">
                    <i class="bi bi-telephone me-2 text-primary"></i>
                    <strong>Telefone:</strong> ${vaga.contato}
                </p>
                <p class="text-light mb-2">
                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                    <strong>Endereço:</strong> ${vaga.endereco}
                </p>
                <p class="text-light mb-0">
                    <i class="bi bi-pin-map me-2 text-primary"></i>
                    <strong>Cidade:</strong> ${vaga.cidade}
                </p>
                `;
        document.getElementById('modalContato').innerHTML = contatoHtml;
    }

    function abrirModalCriarVaga() {
        // Resetar o formulário
        document.getElementById('formVaga').reset();

        // Definir modo criar
        document.getElementById('modoVaga').value = 'criar';
        document.getElementById('idVaga').value = '';

        // Atualizar título e botão
        document.getElementById('tituloModalVaga').innerHTML =
            '<i class="bi bi-plus-circle me-2"></i>Anunciar Nova Vaga';
        document.getElementById('botaoEnviarVaga').innerHTML =
            '<i class="bi bi-check-circle me-1"></i>Publicar Vaga';

        // Configurar switch para "Habilitada" por padrão em novas vagas
        const habilitadaSwitch = document.getElementById('habilitadaSwitch');
        if (habilitadaSwitch) {
            habilitadaSwitch.checked = true;
            document.getElementById('labelHabilitada').textContent = 'Vaga Habilitada';
        }

        // Esconder preview de imagem
        document.getElementById('previewImagemExistente').style.display = 'none';

        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('postJobModal'));
        modal.show();
    }

    function editarVagaAtual() {
        if (!vagaAtual) return;

        // Preencher formulário com dados da vaga
        document.querySelector('[name="nome"]').value = vagaAtual.nome || '';
        document.querySelector('[name="empresa"]').value = vagaAtual.empresa || '';
        document.querySelector('[name="contato"]').value = vagaAtual.contato || '';
        document.querySelector('[name="cidade"]').value = vagaAtual.cidade || '';
        document.querySelector('[name="endereco"]').value = vagaAtual.endereco || '';

        // Selecionar modalidade
        const selectModalidade = document.querySelector('[name="modalidade"]');
        selectModalidade.value = vagaAtual.modalidade || '';

        // Selecionar categoria
        const selectCategoria = document.querySelector('[name="idCategoria"]');
        selectCategoria.value = vagaAtual.idCategoria || '';

        // Definir modo editar
        document.getElementById('modoVaga').value = 'editar';
        document.getElementById('idVaga').value = vagaAtual.id || '';

        // Atualizar título e botão
        document.getElementById('tituloModalVaga').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar Vaga';
        document.getElementById('botaoEnviarVaga').innerHTML =
            '<i class="bi bi-check-circle me-1"></i>Atualizar Vaga';

        // Configurar switch de habilitar/desabilitar
        const habilitadaSwitch = document.getElementById('habilitadaSwitch');
        if (habilitadaSwitch) {
            habilitadaSwitch.checked = vagaAtual.habilitada == 1;
            document.getElementById('labelHabilitada').textContent = habilitadaSwitch.checked ? 'Vaga Habilitada' :
                'Vaga Desabilitada';
        }

        // Mostrar preview da imagem existente, se houver
        if (vagaAtual.imagem) {
            document.getElementById('imagemExistente').src = vagaAtual.imagem;
            document.getElementById('previewImagemExistente').style.display = 'block';
        } else {
            document.getElementById('previewImagemExistente').style.display = 'none';
        }

        // Fechar modal de detalhes
        const detalhesModal = bootstrap.Modal.getInstance(document.getElementById('jobDetailModal'));
        if (detalhesModal) {
            detalhesModal.hide();
        }

        // Abrir modal de edição
        const modal = new bootstrap.Modal(document.getElementById('postJobModal'));
        modal.show();
    }
    </script>
</body>

</html>