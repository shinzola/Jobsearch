<?php
session_start();

// Recuperar mensagens
$erro = $_SESSION['erro'] ?? null;
$erros = $_SESSION['erros'] ?? [];
$sucesso = $_SESSION['sucesso'] ?? null;
$formData = $_SESSION['form_data'] ?? [];

// Limpar sessão
unset($_SESSION['erro']);
unset($_SESSION['erros']);
unset($_SESSION['sucesso']);
unset($_SESSION['form_data']);

// Se já estiver logado, redirecionar
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JobSearch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .login-container {
        max-width: 480px;
        width: 100%;
        padding: 0 1rem;
    }

    .login-card {
        background: rgba(30, 41, 59, 0.8);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(10px);
    }

    .logo-section {
        text-align: center;
        margin-bottom: 2rem;
    }

    .logo-title {
        font-weight: 700;
        font-size: 2.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
    }

    .logo-subtitle {
        color: #94a3b8;
        font-size: 1rem;
    }

    .form-control {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: #fff;
        padding: 0.75rem 1rem;
        border-radius: 10px;
    }

    .form-control:focus {
        background: rgba(15, 23, 42, 0.8);
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        color: #fff;
    }

    .form-label {
        color: #e2e8f0;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .btn-login {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        padding: 0.75rem;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .signup-link {
        text-align: center;
        margin-top: 1.5rem;
        color: #94a3b8;
    }

    .signup-link a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .signup-link a:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    .back-home {
        text-align: center;
        margin-top: 1.5rem;
    }

    .back-home a {
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .back-home a:hover {
        color: white;
    }

    .input-group-text {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: #94a3b8;
        border-radius: 10px 0 0 10px;
    }

    .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-title">
                    <i class="bi bi-briefcase-fill"></i> JobSearch
                </div>
                <p class="logo-subtitle">Faça login para continuar</p>
            </div>

            <!-- Mensagens de Feedback -->
            <?php if ($sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($sucesso); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($erro); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (!empty($erros)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login_ok.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i>Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com"
                            value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i>Senha
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="senha" placeholder="••••••••"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn btn-login mt-4">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                </button>
            </form>

            <!-- Signup Link -->
            <div class="signup-link">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
            </div>

            <!-- Back to Home -->
            <div class="back-home">
                <a href="index.php">
                    <i class="bi bi-arrow-left me-1"></i>Voltar para o início
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>