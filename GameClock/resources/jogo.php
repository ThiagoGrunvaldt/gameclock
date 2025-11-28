<?php
require_once 'conexao.php';

if (isset($_GET['acao'])) {
    $acao = $_GET['acao'];

    switch ($acao) {
        case 'adicionar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $usuario_id = htmlspecialchars($_POST['usuario_id']);
                processarAdicaoJogo($usuario_id);
            }
            break;

        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $jogo_id = htmlspecialchars($_POST['jogo_id']);
                $titulo = htmlspecialchars($_POST['titulo']);
                $tempo_jogo = htmlspecialchars($_POST['tempo_jogo']); // Novo campo
                editarJogo($jogo_id, $titulo, $tempo_jogo);

                header("Location: /GameClock/pages/jogoDetalhes.php?jogo_id=$jogo_id");
                exit;
            }
            break;

        case 'excluir':
            if (isset($_GET['jogo_id'])) {
                $jogo_id = htmlspecialchars($_GET['jogo_id']);
                excluirJogo($jogo_id);

                header("Location: /GameClock/pages/configuracaoPerfil.php");
                exit;
            }
            break;

            case 'sincronizar_steam':
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
                    $usuario_id = htmlspecialchars($_POST['usuario_id']);
                    $apiKey = 'F58C51E12D02C688C13BB03A26F1B682'; // Coloque sua API Key
                    
                    $mensagem = sincronizarJogosSteam($usuario_id, $apiKey);
                    
                    // Redireciona de volta para a página de perfil com uma mensagem
                    session_start();
                    $_SESSION['mensagem_sinc'] = $mensagem;
                    header("Location: /GameClock/pages/configuracaoPerfil.php");
                    exit;
                }
                break;

        default:
            echo "Ação inválida.";
            exit;
    }
}

function listarJogos($usuario_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, titulo, tempo_jogo FROM jogos WHERE usuario_id = :usuario_id");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($jogos as &$jogo) {
        $stmtConquistas = $pdo->prepare("SELECT nome_conquista, data_conquista FROM conquistas WHERE jogo_id = :jogo_id");
        $stmtConquistas->bindParam(':jogo_id', $jogo['id']);
        $stmtConquistas->execute();
        $jogo['conquistas'] = $stmtConquistas->fetchAll(PDO::FETCH_ASSOC);
    }

    return $jogos;
}

function listarTodosJogos() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT j.id, j.titulo, j.tempo_jogo, u.nome_usuario
        FROM jogos j
        JOIN usuarios u ON j.usuario_id = u.id
        ORDER BY j.titulo ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarJogoPorId($jogo_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, usuario_id, titulo, tempo_jogo FROM jogos WHERE id = :jogo_id");
    $stmt->bindParam(':jogo_id', $jogo_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function adicionarJogo($usuario_id, $titulo, $tempo_jogo) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO jogos (usuario_id, titulo, tempo_jogo) VALUES (:usuario_id, :titulo, :tempo_jogo)");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':tempo_jogo', $tempo_jogo);
    $stmt->execute();
    return $pdo->lastInsertId();
}

function processarAdicaoJogo($usuario_id) {
    $titulo = htmlspecialchars($_POST['titulo']);
    $tempo_jogo = htmlspecialchars($_POST['tempo_jogo']);

    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO jogos (usuario_id, titulo, tempo_jogo) VALUES (:usuario_id, :titulo, :tempo_jogo)");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':tempo_jogo', $tempo_jogo);
    $stmt->execute();

    $novoJogoId = $pdo->lastInsertId();
    header("Location: /GameClock/pages/jogoDetalhes.php?jogo_id=$novoJogoId");
    exit;
}

function editarJogo($jogo_id, $titulo, $tempo_jogo) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE jogos SET titulo = :titulo, tempo_jogo = :tempo_jogo WHERE id = :jogo_id");
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':tempo_jogo', $tempo_jogo);
    $stmt->bindParam(':jogo_id', $jogo_id);
    $stmt->execute();
}

function excluirJogo($jogo_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM jogos WHERE id = :jogo_id");
    $stmt->bindParam(':jogo_id', $jogo_id);
    $stmt->execute();
    return $stmt->rowCount();
    
}

function sincronizarJogosSteam($usuario_id, $apiKey) {
    $pdo = getConnection(); //

    // 1. Busca o SteamID do usuário no seu banco
    $stmtGetId = $pdo->prepare("SELECT steam_id FROM usuarios WHERE id = :usuario_id");
    $stmtGetId->execute([':usuario_id' => $usuario_id]);
    $usuario = $stmtGetId->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || empty($usuario['steam_id'])) {
        return "Erro: Nenhuma conta Steam vinculada. Vincule sua conta primeiro.";
    }
    $steamId = $usuario['steam_id'];

    // 2. Busca os jogos na API Steam
    $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=$apiKey&steamid=$steamId&format=json&include_appinfo=1";
    $gamesResponse = @file_get_contents($url);
    if ($gamesResponse === false) { return "Erro ao buscar jogos na API Steam."; }

    $gamesData = json_decode($gamesResponse, true);
    if (!isset($gamesData['response']['games'])) {
        return "Nenhum jogo encontrado ou perfil privado.";
    }
    $jogos = $gamesData['response']['games'];
    $jogosSincronizados = 0;

    // 3. USA O "INSERT ... ON DUPLICATE KEY" (MÁGICA)
    // Isso insere um novo jogo se a combinação (usuario_id, appid) não existir
    // ou ATUALIZA o tempo_jogo e titulo se ela já existir.
    $sql = "INSERT INTO jogos (usuario_id, appid, titulo, tempo_jogo) 
            VALUES (:usuario_id, :appid, :titulo, :tempo_jogo)
            ON DUPLICATE KEY UPDATE 
            titulo = VALUES(titulo), 
            tempo_jogo = VALUES(tempo_jogo)";
            
    $stmtInsert = $pdo->prepare($sql);

    foreach ($jogos as $jogo) {
        $stmtInsert->execute([
            ':usuario_id' => $usuario_id,
            ':appid' => $jogo['appid'],
            ':titulo' => $jogo['name'],
            ':tempo_jogo' => $jogo['playtime_forever'] // Salva em minutos
        ]);
        $jogosSincronizados++;
    }

    return "$jogosSincronizados jogos sincronizados com sucesso!";
}