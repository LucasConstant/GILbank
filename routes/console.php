<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send-mail', function () {
    $apiKey = trim((string) config('services.mailtrap.api_token'));
    $recipient = trim((string) env('MAILTRAP_TEST_RECIPIENT'));
    $fromAddress = trim((string) config('mail.from.address', 'noreply@gilbank.local'));
    $fromName = (string) config('mail.from.name', 'GILbank');

    if ($apiKey === '' || $recipient === '' || ! filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
        $this->error('Configure MAILTRAP_API_TOKEN, MAILTRAP_TEST_RECIPIENT e um MAIL_FROM_ADDRESS válido no arquivo .env.');

        return self::FAILURE;
    }

    $email = (new MailtrapEmail())
        ->from(new Address($fromAddress, $fromName))
        ->to(new Address($recipient))
        ->subject('Teste de e-mail do GILbank')
        ->category('Integration Test')
        ->text('E-mail de teste enviado pelo GILbank usando o Mailtrap.');

    $response = MailtrapClient::initSendingEmails(apiKey: $apiKey)->send($email);

    $this->line(json_encode(ResponseHelper::toArray($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return self::SUCCESS;
})->purpose('Send a test email through Mailtrap');
