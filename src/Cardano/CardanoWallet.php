<?php declare(strict_types=1);

namespace App\Cardano;

use FurqanSiddiqui\BIP39\BIP39;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CardanoWallet
{
    public function __construct(public string $cardanoApiUrl, public $cardanoApiKey, public HttpClientInterface $client)
    {
    }

    #[ArrayShape(['mnemonic' => "string", 'address' => "string"])] public function create(): array
    {
        $wallet   = $this->createWallet($mnemonic = BIP39::Generate(15)->words);
        $walletId = $wallet->toArray()['id'];

        $addresses = $this->client->request('GET', "$this->cardanoApiUrl/wallets/$walletId/addresses");
        $address   = $addresses->toArray()[0]['id'];

        $this->client->request('DELETE', "$this->cardanoApiUrl/wallets/$walletId");

        return ['mnemonic' => implode(' ', $mnemonic), 'address' => $address];
    }

    private function createWallet(array $mnemonic): ResponseInterface
    {
        return $this->client->request(
            'POST',
            "$this->cardanoApiUrl/wallets",
            [
                'json' => [
                    'name'              => 'Karabin',
                    'mnemonic_sentence' => $mnemonic,
                    'passphrase'        => bin2hex(random_bytes(15))
                ],
            ]
        );
    }
}
