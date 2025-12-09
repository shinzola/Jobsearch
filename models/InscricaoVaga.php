<?php
class InscricaoVaga {
    private $conn;
    private $table_name = "inscricao_vagas";

    // Propriedades
    public $id;
    public $idVagas;
    public $idUsuario;

    // Construtor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Getters e Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getIdVagas() {
        return $this->idVagas;
    }

    public function setIdVagas($idVagas) {
        $this->idVagas = $idVagas;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }
}
?>