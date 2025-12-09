<?php
require_once 'models/Vagas.php';

class VagasDAO {
    private $conn;
    private $table_name = "vagas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar vaga
    public function criar(Vaga $vaga) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, empresa, contato, imagem, cidade, endereco, modalidade, idCategoria, habilitada) 
                  VALUES (:nome, :empresa, :contato, :imagem, :cidade, :endereco, :modalidade, :idCategoria, :habilitada)";

        try {
            $stmt = $this->conn->prepare($query);

            $nome = $vaga->getNome();
            $empresa = $vaga->getEmpresa();
            $contato = $vaga->getContato();
            $imagem = $vaga->getImagem();
            $cidade = $vaga->getCidade();
            $endereco = $vaga->getEndereco();
            $modalidade = $vaga->getModalidade();
            $idCategoria = $vaga->getIdCategoria();
            $habilitada = $vaga->getHabilitada() ?? 1; // Padrão: habilitada

            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":empresa", $empresa);
            $stmt->bindParam(":contato", $contato);
            $stmt->bindParam(":imagem", $imagem);
            $stmt->bindParam(":cidade", $cidade);
            $stmt->bindParam(":endereco", $endereco);
            $stmt->bindParam(":modalidade", $modalidade);
            $stmt->bindParam(":idCategoria", $idCategoria);
            $stmt->bindParam(":habilitada", $habilitada);

            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao criar vaga: " . $e->getMessage());
            throw $e;
        }
    }

    // Buscar por ID
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $vaga = new Vaga($this->conn);
            $vaga->setId($row['id']);
            $vaga->setNome($row['nome']);
            $vaga->setEmpresa($row['empresa']);
            $vaga->setContato($row['contato']);
            $vaga->setImagem($row['imagem']);
            $vaga->setCidade($row['cidade']);
            $vaga->setEndereco($row['endereco']);
            $vaga->setModalidade($row['modalidade']);
            $vaga->setIdCategoria($row['idCategoria']);
            $vaga->setHabilitada($row['habilitada']);
            return $vaga;
        }
        return null;
    }
public function listarPorCategoria($idCategoria) {
    $sql = "SELECT v.*, c.nome as categoria_nome 
            FROM vagas v 
            LEFT JOIN categoria c ON v.idCategoria = c.id 
            WHERE v.habilitada = 1 AND v.idCategoria = :idCategoria
            ORDER BY v.id DESC";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':idCategoria', $idCategoria, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    // Listar todas
    public function listarTodas() {
        $query = "SELECT v.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " v 
                  LEFT JOIN categoria c ON v.idCategoria = c.id 
                  ORDER BY v.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar apenas habilitadas
    public function listarHabilitadas() {
        $query = "SELECT v.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " v 
                  LEFT JOIN categoria c ON v.idCategoria = c.id 
                  WHERE v.habilitada = 1 
                  ORDER BY v.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar por categoria
    public function buscarPorCategoria($idCategoria) {
        $query = "SELECT v.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " v 
                  LEFT JOIN categoria c ON v.idCategoria = c.id 
                  WHERE v.idCategoria = :idCategoria AND v.habilitada = 1 
                  ORDER BY v.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idCategoria", $idCategoria);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Atualizar
    public function atualizar(Vaga $vaga) {
        // Buscar a vaga antiga para verificar a imagem
        $vagaAntiga = $this->buscarPorId($vaga->getId());
        if ($vagaAntiga && $vagaAntiga->getImagem() && $vagaAntiga->getImagem() !== $vaga->getImagem()) {
            // Se uma nova imagem foi enviada e a antiga existe, deleta a antiga
            if (file_exists($vagaAntiga->getImagem())) {
                unlink($vagaAntiga->getImagem());
            }
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, 
                      empresa = :empresa, 
                      contato = :contato, 
                      imagem = :imagem, 
                      cidade = :cidade, 
                      endereco = :endereco, 
                      modalidade = :modalidade, 
                      idCategoria = :idCategoria,
                      habilitada = :habilitada 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $nome = $vaga->getNome();
        $empresa = $vaga->getEmpresa();
        $contato = $vaga->getContato();
        $imagem = $vaga->getImagem();
        $cidade = $vaga->getCidade();
        $endereco = $vaga->getEndereco();
        $modalidade = $vaga->getModalidade();
        $idCategoria = $vaga->getIdCategoria();
        $habilitada = $vaga->getHabilitada();
        $id = $vaga->getId();

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":empresa", $empresa);
        $stmt->bindParam(":contato", $contato);
        $stmt->bindParam(":imagem", $imagem);
        $stmt->bindParam(":cidade", $cidade);
        $stmt->bindParam(":endereco", $endereco);
        $stmt->bindParam(":modalidade", $modalidade);
        $stmt->bindParam(":idCategoria", $idCategoria);
        $stmt->bindParam(":habilitada", $habilitada);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Deletar
    public function deletar($id) {
        // Buscar a vaga para deletar a imagem associada
        $vaga = $this->buscarPorId($id);
        if ($vaga && $vaga->getImagem()) {
            if (file_exists($vaga->getImagem())) {
                unlink($vaga->getImagem());
            }
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Habilitar/Desabilitar vaga
    public function alterarStatus($id, $habilitada) {
        $query = "UPDATE " . $this->table_name . " SET habilitada = :habilitada WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habilitada", $habilitada);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Contar vagas habilitadas
    public function contarVagas() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE habilitada = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

}
?>