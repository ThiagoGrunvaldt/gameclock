<?php
require_once '../resources/header.php';

$apiKey = 'F58C51E12D02C688C13BB03A26F1B682'; // Sua API Key

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vanityUrl'])) {
    $vanityUrl = htmlspecialchars($_POST['vanityUrl']); // Sanitiza o input do usuário

    // URL para resolver o Vanity URL
    $resolveUrl = "https://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=$apiKey&vanityurl=$vanityUrl";

    $response = @file_get_contents($resolveUrl); // Suprime erros e verifica o retorno
    if ($response === false) {
        echo "Erro ao conectar à API Steam. Verifique sua API Key e Vanity URL.";
        exit;
    }

    $data = json_decode($response, true);

    if (isset($data['response']['success']) && $data['response']['success'] === 1) {
        $steamId = $data['response']['steamid']; // Steam ID numérico
        echo "Steam ID numérico: " . $steamId . "<br>";

        // Agora você pode usar o Steam ID para buscar os jogos do usuário
        $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=$apiKey&steamid=$steamId&format=json&include_appinfo=1";

        $gamesResponse = @file_get_contents($url);
        $gamesData = json_decode($gamesResponse, true);

        if (isset($gamesData['response']['games'])) {
            foreach ($gamesData['response']['games'] as $jogo) {
                echo "Nome: " . $jogo['name'] . "<br>";
                echo "Horas jogadas: " . round($jogo['playtime_forever'] / 60, 2) . "h<br><br>";
            }
        } else {
            echo "Nenhum jogo encontrado ou Steam ID inválido.";
        }
    } else {
        echo "Vanity URL inválido ou não encontrado.";
    }
} else {
    echo "Por favor, insira um Vanity URL.";
}
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Dados Steam</h1>
    <form method="POST" class="text-center">
        <label for="vanityUrl">Vanity URL (Nome de perfil):</label>
        <input type="text" id="vanityUrl" name="vanityUrl" required>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
</div>

<?php
require_once '../resources/footer.php'; 
?>