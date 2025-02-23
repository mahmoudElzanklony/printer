<?php

namespace App\Services\External;

use GuzzleHttp\Client;

class ZohoBooksService
{
    protected $client;

    protected $accessToken;

    protected $organizationId;

    public function __construct()
    {
        $this->client = new Client();
        $this->accessToken = env('ZOHO_BOOKS_ACCESS_TOKEN');
        $this->organizationId = env('ZOHO_BOOKS_ORGANIZATION_ID');
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => 'Zoho-oauthtoken '.$this->accessToken,
            'Content-Type' => 'application/json',
        ];
    }

    public function createOrder($data)
    {
        $url = 'https://www.zohoapis.com/books/v3/salesorders';
        $response = $this->client->post($url, [
            'headers' => $this->getHeaders(),
            'json' => $data,
            'query' => ['organization_id' => $this->organizationId],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function createBill($data)
    {
        $url = 'https://www.zohoapis.com/books/v3/bills';
        $response = $this->client->post($url, [
            'headers' => $this->getHeaders(),
            'json' => $data,
            'query' => ['organization_id' => $this->organizationId],
        ]);

        return json_decode($response->getBody(), true);
    }
}
