<?php

namespace App\Business;

use App\Dto\EmailDto;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

readonly class EmailBusiness
{
    public function __construct(
        private MailerInterface $mailer
    )
    {}

    public function sendEmail(EmailDto $emailDto): void
    {
        try {
            $email = (new Email())
                ->from(new Address('contact-media@viking-cup.fr', $emailDto->fromName))
                ->to($emailDto->to)
                ->subject($emailDto->subject)
                ->html($emailDto->message);

            if ($emailDto->fromEmail) {
                $email->replyTo($emailDto->fromEmail);
            }

            if ($emailDto->attachment) {
                $fileName = basename($emailDto->attachment);

                $tempFile = tempnam(sys_get_temp_dir(), explode('.', $fileName)[0] . '_');
                copy($emailDto->attachment, $tempFile);

                $email->attachFromPath($tempFile, $fileName, 'application/pdf');
            }

            $this->mailer->send($email);
        } catch (\Throwable $e) {}
    }
}