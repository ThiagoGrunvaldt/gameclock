<?php
require_once '../resources/header.php';
require_once '../resources/jogo.php';
require_once '../resources/conquista.php';


$jogo_id = $_GET['jogo_id'] ?? null;

if (!$jogo_id) {
    echo "<div class='container mt-5'><p>Jogo não encontrado.</p></div>";
    require_once '../resources/footer.php';
    exit;
}

$jogo = buscarJogoPorId($jogo_id);

if (!$jogo) {
    echo "<div class='container mt-5'><p>Jogo não encontrado.</p></div>";
    require_once '../resources/footer.php';
    exit;
}

$conquistas = listarConquistasPorJogo($jogo_id);

?>

<div class="container mt-5">
    <h1 class="text-center">Detalhes do Jogo</h1>
    <div class="card p-4 mb-4">
        <form method="POST" action="/GameClock/resources/jogo.php?acao=editar" id="formEditarJogo">
            <input type="hidden" name="jogo_id" value="<?php echo $jogo['id']; ?>">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($jogo['titulo']); ?>" required>
                    <!-- <button type="button" class="btn btn-secondary" id="validarNomeJogoBtn">
                        <i class="bi bi-gem"></i> Validar
                    </button> -->
                </div>
                <!-- <small id="validacaoFeedback" class="text-muted"></small> -->
            </div>
            <div class="mb-3">
                <label for="genero" class="form-label">Gênero</label>
                <input type="text" class="form-control" id="genero" name="genero" value="<?php echo htmlspecialchars($jogo['genero']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="data_lancamento" class="form-label">Data de Lançamento</label>
                <input type="date" class="form-control" id="data_lancamento" name="data_lancamento" value="<?php echo htmlspecialchars($jogo['data_lancamento']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tempo_jogo" class="form-label">Tempo de Jogo (em horas)</label>
                <input type="number" class="form-control" id="tempo_jogo" name="tempo_jogo" value="<?php echo htmlspecialchars($jogo['tempo_jogo']); ?>" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary" id="editarJogoBtn">Salvar Alterações</button>
            <button type="button" class="btn btn-danger" onclick="excluirJogo(<?php echo $jogo['id']; ?>)">Excluir Jogo</button>
        </form>
    </div>

    <h2>Conquistas</h2>
    <div class="card p-4">
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addConquistaModal"><i class="bi bi-plus-circle"></i> Adicionar Conquista</button>
        <div id="conquistasContainer">
            <?php foreach ($conquistas as $conquista): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($conquista['nome_conquista']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($conquista['data_conquista']); ?></p>
                        <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editConquistaModal-<?php echo $conquista['id']; ?>"><i class="bi bi-pencil"></i> Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="excluirConquista(<?php echo $conquista['id']; ?>)"><i class="bi bi-trash"></i> Excluir</button>
                    </div>
                </div>

                <div class="modal fade" id="editConquistaModal-<?php echo $conquista['id']; ?>" tabindex="-1" aria-labelledby="editConquistaModalLabel-<?php echo $conquista['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="/GameClock/resources/conquista.php?acao=editar">
                                <input type="hidden" name="conquista_id" value="<?php echo $conquista['id']; ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editConquistaModalLabel-<?php echo $conquista['id']; ?>">Editar Conquista</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="nome_conquista" class="form-label">Nome da Conquista</label>
                                        <input type="text" class="form-control" id="nome_conquista" name="nome_conquista" value="<?php echo htmlspecialchars($conquista['nome_conquista']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_conquista" class="form-label">Data da Conquista</label>
                                        <input type="date" class="form-control" id="data_conquista" name="data_conquista" value="<?php echo htmlspecialchars($conquista['data_conquista']); ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="addConquistaModal" tabindex="-1" aria-labelledby="addConquistaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/GameClock/resources/conquista.php?acao=adicionar">
                <input type="hidden" name="jogo_id" value="<?php echo $jogo['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addConquistaModalLabel">Adicionar Conquista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome_conquista" class="form-label">Nome da Conquista</label>
                        <input type="text" class="form-control" id="nome_conquista" name="nome_conquista" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_conquista" class="form-label">Data da Conquista</label>
                        <input type="date" class="form-control" id="data_conquista" name="data_conquista" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function excluirConquista(conquistaId) {
        if (confirm('Tem certeza que deseja excluir esta conquista?')) {
            window.location.href = `/GameClock/resources/conquista.php?acao=excluir&conquista_id=${conquistaId}`;
        }
    }
</script>
</div>

<script>
    function excluirJogo(jogoId) {
        if (confirm('Tem certeza que deseja excluir este jogo?')) {
            window.location.href = `/GameClock/resources/jogo.php?acao=excluir&jogo_id=${jogoId}`;
        }
    }
</script>

<!-- <script>
    document.getElementById('validarNomeJogoBtn').addEventListener('click', async function () {
    const nomeJogo = document.getElementById('titulo').value.trim();
    const feedback = document.getElementById('validacaoFeedback');
    const editarBtn = document.getElementById('editarJogoBtn');

    if (!nomeJogo) {
        feedback.textContent = 'Por favor, insira o nome do jogo.';
        feedback.classList.add('text-danger');
        editarBtn.disabled = true;
        return;
    }

    feedback.textContent = 'Validando...';
    feedback.classList.remove('text-danger', 'text-success', 'text-warning');

    try {
        const response = await fetch('/GameClock/resources/validacaoJogosGemini.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ nomeJogo })
        });

        const data = await response.json();

        if (data.status === 'sucesso') {
            const resultado = data.resultado.trim().toLowerCase();

            if (resultado === 'válido') {
                feedback.textContent = 'Nome válido!';
                feedback.classList.add('text-success');
                editarBtn.disabled = false;
            } else if (resultado === 'possivelmente válido') {
                feedback.textContent = 'Nome possivelmente válido. Confirme se está correto.';
                feedback.classList.add('text-warning');
                editarBtn.disabled = false;
            } else {
                feedback.textContent = 'Nome inválido. Por favor, insira um nome válido.';
                feedback.classList.add('text-danger');
                editarBtn.disabled = true;
            }
        } else {
            feedback.textContent = 'Erro na validação. Tente novamente.';
            feedback.classList.add('text-danger');
            editarBtn.disabled = true;
        }
    } catch (error) {
        feedback.textContent = 'Erro ao conectar ao servidor. Tente novamente.';
        feedback.classList.add('text-danger');
        editarBtn.disabled = true;
    }
});
</script> -->

<?php require_once '../resources/footer.php'; ?>