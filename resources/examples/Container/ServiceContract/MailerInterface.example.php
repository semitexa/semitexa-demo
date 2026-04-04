<?php

declare(strict_types=1);

namespace App\Domain\Mail;

interface MailerInterface
{
    public function send(object $message): void;
}
