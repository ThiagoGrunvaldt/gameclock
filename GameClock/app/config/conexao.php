<?php

function getConnection() {
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    try {
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname", 
            $user, 
            $pass
        );

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
        
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}