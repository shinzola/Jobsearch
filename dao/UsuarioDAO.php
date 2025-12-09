<?php
require_once 'models/Usuario.php';

class UsuarioDAO {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar usuário
    public function criar(Usuario $usuario) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, email, imagem, linkedin, administrador, senha) 
                  VALUES (:nome, :email, :imagem, :linkedin, :administrador, :senha)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $usuario->getNome());
        $stmt->bindParam(":email", $usuario->getEmail());
        $stmt->bindParam(":imagem", $usuario->getImagem());
        $stmt->bindParam(":linkedin", $usuario->getLinkedin());
        $stmt->bindParam(":administrador", $usuario->getAdministrador());
        $stmt->bindParam(":senha", $usuario->getSenha());

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Buscar por ID
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $usuario = new Usuario($this->conn);
            $usuario->setId($row['id']);
            $usuario->setNome($row['nome']);
            $usuario->setEmail($row['email']);
            $usuario->setImagem($row['imagem']);
            $usuario->setLinkedin($row['linkedin']);
            $usuario->setAdministrador($row['administrador']);
            $usuario->setSenha($row['senha']);
            return $usuario;
        }
        return null;
    }

    // Buscar por email
    public function buscarPorEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $usuario = new Usuario($this->conn);
            $usuario->setId($row['id']);
            $usuario->setNome($row['nome']);
            $usuario->setEmail($row['email']);
            $usuario->setImagem($row['imagem']);
            $usuario->setLinkedin($row['linkedin']);
            $usuario->setAdministrador($row['administrador']);
            $usuario->setSenha($row['senha']);
            return $usuario;
        }
        return null;
    }

    // Atualizar
    public function atualizar(Usuario $usuario) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, 
                      email = :email, 
                      imagem = :imagem, 
                      linkedin = :linkedin, 
                      administrador = :administrador,
                      senha = :senha
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $usuario->getNome());
        $stmt->bindParam(":email", $usuario->getEmail());
        $stmt->bindParam(":imagem", $usuario->getImagem());
        $stmt->bindParam(":linkedin", $usuario->getLinkedin());
        $stmt->bindParam(":administrador", $usuario->getAdministrador());
        $stmt->bindParam(":senha", $usuario->getSenha());
        $stmt->bindParam(":id", $usuario->getId());

        return $stmt->execute();
    }

    // Deletar
    public function deletar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>