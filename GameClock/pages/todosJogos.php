<?php
require_once '../resources/header.php';
require_once '../resources/jogo.php';

$jogos = listarTodosJogos();
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Todos os Jogos</h1>
    <div class="row">
        <?php foreach ($jogos as $jogo): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($jogo['titulo']); ?></h5>
                        <p class="card-text"><strong>Gênero:</strong> <?php echo htmlspecialchars($jogo['genero']); ?></p>
                        <p class="card-text"><strong>Data de Lançamento:</strong> <?php echo htmlspecialchars($jogo['data_lancamento']); ?></p>
                        <p class="card-text"><strong>Tempo de Jogo:</strong> <?php echo htmlspecialchars($jogo['tempo_jogo']); ?> horas</p>
                        <p class="card-text"><strong>Usuário:</strong> <?php echo htmlspecialchars($jogo['nome_usuario']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
require_once '../resources/footer.php';
?>