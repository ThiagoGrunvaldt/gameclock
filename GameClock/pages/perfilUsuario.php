<?php
require_once '../resources/header.php';
require_once '../resources/usuario.php';
require_once '../resources/jogo.php';

$id_personalizado = basename($_SERVER['REQUEST_URI']);

$usuario = buscarUsuarioPorIdPersonalizado($id_personalizado);

if (!$usuario) {
    echo "<div class='container mt-5'><p>Perfil não encontrado.</p></div>";
    require_once '../resources/footer.php';
    exit;
}

$jogos = listarJogos($usuario['id']);
$DonoDoPerfil = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === (int)$usuario['id']);

$recomendacoes = [];

if ($DonoDoPerfil) {
    $recomendacoes = buscarRecomendacoesUsuario($_SESSION['usuario_id']);
}

$total_horas = 0;
$total_jogos = count($jogos);
$total_conquistas = 0;

foreach ($jogos as $jogo) {
    $total_horas += $jogo['tempo_jogo'];
    $total_conquistas += count($jogo['conquistas']);
}

?>

<div class="container mt-5">
    <div class="row align-items-stretch">
        <div class="col-md-9">
            <div class="card h-100 p-4 mb-4 position-relative">
                <div class="row">
                    <div class="col-md-3">
                        <img src="/GameClock/<?php echo $usuario['foto']; ?>" alt="Foto do Usuário" class="card-img-top">
                    </div>

                    <div class="col-md-9">
                        <h1><?php echo htmlspecialchars($usuario['nome_usuario']); ?></h1>
                        <p><strong>Usuário desde:</strong> <?php echo htmlspecialchars($usuario['criado_em']); ?></p>
                        <p><?php echo htmlspecialchars($usuario['descricao']); ?></p>
                    </div>
                </div>

                <?php if ($DonoDoPerfil): ?>
                    <a href="/GameClock/pages/configuracaoPerfil.php" class="btn btn-primary position-absolute" style="top: 10px; right: 10px;">Editar Perfil</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100 p-4 mb-4">
                <p><strong>Total de Horas:</strong> <?php echo round($total_horas / 60, 1); ?></p>
                <p><strong>Total de Jogos:</strong> <?php echo $total_jogos; ?></p>
                <p><strong>Total de Conquistas:</strong> <?php echo $total_conquistas; ?></p>
            </div>
        </div>
    </div>
    
    <?php if ($DonoDoPerfil && !empty($recomendacoes)): ?>
        <div class="container mt-5">
            <h2 class="mb-4">Jogos Recomendados para Você</h2>
            <div class="card p-4">
                <div class="row">
                    <?php foreach ($recomendacoes as $jogo): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($jogo['titulo']); ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-5">
    <h2>Jogos</h2>
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
                                <li><?php echo htmlspecialchars($conquista['nome_conquista']); ?> - <?php echo htmlspecialchars($conquista['data_conquista']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../resources/footer.php'; ?>