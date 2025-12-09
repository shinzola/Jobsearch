<?php
require_once 'models/InscricaoVaga.php';

class InscricaoVagaDAO {
    private $conn;
    private $table_name = "inscricao_vagas"; // ajuste para o nome correto da sua tabela

    public function __construct($db) {
        $this->conn = $db;
    }

    public function criar(InscricaoVaga $inscricao) {
        // Verificar se já existe inscrição
        $exists = $this->verificarInscricaoExistente($inscricao->getIdVagas(), $inscricao->getIdUsuario());
        if ($exists === true) {
            return false; // já inscrito
        }

        $query = "INSERT INTO " . $this->table_name . " (idVagas, idUsuario) VALUES (:idVagas, :idUsuario)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":idVagas", $inscricao->getIdVagas(), PDO::PARAM_INT);
        $stmt->bindValue(":idUsuario", $inscricao->getIdUsuario(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        // se falhou, retornar erro detalhado via Exception
        $err = $stmt->errorInfo();
        throw new Exception("Erro ao inserir inscrição: " . ($err[2] ?? json_encode($err)));
    }

    public function verificarInscricaoExistente($idVaga, $idUsuario) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE idVagas = :idVagas AND idUsuario = :idUsuario LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":idVagas", $idVaga, PDO::PARAM_INT);
        $stmt->bindValue(":idUsuario", $idUsuario, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new Exception("Erro ao verificar inscrição existente: " . ($err[2] ?? json_encode($err)));
        }

        return $stmt->rowCount() > 0;
    }

    public function contarInscricoesPorVaga($idVaga) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE idVagas = :idVagas";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":idVagas", $idVaga, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            error_log("InscricaoVagaDAO::contarInscricoesPorVaga - " . ($err[2] ?? json_encode($err)));
            return 0;
        }
        return (int) $stmt->fetchColumn();
    }

    public function deletar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletarInscricao($idVaga, $idUsuario) {
        $query = "DELETE FROM " . $this->table_name . " WHERE idVagas = :idVagas AND idUsuario = :idUsuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":idVagas", $idVaga, PDO::PARAM_INT);
        $stmt->bindValue(":idUsuario", $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>