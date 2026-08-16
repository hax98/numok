<?php

namespace Numok\Services;

use Resend;

class EmailService
{
    private $brevoApiKey;
    private $resendApiKey;
    private $fromEmail;
    private $fromName;
    private $appName;

    public function __construct()
    {
        global $config;

        $this->brevoApiKey = $config['email']['brevo_api_key'] ?? getenv('BREVO_API_KEY') ?: '';
        $this->resendApiKey = $config['email']['resend_api_key'] ?? getenv('RESEND_API_KEY') ?: '';
        $this->fromEmail = $config['email']['from_address'] ?? getenv('MAIL_FROM_ADDRESS') ?: 'support@repostit.io';
        $this->fromName = $config['email']['from_name'] ?? getenv('MAIL_FROM_NAME') ?: 'Repostit Partners';
        $this->appName = $config['app']['name'] ?? getenv('APP_NAME') ?: 'Repostit';
    }

    public function sendWelcomeEmail(string $to, string $name): void
    {
        $this->sendEmail($to, "Welcome to {$this->appName}", "
                    <h1>Welcome, {$name}!</h1>
                    <p>We are excited to have you on board at {$this->appName}.</p>
                    <p>You can now log in to your partner dashboard and start promoting our programs.</p>
                    <p>If you have any questions, feel free to reply to this email.</p>
                    <br>
                    <p>Best regards,</p>
                    <p>The {$this->appName} Team</p>
                ");
    }

    public function sendPasswordResetEmail(string $to, string $resetLink): void
    {
        $this->sendEmail($to, 'Reset Your Password', "
                    <h1>Password Reset Request</h1>
                    <p>We received a request to reset your password for your {$this->appName} account.</p>
                    <p>Click the link below to reset your password:</p>
                    <p><a href=\"{$resetLink}\">Reset Password</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                    <p>This link will expire in 60 minutes.</p>
                    <br>
                    <p>Best regards,</p>
                    <p>The {$this->appName} Team</p>
                ");
    }

    private function sendEmail(string $to, string $subject, string $html): void
    {
        try {
            if ($this->brevoApiKey !== '') {
                $this->sendViaBrevo($to, $subject, $html);
                return;
            }

            if ($this->resendApiKey !== '' && $this->resendApiKey !== 'RESEND_API_KEY') {
                Resend::client($this->resendApiKey)->emails->send([
                    'from' => "{$this->fromName} <{$this->fromEmail}>",
                    'to' => [$to],
                    'subject' => $subject,
                    'html' => $html,
                ]);
                return;
            }

            error_log('Email provider is not configured; skipped email delivery.');
        } catch (\Throwable $e) {
            error_log("Failed to send email to {$to}: " . $e->getMessage());
        }
    }

    private function sendViaBrevo(string $to, string $subject, string $html): void
    {
        $payload = json_encode([
            'sender' => ['name' => $this->fromName, 'email' => $this->fromEmail],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $html,
        ], JSON_THROW_ON_ERROR);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "accept: application/json\r\ncontent-type: application/json\r\napi-key: {$this->brevoApiKey}\r\n",
                'content' => $payload,
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $response = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $context);
        $statusLine = $http_response_header[0] ?? '';
        if ($response === false || !preg_match('/\s2\d{2}\s/', $statusLine)) {
            throw new \RuntimeException('Brevo transactional email request failed.');
        }
    }
}
