<?php
require_once __DIR__ . '/../../../app/config/config.php';

require_once PUBLIC_PATH . '/views/components/header.php';
require_once APP_PATH . '/config/conexao.php';
require_once APP_PATH . '/models/usuario.php';

$pdo = getConnection();

$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensagem = loginUsuario($_POST['email'], $_POST['senha']);
    if ($mensagem === "Login realizado com sucesso!") {
        header("Location: /GameClock/public/index.php");
        exit;
    } else {
        $mensagemErro = $mensagem;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body class="centered-content">
<div class="container">
    <div class="login-container">
        <h1 class="text-center">Login</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" title="Entrar na Conta">Entrar</button>
        </form>
        <div class="text-center mt-3">
            <p>Não tem cadastro? <a href="cadastroUsuario.php" title="Cadastrar Conta">Cadastre-se aqui</a></p>
        </div>
    </div>

    <?php if (!empty($mensagemErro)): ?>
        <div class="alert alert-danger text-center mt-3">
            <?php echo htmlspecialchars($mensagemErro); ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

<?php
    require_once PUBLIC_PATH . '/views/components/footer.php';
?>