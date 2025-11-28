<?php
require_once '../resources/header.php';
require_once '../resources/usuario.php';
require_once '../resources/jogo.php';

$apiKey = 'F58C51E12D02C688C13BB03A26F1B682';
$mensagem = "";
$mensagem_steam = "";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /GameClock/pages/login.php");
    exit;
}

if (isset($_SESSION['mensagem_sinc'])) {
    $mensagem_steam = $_SESSION['mensagem_sinc'];
    unset($_SESSION['mensagem_sinc']);
}

if (isset($_SESSION['mensagem_sinc_conquistas'])) {
    $mensagem_steam = $_SESSION['mensagem_sinc_conquistas'];
    unset($_SESSION['mensagem_sinc_conquistas']);
}

$steamIdAtual = buscarSteamIdPorUsuarioId($_SESSION['usuario_id']);

$usuario = [
    'nome_usuario' => $_SESSION['usuario_nome'],
    'descricao' => $_SESSION['descricao'] ?? '',
    'id_personalizado' => $_SESSION['id_personalizado'],
    'foto' => $_SESSION['usuario_foto'],
    'steam_id' => $steamIdAtual
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['salvar_perfil'])) {
        $mensagem = editarPerfil(
            $_SESSION['usuario_id'],
            $_POST['nome_usuario'],
            $_POST['descricao'],
            $_POST['id_personalizado'],
            $_FILES['foto']
        );

        if (!empty($_FILES['foto']['name'])) {
            $pastaDestino = '../uploads/'; 
            $nomeArquivo = basename($_FILES['foto']['name']);
            $caminhoCompleto = $pastaDestino . $nomeArquivo;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoCompleto)) {
                $novoCaminhoFoto = '/GameClock/uploads/' . $nomeArquivo;
                $_SESSION['usuario_foto'] = $novoCaminhoFoto;
            } else {
                $mensagem = "Erro ao salvar a foto.";
            }
        }
    }
}

if (isset($_POST['vincular_steam'])) {
    $mensagem_steam = vincularContaSteam(
        $_SESSION['usuario_id'],
        $_POST['vanityUrl'],
        $apiKey
    );
}

$jogos = listarJogos($_SESSION['usuario_id']);

?>

<div class="container mt-5">
    <h1 class="mb-4">Configuração do Perfil</h1>
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>
    <div class="card p-4 mb-5">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-3 text-center">
                    <label for="foto" class="form-label">Foto</label>
                    <input type="file" class="form-control mb-3" id="foto" name="foto">
                    <?php if (!empty($usuario['foto'])): ?>
                        <img src="/GameClock/<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto Atual" class="img-thumbnail" style="width: 150px; height: 150px;">
                    <?php endif; ?>
                </div>

                <div class="col-md-9">
                    <div class="mb-3">
                        <label for="id_personalizado" class="form-label">ID Personalizado</label>
                        <input type="text" class="form-control" id="id_personalizado" name="id_personalizado" value="<?php echo htmlspecialchars($usuario['id_personalizado']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nome_usuario" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome_usuario" name="nome_usuario" value="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo htmlspecialchars($usuario['descricao']); ?></textarea>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" name="salvar_perfil" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
    <div class="container mt-5">
    <h2 class="mb-4">Integração Steam</h2>
    <?php if (!empty($mensagem_steam)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($mensagem_steam); ?></div>
    <?php endif; ?>

    <div class="card p-4 mb-5">
        <div class="row">
            <div class="col-md-6 border-end">
                <h5>1. Vincular Conta</h5>
                <?php if ($usuario['steam_id']): ?>
                    <p class="text-success">Conta Steam vinculada!</p>
                    <p><strong>SteamID:</strong> <?php echo htmlspecialchars($usuario['steam_id']); ?></p>
                <?php else: ?>
                    <p>Vincule sua conta Steam para sincronizar jogos e conquistas.</p>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="vanityUrl" class="form-label">Seu nome de perfil Steam (URL)</label>
                        <input type="text" class="form-control" id="vanityUrl" name="vanityUrl" placeholder="ex: seu_nome_customizado">
                    </div>
                    <button type="submit" name="vincular_steam" class="btn btn-primary">Vincular / Atualizar</button>
                </form>
            </div>

            <div class="col-md-6">
                <h5>2. Sincronizar Dados</h5>
                <p>Após vincular, sincronize seus dados. Isso pode levar alguns minutos.</p>
                
                <form method="POST" action="/GameClock/resources/jogo.php?acao=sincronizar_steam" class="d-inline">
                    <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
                    <button type="submit" class="btn btn-success" <?php echo !$usuario['steam_id'] ? 'disabled' : ''; ?>>
                        <i class="bi bi-controller"></i> Sincronizar Jogos
                    </button>
                </form>

                <form method="POST" action="/GameClock/resources/conquista.php?acao=sincronizar_steam" class="d-inline">
                    <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
                    <button type="submit" class="btn btn-info" <?php echo !$usuario['steam_id'] ? 'disabled' : ''; ?>>
                        <i class="bi bi-trophy"></i> Sincronizar Conquistas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<div class="container mt-5">
    <div class="card p-4">
        <div class="row">
            <?php foreach ($jogos as $jogo): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($jogo['titulo']); ?></h5>
                            <p class="card-text"><strong>Tempo de Jogo:</strong> <?php echo round(htmlspecialchars($jogo['tempo_jogo']) / 60, 1); ?> horas</p>
                            <p class="card-text"><strong>Conquistas:</strong></p>
                            <ul>
                                <?php foreach ($jogo['conquistas'] as $conquista): ?>
                                    <li><?php echo htmlspecialchars($conquista['nome_conquista']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <a href="/GameClock/pages/jogoDetalhes.php?jogo_id=<?php echo $jogo['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Detalhes</a>
                                <button class="btn btn-danger btn-sm" onclick="excluirJogo(<?php echo $jogo['id']; ?>)"><i class="bi bi-trash"></i> Excluir</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../resources/footer.php'; ?>