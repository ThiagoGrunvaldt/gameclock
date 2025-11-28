<?php
session_start();

$isLoggedIn = isset($_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="plagiarism" content="GameClock - Proteção contra plágio">
    <title><?php echo $title ?? 'GameClock'; ?></title>
    <link href="/GameClock/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="mt-navbar">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/GameClock/index.php" title="Página Inicial">
                <img src="/GameClock/resources/gameClockLogo.png" alt="GameClock Logo" style="height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Itens alinhados à esquerda -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <!-- <a class="nav-link" href="/GameClock/pages/rankingUsuarios.php" title="Página Inicial">Ranking Usuários</a> -->
                    </li>
                    <li class="nav-item">
                        <!-- <a class="nav-link" href="/GameClock/pages/todosJogos.php" title="Página Inicial">Todos Jogos</a> -->
                    </li>
                    <li class="nav-item">
                        <!-- <a class="nav-link" href="/GameClock/pages/termos.php" title="Página Inicial">Termos de Serviço</a> -->
                    </li>
                    <li class="nav-item">
                        <!-- <a class="nav-link" href="/GameClock/pages/formularioContato.php" title="Página Inicial">Contato</a> -->
                    </li>
                </ul>
                <!-- Itens alinhados à direita -->
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="/GameClock/<?php echo htmlspecialchars($_SESSION['usuario_foto']); ?>" alt="Foto do Usuário" class="user-photo">
                                <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/GameClock/pages/perfilUsuario.php/<?php echo urlencode($_SESSION['id_personalizado'] ?? ''); ?>">Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="/GameClock/pages/configuracaoPerfil.php">Editar Perfil</a></li>
                                <li><a class="dropdown-item" href="/GameClock/resources/logout.php">Sair</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/GameClock/pages/login.php" title="Login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/GameClock/pages/cadastroUsuario.php" title="Cadastro">Cadastro</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>