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

    public function findSpecificContact(): string
    {
        $query = [
            'fields' => 'modified_user_id,full_name,last_name,first_name,primary_address_street,primary_address_city,primary_address_postalcode,email1',
            'filter[0][$or][0][first_name][$contains]' => "a",
            'filter[0][$or][0][last_name][$contains]' => "b",
            'max_num' => 1
        ];

        $response = $this->sugarClient->request(
            'GET',
            '/rest/v11_17/Contacts',
            [
                'auth_bearer' => $this->token,
                'query' => $query,
            ]
        );

        return $response->getContent();
    }
}
