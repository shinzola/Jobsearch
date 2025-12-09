<?php
class Vaga {
    private $conn;
    private $table_name = "vagas";

    // Propriedades
    public $id;
    public $nome;
    public $empresa;
    public $contato;
    public $imagem;
    public $cidade;
    public $endereco;
    public $modalidade;
    public $id_categoria;
    private $habilitada;

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

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getContato() {
        return $this->contato;
    }

    public function setContato($contato) {
        $this->contato = $contato;
    }

    public function getImagem() {
        return $this->imagem;
    }

    public function setImagem($imagem) {
        $this->imagem = $imagem;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function getModalidade() {
        return $this->modalidade;
    }

    public function setModalidade($modalidade) {
        $this->modalidade = $modalidade;
    }

    public function getIdCategoria() {
        return $this->id_categoria;
    }

    public function setIdCategoria($id_categoria) {
        $this->id_categoria = $id_categoria;
    }
    public function getHabilitada() { 
        return $this->habilitada; 
    }
    public function setHabilitada($habilitada) { 
        $this->habilitada = $habilitada; 
    }
}
?>