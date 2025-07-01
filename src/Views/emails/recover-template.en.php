<?php
/**
 * Email Template: Password Recovery
 * Atenção: Não altere as variáveis PHP abaixo.
 * As variáveis $name e $link são obrigatórias e controladas pela camada de serviço.
 * Modifique apenas o texto ou o layout HTML se quiser personalizar o visual.
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Recovery</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #007BFF;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <p>Hello <?= htmlspecialchars($name) ?>,</p>

    <p>We received a request to reset your password.</p>

    <p>Click the button below to choose a new password:</p>

    <p>
        <a href="<?= htmlspecialchars($link) ?>" class="button">Reset Password</a>
    </p>

    <p>If you prefer, you can also copy and paste this link into your browser:</p>
    <p><?= htmlspecialchars($link) ?></p>

    <p>If you didn’t request this change, you can safely ignore this email. Your account remains secure.</p>

    <div class="footer">
        This is an automated message. Please do not reply.
    </div>
</body>
</html>
