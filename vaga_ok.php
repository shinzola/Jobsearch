<?php
session_start();
require_once 'config/Database.php';
require_once 'models/Vagas.php';
require_once 'dao/VagasDAO.php';

// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado']) || !isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_vaga'] = "Você precisa estar logado para anunciar ou editar uma vaga.";
    header("Location: index.php");
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta e sanitiza os dados do formulário
    $modo = $_POST['modo'] ?? 'criar'; // 'criar' ou 'editar'
    $idVaga = intval($_POST['idVaga'] ?? 0);
    $habilitada = isset($_POST['habilitada']) ? 1 : 0; // Para edição

    $nome = trim($_POST['nome'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $contato = trim($_POST['contato'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $modalidade = trim($_POST['modalidade'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $idCategoria = intval($_POST['idCategoria'] ?? 0);
    // Salva os dados do formulário na sessão para preencher em caso de erro
    $_SESSION['form_vaga'] = $_POST;

    // Validações básicas
    if (empty($nome) || empty($empresa) || empty($contato) || empty($cidade) || 
        empty($modalidade) || empty($endereco) || $idCategoria <= 0) {
        $_SESSION['erro_vaga'] = "Todos os campos obrigatórios devem ser preenchidos.";
        header("Location: index.php");
        exit();
    }

    // --- Validação e Upload da Imagem ---
    $caminhoImagem = null;
    $imagemEnviada = isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($imagemEnviada) {
        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro_vaga'] = "Erro no upload da imagem. Código: " . $_FILES['imagem']['error'];
            header("Location: index.php");
            exit();
        }

        $imagem = $_FILES['imagem'];
        $tiposPermitidos = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];

        // Verificar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $imagem['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $tiposPermitidos)) {
            $_SESSION['erro_vaga'] = "Formato de imagem não permitido. Use PNG, JPG, GIF ou WEBP.";
            header("Location: index.php");
            exit();
        }

        if ($imagem['size'] > 5 * 1024 * 1024) { // 5MB
            $_SESSION['erro_vaga'] = "A imagem deve ter no máximo 5MB.";
            header("Location: index.php");
            exit();
        }

        try {
            // Criar diretório se não existir
            $diretorio = 'uploads/vagas';
            if (!is_dir($diretorio)) {
                if (!mkdir($diretorio, 0755, true)) {
                    throw new Exception("Não foi possível criar o diretório de upload.");
                }
            }

            // Gerar nome único para a imagem
            $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
            $nomeArquivo = 'vaga_' . uniqid() . '.' . $extensao;
            $caminhoCompleto = $diretorio . '/' . $nomeArquivo;

            // Mover arquivo
            if (!move_uploaded_file($imagem['tmp_name'], $caminhoCompleto)) {
                throw new Exception("Erro ao mover o arquivo de upload.");
            }

            $caminhoImagem = $caminhoCompleto;

        } catch (Exception $e) {
            $_SESSION['erro_vaga'] = "Erro no upload da imagem: " . $e->getMessage();
            header("Location: index.php");
            exit();
        }
    }

    try {
        $database = new Database();
        $db = $database->getConnection();
        $vagaDAO = new VagasDAO($db);

        if ($modo === 'editar' && $idVaga > 0) {
            // Buscar vaga existente para editar
            $vaga = $vagaDAO->buscarPorId($idVaga);
            if (!$vaga) {
                $_SESSION['erro_vaga'] = "Vaga não encontrada para edição.";
                header("Location: index.php");
                exit();
            }

            // Atualizar dados
            $vaga->setNome($nome);
            $vaga->setEmpresa($empresa);
            $vaga->setContato($contato);
            $vaga->setCidade($cidade);
            $vaga->setEndereco($endereco);
            $vaga->setModalidade($modalidade);
            $vaga->setIdCategoria($idCategoria);
            $vaga->setHabilitada($habilitada); // Status da vaga

            // Se enviou nova imagem, atualiza e apaga a antiga
            if ($imagemEnviada && $caminhoImagem) {
                $imagemAntiga = $vaga->getImagem();
                $vaga->setImagem($caminhoImagem);
                if ($imagemAntiga && file_exists($imagemAntiga)) {
                    unlink($imagemAntiga);
                }
            }

            $resultado = $vagaDAO->atualizar($vaga);

            if ($resultado) {
                $_SESSION['sucesso'] = "Vaga '{$nome}' atualizada com sucesso!";
                unset($_SESSION['form_vaga']);
            } else {
                // Se falhar, apaga a imagem nova que foi salva
                if ($imagemEnviada && $caminhoImagem && file_exists($caminhoImagem)) {
                    unlink($caminhoImagem);
                }
                $_SESSION['erro_vaga'] = "Erro ao atualizar a vaga no banco de dados.";
            }
        } else {
            // Criar nova vaga
            $vaga = new Vaga($db);
            $vaga->setNome($nome);
            $vaga->setEmpresa($empresa);
            $vaga->setContato($contato);
            $vaga->setImagem($caminhoImagem);
            $vaga->setCidade($cidade);
            $vaga->setEndereco($endereco);
            $vaga->setModalidade($modalidade);
            $vaga->setIdCategoria($idCategoria);
            $vaga->setHabilitada($habilitada); // Vaga habilitada por padrão

            $resultado = $vagaDAO->criar($vaga);

            if ($resultado) {
                $_SESSION['sucesso'] = "Vaga '{$nome}' publicada com sucesso!";
                unset($_SESSION['form_vaga']);
            } else {
                // Se falhar, apaga a imagem que foi salva
                if ($caminhoImagem && file_exists($caminhoImagem)) {
                    unlink($caminhoImagem);
                }
                $_SESSION['erro_vaga'] = "Erro ao publicar a vaga no banco de dados.";
            }
        }
    } catch (Exception $e) {
        // Se falhar, apaga a imagem que foi salva
        if ($imagemEnviada && $caminhoImagem && file_exists($caminhoImagem)) {
            unlink($caminhoImagem);
        }
        $_SESSION['erro_vaga'] = "Erro ao processar a vaga: " . $e->getMessage();
    }

    header("Location: index.php");
    exit();
}

// Se não for POST, redireciona
header("Location: index.php");
exit();
?>