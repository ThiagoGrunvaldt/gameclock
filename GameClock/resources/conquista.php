<?php
require_once 'conexao.php';

if (isset($_GET['acao'])) {
    $acao = $_GET['acao'];

    switch ($acao) {
        case 'adicionar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $jogo_id = htmlspecialchars($_POST['jogo_id']);
                $nome_conquista = htmlspecialchars($_POST['nome_conquista']);
                $data_conquista = htmlspecialchars($_POST['data_conquista']);
                adicionarConquista($jogo_id, $nome_conquista, $data_conquista);
                header("Location: /GameClock/pages/jogoDetalhes.php?jogo_id=$jogo_id");
                exit;
            }
            break;

        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $conquista_id = htmlspecialchars($_POST['conquista_id']);
                $nome_conquista = htmlspecialchars($_POST['nome_conquista']);
                $data_conquista = htmlspecialchars($_POST['data_conquista']);
                editarConquista($conquista_id, $nome_conquista, $data_conquista);
                $jogo_id = buscarJogoIdPorConquista($conquista_id);
                header("Location: /GameClock/pages/jogoDetalhes.php?jogo_id=$jogo_id");
                exit;
            }
            break;

        case 'excluir':
            if (isset($_GET['conquista_id'])) {
                $conquista_id = htmlspecialchars($_GET['conquista_id']);
                $jogo_id = buscarJogoIdPorConquista($conquista_id);
                excluirConquista($conquista_id);
                header("Location: /GameClock/pages/jogoDetalhes.php?jogo_id=$jogo_id");
                exit;
            }
            break;

        case 'sincronizar_steam':
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
                    $usuario_id = htmlspecialchars($_POST['usuario_id']);
                    $apiKey = 'F58C51E12D02C688C13BB03A26F1B682'; // Coloque sua API Key
                    
                    $mensagem = sincronizarConquistasSteam($usuario_id, $apiKey);
                    
                    session_start();
                    $_SESSION['mensagem_sinc_conquistas'] = $mensagem;
                    header("Location: /GameClock/pages/configuracaoPerfil.php");
                    exit;
                }
            break;

        default:
            echo "Ação inválida.";
            exit;
    }
}

function listarConquistasPorJogo($jogo_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, nome_conquista, data_conquista FROM conquistas WHERE jogo_id = :jogo_id");
    $stmt->bindParam(':jogo_id', $jogo_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function adicionarConquista($jogo_id, $nome_conquista, $data_conquista) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO conquistas (jogo_id, nome_conquista, data_conquista) VALUES (:jogo_id, :nome_conquista, :data_conquista)");
    $stmt->bindParam(':jogo_id', $jogo_id);
    $stmt->bindParam(':nome_conquista', $nome_conquista);
    $stmt->bindParam(':data_conquista', $data_conquista);
    $stmt->execute();
}

function editarConquista($conquista_id, $nome_conquista, $data_conquista) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE conquistas SET nome_conquista = :nome_conquista, data_conquista = :data_conquista WHERE id = :conquista_id");
    $stmt->bindParam(':nome_conquista', $nome_conquista);
    $stmt->bindParam(':data_conquista', $data_conquista);
    $stmt->bindParam(':conquista_id', $conquista_id);
    $stmt->execute();
}

function excluirConquista($conquista_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM conquistas WHERE id = :conquista_id");
    $stmt->bindParam(':conquista_id', $conquista_id);
    $stmt->execute();
}

function buscarJogoIdPorConquista($conquista_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT jogo_id FROM conquistas WHERE id = :conquista_id");
    $stmt->bindParam(':conquista_id', $conquista_id);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function sincronizarConquistasSteam($usuario_id, $apiKey) {
    $pdo = getConnection(); //

    // 1. Busca o SteamID do usuário
    $stmtGetId = $pdo->prepare("SELECT steam_id FROM usuarios WHERE id = :usuario_id");
    $stmtGetId->execute([':usuario_id' => $usuario_id]);
    $steamId = $stmtGetId->fetchColumn();

    if (empty($steamId)) {
        return "Erro: Nenhuma conta Steam vinculada.";
    }

    // 2. Busca TODOS os jogos (com appid) que o usuário tem no SEU banco
    $stmtGetJogos = $pdo->prepare("SELECT id, appid FROM jogos WHERE usuario_id = :usuario_id AND appid IS NOT NULL");
    $stmtGetJogos->execute([':usuario_id' => $usuario_id]);
    $jogosDoUsuario = $stmtGetJogos->fetchAll(PDO::FETCH_ASSOC);

    if (empty($jogosDoUsuario)) {
        return "Nenhum jogo da Steam sincronizado. Sincronize os jogos primeiro.";
    }

    $conquistasSincronizadas = 0;

    // 3. Prepara o SQL
    $sql = "INSERT INTO conquistas (jogo_id, apiname, nome_conquista, data_conquista) 
            VALUES (:jogo_id, :apiname, :nome_conquista, FROM_UNIXTIME(:data_conquista))
            ON DUPLICATE KEY UPDATE
            data_conquista = VALUES(data_conquista),
            nome_conquista = VALUES(nome_conquista)"; // Atualiza o nome caso ele mude
            
    $stmtInsert = $pdo->prepare($sql);

    // 4. Loop POR CADA JOGO
    foreach ($jogosDoUsuario as $jogo) {
        $local_jogo_id = $jogo['id']; // ID da sua tabela 'jogos'
        $appid = $jogo['appid'];       // ID da Steam

        // 5. CHAMA A API PARA CADA JOGO
        $url = "http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/?key=$apiKey&steamid=$steamId&appid=$appid&l=brazilian";
        $achResponse = @file_get_contents($url);
        
        if ($achResponse === false) continue; // Pula este jogo se der erro
        $achData = json_decode($achResponse, true);

        if (!isset($achData['playerstats']['achievements'])) continue; // Pula se não tiver conquistas
        
        $conquistas = $achData['playerstats']['achievements'];

        // 6. Loop PELAS CONQUISTAS DO JOGO
        foreach ($conquistas as $conq) {
            // SÓ SALVA AS QUE FORAM DESBLOQUEADAS
            if ($conq['achieved'] === 1) {
                $stmtInsert->execute([
                    ':jogo_id' => $local_jogo_id,
                    ':apiname' => $conq['apiname'],
                    ':nome_conquista' => $conq['name'], // Nome de exibição
                    ':data_conquista' => $conq['unlocktime'] // Timestamp UNIX
                ]);
                $conquistasSincronizadas++;
            }
        }
        // É bom dar uma pequena pausa para não sobrecarregar a API
        usleep(100000); // 0.1 segundos
    }

    return "$conquistasSincronizadas conquistas sincronizadas com sucesso!";
}