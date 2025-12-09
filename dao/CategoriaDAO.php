<?php
// dao/CategoriaDAO.php
require_once "models/Categoria.php";
class CategoriaDAO {
    private $conn;
    private $table_name = "categoria";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Listar todas as categorias como array de objetos Categoria
    public function listarTodas(): array {
        $query = "SELECT id, nome FROM {$this->table_name} ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);

        if (!$stmt->execute()) {
            return [];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $r) {
            $categoria = new Categoria();
            $categoria->setId($r['id']);
            $categoria->setNome($r['nome']);
            $result[] = $categoria;
        }

        return $result;
    }

    // Criar nova categoria - retorna id inserido ou false
    public function criar(Categoria $categoria) {
        $query = "INSERT INTO {$this->table_name} (nome) VALUES (:nome)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome', $categoria->getNome(), PDO::PARAM_STR);

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }
        return false;
    }

    // Buscar por id - retorna Categoria ou null
    public function buscarPorId($id) {
        $query = "SELECT id, nome FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $categoria = new Categoria();
        $categoria->setId($row['id']);
        $categoria->setNome($row['nome']);
        return $categoria;
    }
    public function atualizar(Categoria $categoria): bool {
    $query = "UPDATE {$this->table_name} SET nome = :nome WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':nome', $categoria->getNome(), PDO::PARAM_STR);
    $stmt->bindValue(':id', $categoria->getId(), PDO::PARAM_INT);

    return $stmt->execute() && $stmt->rowCount() > 0;
}
public function excluir(int $id): bool {
    $id = (int) $id;
    if ($id <= 0) {
        return false;
    }

    // Tentar verificar vínculos em possíveis nomes de tabela de vagas
    $tabelasPossiveis = ['vaga', 'vagas'];
    $count = 0;
    foreach ($tabelasPossiveis as $tabela) {
        try {
            $checkQuery = "SELECT COUNT(*) FROM {$tabela} WHERE idCategoria = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($checkStmt->execute()) {
                $count = (int) $checkStmt->fetchColumn();
                // se executar sem erro, usamos esse resultado (mesmo que 0)
                break;
            }
        } catch (Exception $e) {
            // continua para próxima tentativa (nome de tabela diferente)
            continue;
        }
    }

    if ($count > 0) {
        // existem vagas vinculadas -> não excluir
        return false;
    }

    // Prosseguir com a exclusão da categoria
    $query = "DELETE FROM {$this->table_name} WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        return false;
    }

    return $stmt->rowCount() > 0;
}
}
?>