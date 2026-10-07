<?php
namespace Aplicacao\Helpers;

require_once __DIR__ . '/../Libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../Libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../Libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailHelper
{
    /** Envio simples em texto puro (usado para notificações internas). */
    public static function enviar(string $destinatario, string $nomeDestinatario, string $assunto, string $corpoTexto): bool
    {
        return self::enviarBase($destinatario, $nomeDestinatario, $assunto, $corpoTexto, null);
    }

    /**
     * Envio com HTML com a identidade visual do CHRONA + fallback em texto puro.
     * $conteudoHtml é apenas o miolo (parágrafos, links, etc.) — o cabeçalho/rodapé
     * da marca são adicionados automaticamente pelo template().
     */
    public static function enviarHtml(string $destinatario, string $nomeDestinatario, string $assunto, string $conteudoHtml, string $corpoTexto): bool
    {
        $html = self::template($conteudoHtml);
        return self::enviarBase($destinatario, $nomeDestinatario, $assunto, $corpoTexto, $html);
    }

    private static function enviarBase(string $destinatario, string $nomeDestinatario, string $assunto, string $corpoTexto, ?string $corpoHtml): bool
    {
        require_once __DIR__ . '/../../config/smtp.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($destinatario, $nomeDestinatario);

            $mail->Subject = $assunto;

            if ($corpoHtml !== null) {
                $mail->isHTML(true);
                $mail->Body    = $corpoHtml;
                $mail->AltBody = $corpoTexto;
            } else {
                $mail->isHTML(false);
                $mail->Body = $corpoTexto;
            }

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('[CHRONA] Falha ao enviar e-mail para ' . $destinatario . ': ' . $mail->ErrorInfo);
            return false;
        }
    }

    /** Envolve o conteúdo com o cabeçalho (logo CHRONA) e rodapé padrão. */
    private static function template(string $conteudoHtml): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:24px 0;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;max-width:600px;width:100%;">
<tr>
<td style="background-color:#0066ff;background-image:linear-gradient(135deg,#0066ff,#00c9ff);padding:24px 32px;">
<table role="presentation" cellpadding="0" cellspacing="0"><tr>
<td style="width:36px;height:36px;background-color:#ffffff;border-radius:8px;text-align:center;vertical-align:middle;font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:15px;color:#0066ff;">CH</td>
<td style="padding-left:10px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:19px;color:#ffffff;">CHRONA</td>
</tr></table>
</td>
</tr>
<tr>
<td style="padding:32px;color:#374151;font-size:15px;line-height:1.65;">
{$conteudoHtml}
</td>
</tr>
<tr>
<td style="padding:18px 32px;background-color:#f9fafb;border-top:1px solid #e5e7eb;color:#9ca3af;font-size:12px;text-align:center;">
Equipe CHRONA &middot; <a href="https://engmarcosalveres.com.br" style="color:#0066ff;text-decoration:none;">engmarcosalveres.com.br</a>
</td>
</tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
