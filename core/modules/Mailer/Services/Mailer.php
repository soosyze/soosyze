<?php

namespace SoosyzeCore\Mailer\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $mailer;

    private $values = [
        'driver'          => 'mail',
        'smtp_encryption' => '',
        'smtp_host'       => '',
        'smtp_password'   => '',
        'smtp_port'       => '',
        'smtp_username'   => ''
    ];

    public function __construct(\ArrayAccess $config = null)
    {
        if (empty($config[ 'mailer' ])) {
            $this->values += $config[ 'mailer' ];
        }
        $this->mailer = new PHPMailer;
        $this->values[ 'driver' ] === 'mail'
                ? $this->mailer->isMail()
                : $this->mailer->isSMTP();
    }

    public function isSMTP()
    {
        $this->mailer->isSMTP();

        $this->mailer->Host       = $this->config[ 'smtp_host' ];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $this->config[ 'smtp_username' ];
        $this->mailer->Password   = $this->config[ 'smtp_password' ];
        $this->mailer->SMTPSecure = $this->config[ 'smtp_encryption' ] === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->Port       = $this->config[ 'smtp_port' ];
    }

    public function to($email, $name = '')
    {
        $this->mailer->addAddress($email, $name);

        return $this;
    }

    public function addAttachment($attachement)
    {
        $this->mailer->addAttachment($attachement);

        return $this;
    }

    public function addCc($email, $name = '')
    {
        $this->mailer->addCC($email, $name);

        return $this;
    }

    public function addBcc($email, $name = '')
    {
        $this->mailer->addBCC($email, $name);

        return $this;
    }

    public function from($email, $name = '')
    {
        $this->mailer->setFrom($email, $name);

        return $this;
    }

    public function replayTo($email, $name = '')
    {
        $this->mailer->addReplyTo($email, $name);

        return $this;
    }

    public function subject($subj)
    {
        $this->mailer->Subject = $subj;

        return $this;
    }

    public function message($msg)
    {
        $this->mailer->Body = $msg;

        return $this;
    }

    public function isHtml($bool = true)
    {
        $this->mailer->isHTML($bool);

        return $this;
    }

    public function send()
    {
        try {
            return $this->mailer->send();
        } catch (Exception $e) {
            return $this->mailer->ErrorInfo;
        }
    }
}
