<?php
/**
 * Modelo de E-mail: Recuperação de Senha
 * Atenção: Não altere as variáveis PHP abaixo.
 * As variáveis $name e $link são obrigatórias e controladas pela camada de serviço.
 * Modifique apenas o texto ou o layout HTML se quiser personalizar o visual.
 */
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperação de Senha</title>
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
    <p>Olá <?= htmlspecialchars($name) ?>,</p>

    <p>Recebemos uma solicitação para redefinir sua senha.</p>

    <p>Clique no botão abaixo para escolher uma nova senha:</p>

    <p>
        <a href="<?= htmlspecialchars($link) ?>" class="button">Redefinir Senha</a>
    </p>

    <p>Se preferir, você também pode copiar e colar este link no seu navegador:</p>
    <p><?= htmlspecialchars($link) ?></p>

    <p>Este link expira em <?php echo $expirationHours; ?> horas.</p>

    <p>Se você não solicitou essa alteração, pode ignorar este e-mail com segurança. Sua conta continua protegida.</p>

    <div class="footer">
        Esta é uma mensagem automática. Por favor, não responda.
    </div>
</body>
</html>
