<?php
require_once '../resources/header.php';
require_once '../resources/usuario.php'; // Precisa da nova função listarTodosUsuarios()
require_once '../resources/conexao.php'; // Precisamos de acesso direto ao PDO

// --- 1. Buscar todos os usuários para o dropdown ---
$lista_de_usuarios = listarTodosUsuarios();
$usuario_selecionado = null;
$log_texto = "";

// --- 2. Preparar dados para os gráficos (começam vazios) ---
$dados_grafico_similaridade_json = 'null';
$dados_grafico_contagem_json = 'null';

/**
 * Função auxiliar (versão PHP do get_user_games do Python)
 * Busca os 'appid' de um usuário como um array.
 */
function get_user_games_php($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT appid FROM jogos WHERE usuario_id = :id AND appid IS NOT NULL");
    $stmt->execute([':id' => $user_id]);
    // array_column transforma [['appid'=>1], ['appid'=>2]] em [1, 2]
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'appid');
}

// --- 3. Processamento Principal (Se um usuário foi selecionado) ---
if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
    $pdo = getConnection();
    $usuario_id_alvo = intval($_GET['usuario_id']);
    
    // Busca o nome do usuário alvo
    $stmt = $pdo->prepare("SELECT nome_usuario FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id_alvo]);
    $usuario_selecionado = $stmt->fetch(PDO::FETCH_ASSOC);

    $log_texto = "Iniciando análise para: " . htmlspecialchars($usuario_selecionado['nome_usuario']) . " (ID: $usuario_id_alvo)\n";

    // --- LÓGICA DE RECOMENDAÇÃO (REIMPLEMENTADA EM PHP) ---
    
    // Passo 1: Obter dados do usuário alvo e de todos os outros
    $jogos_usuario_x = get_user_games_php($pdo, $usuario_id_alvo);
    $log_texto .= "Utilizador Alvo tem " . count($jogos_usuario_x) . " jogos.\n";

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id != ?");
    $stmt->execute([$usuario_id_alvo]);
    $outros_usuarios_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $log_texto .= "Comparando com " . count($outros_usuarios_ids) . " outros utilizadores...\n";

    // Passo 2: Calcular "Pontuação de Similaridade"
    $similaridade_scores = [];
    foreach ($outros_usuarios_ids as $outro) {
        $user_id = $outro['id'];
        $jogos_outro_usuario = get_user_games_php($pdo, $user_id);
        
        $jogos_em_comum = array_intersect($jogos_usuario_x, $jogos_outro_usuario);
        $score = count($jogos_em_comum);
        
        if ($score > 0) {
            $similaridade_scores[] = ['id' => $user_id, 'score' => $score];
        }
    }
    $log_texto .= "Ranking de similaridade calculado.\n";

    // Passo 3: Selecionar os 5 melhores
    // usort ordena o array por 'score' (descendente)
    usort($similaridade_scores, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    $top_5_similares = array_slice($similaridade_scores, 0, 5);
    $log_texto .= "Top 5 utilizadores similares selecionados.\n";

    // --- Preparar Dados para o Gráfico 1 (Similaridade) ---
    $labels_chart1 = [];
    $data_chart1 = [];
    foreach ($top_5_similares as $user) {
        $stmt = $pdo->prepare("SELECT nome_usuario FROM usuarios WHERE id = ?");
        $stmt->execute([$user['id']]);
        $labels_chart1[] = $stmt->fetchColumn(); // Nome do usuário
        $data_chart1[] = $user['score']; // Pontuação
    }
    $dados_grafico_similaridade_json = json_encode(['labels' => $labels_chart1, 'data' => $data_chart1]);

    // Passo 4: Agregar jogos dos Top 5 e Contar
    $lista_de_jogos_dos_similares = [];
    foreach ($top_5_similares as $user) {
        $jogos_do_similar = get_user_games_php($pdo, $user['id']);
        $lista_de_jogos_dos_similares = array_merge($lista_de_jogos_dos_similares, $jogos_do_similar);
    }
    
    // array_count_values é o "collections.Counter" do PHP
    $contagem_de_jogos = array_count_values($lista_de_jogos_dos_similares);
    arsort($contagem_de_jogos); // Ordena pela contagem (valor), mantendo o appid (chave)
    $log_texto .= "Contagem de jogos dos utilizadores similares concluída.\n";

    // Passo 5: Filtrar (jogos que o alvo não tem) e pegar Top 5
    $recomendacoes_finais = [];
    $jogos_para_grafico_2 = [];
    foreach ($contagem_de_jogos as $appid => $contagem) {
        if (!in_array($appid, $jogos_usuario_x)) {
            // Guarda o appid e a contagem (para o gráfico 2)
            $jogos_para_grafico_2[$appid] = $contagem;
            if (count($jogos_para_grafico_2) >= 5) {
                break;
            }
        }
    }
    $log_texto .= "Recomendações finais filtradas. Processo concluído.\n";
    
    // --- Preparar Dados para o Gráfico 2 (Contagem de Jogos) ---
    $labels_chart2 = [];
    $data_chart2 = [];
    if (!empty($jogos_para_grafico_2)) {
        // Precisamos dos nomes dos jogos
        $placeholders = rtrim(str_repeat('?,', count($jogos_para_grafico_2)), ',');
        $appids_para_buscar = array_keys($jogos_para_grafico_2);
        
        $sql = "SELECT DISTINCT appid, titulo FROM jogos WHERE appid IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($appids_para_buscar);
        $nomes_jogos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Cria um map [appid => titulo]

        foreach ($jogos_para_grafico_2 as $appid => $contagem) {
            $labels_chart2[] = $nomes_jogos[$appid] ?? "Jogo (ID: $appid)";
            $data_chart2[] = $contagem;
        }
    }
    $dados_grafico_contagem_json = json_encode(['labels' => $labels_chart2, 'data' => $data_chart2]);
}

?>

<div class="container mt-5">
    <div class="card p-4 mb-4">
        <h1 class="mb-4">Apresentação do Sistema de Recomendação</h1>
        <p>Esta página demonstra visualmente como o sistema de recomendação funciona. O processo real acontece em Python, mas a lógica é recriada aqui em PHP para fins de visualização.</p>

        <form method="GET" action="apresentacao.php" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="usuario_id" class="form-label">Selecione um Usuário para Analisar:</label>
                <select name="usuario_id" id="usuario_id" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($lista_de_usuarios as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo (isset($_GET['usuario_id']) && $_GET['usuario_id'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nome_usuario']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">Analisar</button>
            </div>
        </form>
    </div>

    <?php if ($usuario_selecionado): ?>
    <div class="row">
        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <h2>Como Funciona? (Passo a Passo)</h2>
                
                <hr>

                <h3>Passo 1: A Chamada PHP</h3>
                <p>Quando o perfil do utilizador é carregado, o PHP chama a função `buscarRecomendacoesUsuario()`. Esta função usa `shell_exec()` para executar o script Python, passando o ID do utilizador logado.</p>
                <pre><code class="language-php">// Em usuario.php
$caminho_python_exe = "C:\\...\\python.exe";
$caminho_script = __DIR__ . '/api_recomendacao.py';
$comando = escapeshellarg($caminho_python_exe) . " " . escapeshellarg($caminho_script) . " " . intval($usuario_id);
$json_output = shell_exec($comando);
$appids_recomendados = json_decode($json_output, true);
</code></pre>
                
                <hr>

                <h3>Passo 2: O Script Python (`api_recomendacao.py`)</h3>
                <p>O script Python faz o trabalho pesado:</p>
                <ol>
                    <li><b>Imports:</b> Importa `sys` (para ler o ID), `json` (para enviar a resposta) e `mysql.connector` (para ligar à BD).</li>
                    <li><b>Conexão:</b> Liga-se à base de dados `game_clock`.</li>
                    <li><b>Ranking de Afinidade:</b>
                        <ul>
                            <li>Busca os jogos do utilizador alvo (Utilizador X).</li>
                            <li>Entra num loop por todos os outros utilizadores.</li>
                            <li>Calcula a **interseção** (jogos em comum) entre o Utilizador X e cada "outro utilizador".</li>
                            <li>A contagem de jogos em comum torna-se a "pontuação de similaridade".</li>
                        </ul>
                    </li>
                    <li><b>Seleção (Top 5):</b>
                        <ul>
                            <li>Ordena todos os utilizadores pela pontuação de similaridade.</li>
                            <li>Seleciona os **5 utilizadores mais similares** (os "gêmeos de gosto").</li>
                        </ul>
                    </li>
                    <li><b>Contagem de Jogos:</b>
                        <ul>
                            <li>Cria uma lista gigante com *todos* os jogos possuídos por esses 5 "gêmeos".</li>
                            <li>Usa `collections.Counter` para contar quais jogos aparecem mais vezes nessa lista.</li>
                        </ul>
                    </li>
                    <li><b>Filtragem e Saída:</b>
                        <ul>
                            <li>Pega os jogos mais comuns (da contagem anterior).</li>
                            <li>Verifica se o Utilizador X **ainda não tem** o jogo.</li>
                            <li>Os primeiros 5 jogos que ele não tem são adicionados à lista final.</li>
                            <li>O script imprime a lista final como um JSON.</li>
                        </ul>
                    </li>
                </ol>

                <hr>

                <h3>Passo 3: Log da Execução (Demo)</h3>
                <p>Este log mostra o que a lógica (recriada em PHP) fez para gerar os gráficos ao lado:</p>
                <pre><code><?php echo $log_texto; ?></code></pre>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-4 mb-4">
                <h4>Gráfico 1: Top 5 Utilizadores Similares</h4>
                <p>Estes são os 5 utilizadores com mais jogos em comum com <strong><?php echo htmlspecialchars($usuario_selecionado['nome_usuario']); ?></strong>. A altura da barra é a pontuação (nº de jogos em comum).</p>
                <canvas id="graficoSimilaridade"></canvas>
            </div>
            
            <div class="card p-4">
                <h4>Gráfico 2: Top 5 Recomendações</h4>
                <p>Estes são os 5 jogos mais comuns entre os utilizadores similares (do Gráfico 1) que <strong><?php echo htmlspecialchars($usuario_selecionado['nome_usuario']); ?></strong> ainda não possui.</p>
                <canvas id="graficoContagem"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Passar dados do PHP para o JavaScript
const dadosSim = <?php echo $dados_grafico_similaridade_json; ?>;
const dadosCont = <?php echo $dados_grafico_contagem_json; ?>;

// Renderizar Gráfico 1
if (dadosSim && document.getElementById('graficoSimilaridade')) {
    const ctxSim = document.getElementById('graficoSimilaridade').getContext('2d');
    new Chart(ctxSim, {
        type: 'bar',
        data: {
            labels: dadosSim.labels,
            datasets: [{
                label: 'Pontuação de Similaridade (Jogos em Comum)',
                data: dadosSim.data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { // Garante que a escala Y seja de números inteiros
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Renderizar Gráfico 2
if (dadosCont && document.getElementById('graficoContagem')) {
    const ctxCont = document.getElementById('graficoContagem').getContext('2d');
    new Chart(ctxCont, {
        type: 'bar',
        data: {
            labels: dadosCont.labels,
            datasets: [{
                label: 'Contagem (Popularidade entre os "Gêmeos")',
                data: dadosCont.data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
</script>

<?php require_once '../resources/footer.php'; ?>