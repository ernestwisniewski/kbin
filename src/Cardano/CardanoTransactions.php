<?php declare(strict_types=1);

namespace App\Cardano;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoTransactions
{
    // https://forum.cardano.org/t/how-to-get-started-with-metadata-on-cardano/45111

    public function __construct(public string $cardanoApiUrl, public $cardanoApiKey, public HttpClientInterface $client)
    {
    }

    public function balance(string $walletId)
    {
    }

    public function fetch(string $walletId, \DateTimeImmutable $start): array
    {
        $response = $this->client->request(
            'GET',
            "$this->cardanoApiUrl/wallets/$walletId/transactions",
            [
                'query' => [
                    'start' => $start,
                ],
                'headers' => [
                    'project_id' => $this->cardanoApiKey,
                ],
            ]
        );

        return $response->toArray();
    }
}
