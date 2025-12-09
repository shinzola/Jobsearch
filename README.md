# Projeto JobSearch

## Descrição
Sistema web para gerenciamento de vagas de emprego com funcionalidades para cadastro, edição, exclusão de vagas e categorias, além de inscrição de usuários nas vagas.

## Tecnologias Utilizadas
- PHP
- MySQL
- Bootstrap 5
- JavaScript

## Estrutura do Projeto

### Arquivos principais
- `index.php`: Página principal com listagem e filtro de vagas.
- `deletacategoria_ok.php`: Script para exclusão de categorias.
- `categoria_ok.php`: Script para criação de categorias.
- `editacategoria_ok.php`: Script para edição de categorias.
- `vaga_ok.php`: Script para criação e edição de vagas.
- `inscrever_vaga.php`: Script para inscrição de usuários em vagas.
- `listar_candidatos.php`: Endpoint para listar candidatos de uma vaga.

### Classes DAO
- `CategoriaDAO.php`: Gerencia operações CRUD para categorias.
- `VagasDAO.php`: Gerencia operações CRUD para vagas.
- `InscricaoVagaDAO.php`: Gerencia inscrições de usuários em vagas.
- `UsuarioDAO.php`: Gerencia operações CRUD para usuários.

### Modelos
- `Usuario.php`: Modelo para usuaário.
- `Categoria.php`: Modelo para categoria.
- `Vaga.php`: Modelo para vaga.
- `InscricaoVaga.php`: Modelo para inscrição em vaga.

## Funcionalidades

- Listagem de vagas com filtro por categoria.
- Cadastro, edição e exclusão de categorias (restrição para exclusão se houver vagas vinculadas).
- Cadastro, edição e exclusão de vagas.
- Inscrição de usuários nas vagas.
- Visualização dos candidatos inscritos em cada vaga.
- Controle de acesso baseado em sessão para usuários e administradores.
- Upload de imagens para vagas com pré-visualização.

## Observações

- Exclusão em cascata pode ser configurada no banco de dados para apagar vagas vinculadas automaticamente.
- Mensagens de sucesso e erro são exibidas para feedback do usuário.

## Como Utilizar no Ambiente Local

### Requisitos
- XAMPP 3.2.4 (ou superior) instalado e configurado.
- Banco de dados MySQL.
- Script SQL `jobsearch_system.sql` para criar as tabelas e dados iniciais.

### Passos
1. Copie os arquivos do projeto para a pasta `htdocs` do XAMPP.
2. Inicie o Apache e o MySQL pelo painel de controle do XAMPP.
3. Importe o arquivo `jobsearch_system.sql` no phpMyAdmin para criar o banco de dados e as tabelas.
4. Configure o arquivo `config/Database.php` com as credenciais corretas do seu MySQL.
5. Acesse o projeto pelo navegador via `http://localhost/jobsearch`.
6. Utilize a interface para gerenciar vagas, categorias e inscrições.

