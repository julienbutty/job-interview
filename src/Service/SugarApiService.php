<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SugarApiService
{
    public function __construct(
        public HttpClientInterface $sugarClient,
        private readonly string    $username,
        private readonly string    $password,
        private ?string            $token = null
    )
    {
        $this->token = $this->connect();
    }

    public function connect(): string
    {
        $response = $this->sugarClient->request(
            'POST',
            '/rest/v11_17/oauth2/token',
            [
                'json' => [
                    "grant_type" => "password",
                    "client_id" => "sugar",
                    "client_secret" => "",
                    "username" => $this->username,
                    "password" => $this->password,
                    "platform" => "base"
                ]
            ]
        );

        return $response->toArray()['access_token'];
    }

    public function findAllContacts(): string
    {
        $response = $this->sugarClient->request(
            'GET',
            '/rest/v11_17/Contacts',
            [
                'auth_bearer' => $this->token,
            ]
        );

        return $response->getContent();
    }
}
