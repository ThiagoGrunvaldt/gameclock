<?php
require_once 'conexao.php';
require_once 'uploadFoto.php';

function cadastrarUsuario($nome_usuario, $email, $senha, $descricao, $id_personalizado, $foto) {
    $pdo = getConnection();

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id_personalizado)) {
        return "O ID personalizado contém caracteres inválidos. Use apenas letras, números, hífen e sublinhado.";
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_personalizado = :id_personalizado");
    $stmt->bindParam(':id_personalizado', $id_personalizado);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        return "O ID personalizado já está em uso. Escolha outro.";
    }

    $stmtEmail = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $stmtEmail->bindParam(':email', $email);
    $stmtEmail->execute();
    if ($stmtEmail->fetchColumn() > 0) {
        return "O email já está em uso. Escolha outro.";
    }

    $foto_resultado = uploadFoto($foto);
    if (strpos($foto_resultado, "Erro") !== false || strpos($foto_resultado, "inválido") !== false) {
        return $foto_resultado;
    }

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome_usuario, email, senha, descricao, id_personalizado, foto) 
                           VALUES (:nome_usuario, :email, :senha, :descricao, :id_personalizado, :foto)");
    $stmt->bindParam(':nome_usuario', $nome_usuario);
    $stmt->bindParam(':email', $email);
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt->bindParam(':senha', $senhaHash);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':id_personalizado', $id_personalizado);
    $stmt->bindParam(':foto', $foto_resultado);
    $stmt->execute();

    return "Usuário cadastrado com sucesso!";
}

function loginUsuario($email, $senha) {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT id, id_personalizado, nome_usuario, senha, descricao, foto FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        return "Email ou senha inválidos.";
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['id_personalizado'] = $usuario['id_personalizado'];
    $_SESSION['steam_id'] = $usuario['steam_id'];
    $_SESSION['usuario_nome'] = $usuario['nome_usuario'];
    $_SESSION['descricao'] = $usuario['descricao'];
    $_SESSION['usuario_foto'] = $usuario['foto'];

    return "Login realizado com sucesso!";
}

function buscarUsuarioPorIdPersonalizado($id_personalizado) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, steam_id, nome_usuario, email, foto, descricao, criado_em FROM usuarios WHERE id_personalizado = :id_personalizado");
    $stmt->bindParam(':id_personalizado', $id_personalizado);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function editarPerfil($usuario_id, $nome_usuario, $descricao, $id_personalizado, $foto) {
    $pdo = getConnection();

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id_personalizado)) {
        return "O ID personalizado contém caracteres inválidos. Use apenas letras, números, hífen e sublinhado.";
    }

    $stmtId = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_personalizado = :id_personalizado AND id != :usuario_id");
    $stmtId->bindParam(':id_personalizado', $id_personalizado);
    $stmtId->bindParam(':usuario_id', $usuario_id);
    $stmtId->execute();
    if ($stmtId->fetchColumn() > 0) {
        return "O ID personalizado já está em uso. Escolha outro.";
    }

    $foto_resultado = null;
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $foto_resultado = uploadFoto($foto);
        if (strpos($foto_resultado, "Erro") !== false || strpos($foto_resultado, "inválido") !== false) {
            return $foto_resultado;
        }
    }

    $stmtUpdate = $pdo->prepare("UPDATE usuarios SET nome_usuario = :nome_usuario, descricao = :descricao, id_personalizado = :id_personalizado, foto = COALESCE(:foto, foto) WHERE id = :usuario_id");
    $stmtUpdate->bindParam(':nome_usuario', $nome_usuario);
    $stmtUpdate->bindParam(':descricao', $descricao);
    $stmtUpdate->bindParam(':id_personalizado', $id_personalizado);
    $stmtUpdate->bindParam(':foto', $foto_resultado);
    $stmtUpdate->bindParam(':usuario_id', $usuario_id);
    $stmtUpdate->execute();

    $stmtFetch = $pdo->prepare("SELECT id, id_personalizado, nome_usuario, descricao, foto FROM usuarios WHERE id = :usuario_id");
    $stmtFetch->bindParam(':usuario_id', $usuario_id);
    $stmtFetch->execute();
    $usuarioAtualizado = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    $_SESSION['usuario_id'] = $usuarioAtualizado['id'];
    $_SESSION['id_personalizado'] = $usuarioAtualizado['id_personalizado'];
    $_SESSION['usuario_nome'] = $usuarioAtualizado['nome_usuario'];
    $_SESSION['descricao'] = $usuarioAtualizado['descricao'];
    $_SESSION['usuario_foto'] = $usuarioAtualizado['foto'];

    return "Perfil atualizado com sucesso!";
}

