<?php
    require_once __DIR__ . '/../../../app/config/config.php';
    require_once APP_PATH . '/config/env.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nomeJogo = $input['nomeJogo'] ?? '';

    if (empty($nomeJogo)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Nome do jogo não fornecido.']);
        exit;
    }

    $resultado = validarNomeJogoGemini($nomeJogo);

    echo json_encode(['status' => 'sucesso', 'resultado' => $resultado]);
    exit;
}

function validarNomeJogoGemini($nomeJogo) {
    $api_key = $_ENV['GEMINI_API_KEY']; 
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$api_key";

    // Prompt personalizado
    $prompt = "Esse nome parece válido para um jogo de videogame? Responda apenas com: 'válido', 'inválido' ou 'possivelmente válido'. Nome: \"$nomeJogo\"";

    // Monta o corpo da requisição
    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => $prompt
                    ]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return "Erro na validação: " . curl_error($ch);
    }

    $resultado = json_decode($response, true);
    curl_close($ch);

    // Log para inspecionar a resposta da API
    file_put_contents('gemini_log.txt', print_r($resultado, true));

    return $resultado['candidates'][0]['content']['parts'][0]['text'] ?? 'Sem resposta';
}