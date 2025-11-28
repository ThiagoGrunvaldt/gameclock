<?php
session_start(); // Inicia a sessão

// Destroi todas as variáveis de sessão
session_unset();
session_destroy();

// Redireciona para a página de login
header("Location: /GameClock/pages/login.php");
exit;