function vincularContaSteam($usuario_id, $entradaUsuario, $apiKey) {
    if (empty($entradaUsuario)) {
        return "URL de perfil não pode estar vazia.";
    }

    $entradaUsuario = trim($entradaUsuario, "/ ");

    $steamId64 = null;
    $idPersonalizado = null;

    if (preg_match('/^https:\/\/steamcommunity\.com\/id\/([\w-]+)$/', $entradaUsuario, $matches)) {
        $idPersonalizado = $matches[1];
    } elseif (preg_match('/^https:\/\/steamcommunity\.com\/profiles\/(\d{17})$/', $entradaUsuario, $matches)) {
        $steamId64 = $matches[1];
    } elseif (preg_match('/^\d{17}$/', $entradaUsuario)) {
        $steamId64 = $entradaUsuario;
    } else {
        $idPersonalizado = $entradaUsuario;
    }

    if (isset($idPersonalizado)) {
        $url = "https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key=$apiKey&vanityurl=" . urlencode($idPersonalizado);
        $response = @file_get_contents($url);
        if ($response === false) {
             return "Erro: Não foi possível conectar à API Steam para resolver o ID.";
        }
        
        $data = json_decode($response, true);

        if (isset($data['response']['success']) && $data['response']['success'] == 1) {
            $steamId64 = $data['response']['steamid']; // Agora temos o ID numérico
        } else {
            return "Erro: O ID/URL personalizado '$idPersonalizado' não foi encontrado.";
        }
    }

    if (!isset($steamId64) || !preg_match('/^\d{17}$/', $steamId64)) {
        return "Erro: SteamID inválido ou não pôde ser determinado a partir da entrada.";
    }

    try {
        $pdo = getConnection();

        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE steam_id = :steam_id AND id != :usuario_id");
        $stmtCheck->execute([':steam_id' => $steamId64, ':usuario_id' => $usuario_id]);
        if ($stmtCheck->fetch()) {
            return "Erro: Esta conta Steam já está vinculada a outro usuário.";
        }

        $stmtUpdate = $pdo->prepare("UPDATE usuarios SET steam_id = :steam_id WHERE id = :usuario_id");
        $stmtUpdate->execute([':steam_id' => $steamId64, ':usuario_id' => $usuario_id]);

        return "Conta Steam validada e vinculada com sucesso!";

    } catch (PDOException $e) {
        return "Erro de banco de dados: " . $e->getMessage();
    }
}

function buscarUsuarioPorId($usuario_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, id_personalizado, steam_id, nome_usuario, email, foto, descricao, criado_em FROM usuarios WHERE id = :usuario_id");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarSteamIdPorUsuarioId($usuario_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT steam_id FROM usuarios WHERE id = :usuario_id");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    return $stmt->fetchColumn(); // Retorna o steam_id (string) ou null/false se não houver
}

function buscarRecomendacoesUsuario($usuario_id) {
    $caminho_script = __DIR__ . "/api_recomendacao.py";

    $comando = "python " . escapeshellcmd($caminho_script) . " " . intval($usuario_id);

    $comando .= " 2>&1";

    $json_output = shell_exec($comando);

    if (empty($json_output)) {
        return []; 
    }

    $appids_recomendados = json_decode($json_output, true);

    if (empty($appids_recomendados) || !is_array($appids_recomendados)) {
        return []; 
    }

    $pdo = getConnection();
    $placeholders = rtrim(str_repeat('?,', count($appids_recomendados)), ',');
    
    $sql = "SELECT DISTINCT appid, titulo 
            FROM jogos 
            WHERE appid IN ($placeholders)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($appids_recomendados)); 
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarTodosUsuarios() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, nome_usuario FROM usuarios ORDER BY nome_usuario ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
