<?php declare(strict_types=1);

namespace App\Cardano;

use FurqanSiddiqui\BIP39\BIP39;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoWallet
{
    public function __construct(public string $cardanoApiUrl, public $cardanoApiKey, public HttpClientInterface $client)
    {
    }

    public function create(): string
    {
        $mnemonic = BIP39::Generate(24);

        $response = $this->client->request(
            'POST',
            "$this->cardanoApiUrl/wallets",
            [
                'json' => [
                    'name' => 'Kbin Wallet',
                    'mnemonic_sentence' => $mnemonic->words,
                    'passphrase' => 'kbin tips',
                ],
            ]
        );

        return implode(' ', $mnemonic->words);
    }
}
