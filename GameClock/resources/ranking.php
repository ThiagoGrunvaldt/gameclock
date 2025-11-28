<?php
require_once 'conexao.php';

function obterRankingTempoJogo() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT u.nome_usuario, SUM(j.tempo_jogo) AS tempo_total
        FROM usuarios u
        JOIN jogos j ON u.id = j.usuario_id
        GROUP BY u.id
        ORDER BY tempo_total DESC
        LIMIT 3
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obterRankingQuantidadeJogos() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT u.nome_usuario, COUNT(j.id) AS quantidade_jogos
        FROM usuarios u
        JOIN jogos j ON u.id = j.usuario_id
        GROUP BY u.id
        ORDER BY quantidade_jogos DESC
        LIMIT 3
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obterRankingQuantidadeConquistas() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT u.nome_usuario, COUNT(c.id) AS quantidade_conquistas
        FROM usuarios u
        JOIN jogos j ON u.id = j.usuario_id
        JOIN conquistas c ON j.id = c.jogo_id
        GROUP BY u.id
        ORDER BY quantidade_conquistas DESC
        LIMIT 3
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}