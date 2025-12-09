<?php
class Usuario {
    private $conn;
    private $table_name = "usuario";

    // Propriedades
    public $id;
    public $nome;
    public $email;
    public $imagem;
    public $senha;
    public $linkedin;
    public $administrador;

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

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getImagem() {
        return $this->imagem;
    }

    public function setImagem($imagem) {
        $this->imagem = $imagem;
    }
    public function getSenha(){
        return $this->senha;
    }
    public function setSenha($senha)
    {
        $this->senha = $senha;
    }
    public function getLinkedin() {
        return $this->linkedin;
    }

    public function setLinkedin($linkedin) {
        $this->linkedin = $linkedin;
    }

    public function getAdministrador() {
        return $this->administrador;
    }

    public function setAdministrador($administrador) {
        $this->administrador = $administrador;
    }

    public function isAdministrador() {
        return $this->administrador == 1;
    }
}
?>