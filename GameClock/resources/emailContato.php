<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome']);
    $email = htmlspecialchars($_POST['email']);
    $mensagem = htmlspecialchars($_POST['mensagem']);

    $remetenteEmail = 'gameclockoficial@gmail.com';
    $destinatarioEmail = 'thiagogrunvaldt@gmail.com';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Username = $remetenteEmail;
        $mail->Password = 'nfll nuki ecgf xfue';
        $mail->SMTPSecure = 'tls';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;

        $mail->setFrom($remetenteEmail, 'GameClock');
        $mail->addAddress($destinatarioEmail, 'Contato GameClock');

        $mail->isHTML(true);
        $mail->Subject = 'Mensagem de Contato - GameClock';
        $mail->Body = "<p><strong>Nome:</strong> {$nome}</p>
                       <p><strong>E-mail:</strong> {$email}</p>
                       <p><strong>Mensagem:</strong></p>
                       <p>{$mensagem}</p>";
        $mail->AltBody = "Nome: {$nome}\nE-mail: {$email}\nMensagem:\n{$mensagem}";

        $mail->send();

        $pdo = getConnection();
        $stmtInsert = $pdo->prepare("
            INSERT INTO emails_contato (nome, email, mensagem, remetente_email, destinatario_email)
            VALUES (:nome, :email, :mensagem, :remetente_email, :destinatario_email)
        ");
        $stmtInsert->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmtInsert->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtInsert->bindParam(':mensagem', $mensagem, PDO::PARAM_STR);
        $stmtInsert->bindParam(':remetente_email', $remetenteEmail, PDO::PARAM_STR);
        $stmtInsert->bindParam(':destinatario_email', $destinatarioEmail, PDO::PARAM_STR);
        $stmtInsert->execute();

        echo "<div class='alert alert-success text-center'>Mensagem enviada com sucesso!</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger text-center'>Erro ao enviar a mensagem: {$mail->ErrorInfo}</div>";
    }
}
?>
