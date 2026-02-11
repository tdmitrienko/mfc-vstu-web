<?php

namespace App\Service\Communication;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EmailSender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $fromEmail,
        private readonly string $supportEmail,
        private readonly string $fromName = 'App',
    ) {}

    public function sendOtpLoginEmail(string $toEmail, string $otp, int $ttlMinutes): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($toEmail)
            ->subject('Your login code')
            ->htmlTemplate('emails/otp_login.html.twig')
            ->context([
                'otp' => $otp,
                'ttlMinutes' => $ttlMinutes,
                'toEmail' => $toEmail,
                'app_name' => 'MFC VSTU',
                'login_url' => $this->urlGenerator->generate('app_login_otp', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'support_email' => $this->supportEmail,
            ]);

        $this->mailer->send($email);
    }
}
