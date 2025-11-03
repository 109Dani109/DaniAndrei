<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nume = htmlspecialchars($_POST['nume']);
    $email = htmlspecialchars($_POST['email']);
    $mesaj = htmlspecialchars($_POST['mesaj']);

    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'danyandrei109@gmail.com';
        $mail->Password   = 'hspu dxxq yivq fgvi'; // App Password from Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipiente
        $mail->setFrom('danyandrei109@gmail.com', 'Formular Contact AutoLux');
        $mail->addAddress('danyandrei109@gmail.com'); // mesaje vin la tine

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Mesaj nou de la $nume";
        $mail->Body = "
            <strong>Nume:</strong> $nume<br>
            <strong>Email:</strong> $email<br><br>
            <strong>Mesaj:</strong><br>$mesaj
        ";

        $mail->send();
        echo "success";
    } catch (Exception $e) {
        echo "error: {$mail->ErrorInfo}";
    }
}
