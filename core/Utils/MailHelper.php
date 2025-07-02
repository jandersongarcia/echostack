<?php
namespace Core\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    private PHPMailer $mailer;

    /**
     * MailHelper constructor.
     * Inicializa o PHPMailer com as configurações do config/php_mailer.php
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/php_mailer.php';

        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['host'];
        $this->mailer->Port = $config['port'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['username'];
        $this->mailer->Password = $config['password'];
        $this->mailer->SMTPSecure = $config['smtp_secure']; // 'ssl' ou 'tls'

        $this->mailer->CharSet = $config['charset'];
        $this->mailer->isHTML($config['is_html']);
        $this->mailer->setFrom($config['from_email'], $config['from_name']);
    }

    /**
     * Envia um e-mail.
     *
     * @param string|array $to Destinatário(s)
     * @param string $subject Assunto do e-mail
     * @param string $body Conteúdo (HTML ou texto)
     * @param array $attachments Arquivos anexos (paths absolutos)
     * @return bool
     */
    public function send($to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            // Limpa destinatários anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Adiciona destinatários
            if (is_array($to)) {
                foreach ($to as $email) {
                    $this->mailer->addAddress($email);
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Define assunto e corpo
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Anexos, se houver
            foreach ($attachments as $file) {
                $this->mailer->addAttachment($file);
            }

            // Envia o e-mail
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $e->getMessage());
            return false;
        }
    }
}
