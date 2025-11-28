<?php
require_once '../resources/header.php';
require_once '../resources/conexao.php';
require_once '../resources/uploadFoto.php';
require_once '../resources/usuario.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensagem = cadastrarUsuario($_POST['nome_usuario'], $_POST['email'], $_POST['senha'], $_POST['descricao'], $_POST['id_personalizado'], $_FILES['foto']);
    echo $mensagem;

    if ($mensagem === "Usuário cadastrado com sucesso!") {
        sleep(2);
        header("Location: /GameClock/pages/login.php");
        exit;
    }
}
?>

<div class="container">
    <div class="login-container">
        <h1 class="text-center">Cadastro de Usuário</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome_usuario" class="form-label">Nome de Usuário</label>
                <input type="text" class="form-control" id="nome_usuario" name="nome_usuario" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao"></textarea>
            </div>
            <div class="mb-3">
                <label for="id_perfil" class="form-label">Identificador do Perfil</label>
                <input type="text" class="form-control" id="id_perfil" name="id_personalizado" required>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto de Perfil</label>
                <input type="file" class="form-control" id="foto" name="foto">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
        <p class="mt-3"><?php echo $mensagem; ?></p>
    </div>
</div>