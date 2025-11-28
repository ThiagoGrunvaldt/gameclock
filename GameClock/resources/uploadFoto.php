<?php

function uploadFoto($file, $destino = "../uploads/") {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $mensagem = "";

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $foto_extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($foto_extensao, $extensoes_permitidas)) {
            $foto_nome = uniqid() . "." . $foto_extensao;
            $foto_caminho_absoluto = realpath($destino) . DIRECTORY_SEPARATOR . $foto_nome;
            $foto_caminho_relativo = "uploads/" . $foto_nome;

            if (move_uploaded_file($file['tmp_name'], $foto_caminho_absoluto)) {
                return $foto_caminho_relativo;
            } else {
                $mensagem = "Erro ao salvar a foto. Verifique as permissões do diretório.";
            }
        } else {
            $mensagem = "Tipo de arquivo inválido. Apenas imagens (jpg, jpeg, png, gif) são permitidas.";
        }
    } else {
        $mensagem = "Nenhuma foto foi enviada ou ocorreu um erro no upload.";
    }

    return $mensagem;
}