<?php
require_once '../resources/header.php';
require_once '../resources/ranking.php';

$rankingTempoJogo = obterRankingTempoJogo();
$rankingQuantidadeJogos = obterRankingQuantidadeJogos();
$rankingQuantidadeConquistas = obterRankingQuantidadeConquistas();
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Ranking de Usuários</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Top 3 - Tempo de Jogo</h2>
            <ul>
                <?php foreach ($rankingTempoJogo as $usuario): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($usuario['nome_usuario']); ?></strong>: 
                        <?php echo htmlspecialchars($usuario['tempo_total']); ?> horas
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Top 3 - Quantidade de Jogos</h2>
            <ul>
                <?php foreach ($rankingQuantidadeJogos as $usuario): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($usuario['nome_usuario']); ?></strong>: 
                        <?php echo htmlspecialchars($usuario['quantidade_jogos']); ?> jogos
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Top 3 - Quantidade de Conquistas</h2>
            <ul>
                <?php foreach ($rankingQuantidadeConquistas as $usuario): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($usuario['nome_usuario']); ?></strong>: 
                        <?php echo htmlspecialchars($usuario['quantidade_conquistas']); ?> conquistas
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php
require_once '../resources/footer.php';
?>