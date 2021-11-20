<?php declare(strict_types=1);

namespace App\Cardano;

use FurqanSiddiqui\BIP39\BIP39;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoWallet
{
    public function __construct(public string $cardanoApiUrl, public HttpClientInterface $client)
    {
    }

    #[ArrayShape(['mnemonic' => "string", 'address' => "string", 'walletId' => "string"])] public function create(string $passphrase): array
    {
        $wallet   = $this->createWallet($mnemonic = BIP39::Generate(15)->words, $passphrase);
        $walletId = $wallet['id'];

        $addresses = $this->client->request('GET', "$this->cardanoApiUrl/wallets/$walletId/addresses");

        return ['mnemonic' => implode(' ', $mnemonic), 'address' => $addresses->toArray()[0]['id'], 'walletId' => $walletId];
    }

    public function createWallet(array $mnemonic, string $passphrase): array
    {
        return $this->client->request(
            'POST',
            "$this->cardanoApiUrl/wallets",
            [
                'json' => [
                    'name'              => 'Karabin',
                    'mnemonic_sentence' => $mnemonic,
                    'passphrase'        => $passphrase, // @todo
                ],
            ]
        )->toArray();
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
        $this->client->request('DELETE', "$this->cardanoApiUrl/wallets/$walletId");
    }

    public function deleteAll()
    {
        foreach ($this->getWallets() as $wallet) {
            $this->delete($wallet['id']);
        }
    }
}
