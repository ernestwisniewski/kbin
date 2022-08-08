<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApHttpClient
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function getActivityObject(string $url, bool $decoded = true): array|string
    {
        $req = $this->client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/activity+json,application/ld+json,application/json',
            ],
        ]);

        return $decoded ? json_decode($req->getContent(), true) : $req->getContent();
    }

}
