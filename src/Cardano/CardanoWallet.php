<?php declare(strict_types=1);

namespace App\Cardano;

use FurqanSiddiqui\BIP39\BIP39;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoWallet
{
    public function __construct(public string $cardanoApiUrl, public $cardanoApiKey, public HttpClientInterface $client)
    {
    }

    #[ArrayShape(['mnemonic' => "string", 'address' => "string"])] public function create(): array
    {
        $wallet   = $this->createWallet($mnemonic = BIP39::Generate(15)->words);
        $walletId = $wallet['id'];

        $addresses = $this->client->request('GET', "$this->cardanoApiUrl/wallets/$walletId/addresses");
        $address   = $addresses->toArray()[0]['id'];

        $this->delete($walletId);

        return ['mnemonic' => implode(' ', $mnemonic), 'address' => $address];
    }

    public function createWallet(array $mnemonic): array
    {
        return $this->client->request(
                'POST',
                "$this->cardanoApiUrl/wallets",
                [
                    'json' => [
                        'name'              => 'Karabin',
                        'mnemonic_sentence' => $mnemonic,
                        'passphrase'        => $pp = bin2hex(random_bytes(15)),
                    ],
                ]
            )->toArray() + ['pp' => $pp];
    }

    public function getWallets(): array
    {
        return $this->client->request(
            'GET',
            "$this->cardanoApiUrl/wallets",
        )->toArray();
    }

    public function delete(string $walletId)
    {
//        $this->client->request('DELETE', "$this->cardanoApiUrl/wallets/$walletId");
    }
}
