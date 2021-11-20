<?php declare(strict_types=1);

namespace App\Cardano;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoTransactions
{
    // https://forum.cardano.org/t/how-to-get-started-with-metadata-on-cardano/45111

    public function __construct(
        private string $cardanoApiUrl,
        private HttpClientInterface $client,
        private CardanoWallet $wallet
    ) {
    }

    public function create(string $mnemonic, string $authorAddress, float $amount): array
    {
        try {
            $wallet = $this->wallet->createWallet(explode(' ', $mnemonic));
        } catch (\Exception $e) {
            foreach ($this->wallet->getWallets() as $wallet) {
                $this->wallet->delete($wallet['id']);
            }

            $wallet = $this->wallet->createWallet(explode(' ', $mnemonic));
        }

        dd(
            $resp = $this->client->request(
                'POST',
                "$this->cardanoApiUrl/wallets/{$wallet['id']}/transactions",
                [
                    'json' => [
                        'passphrase' => $wallet['pp'],
                        'payments'   => [
                            [
                                'address' => $authorAddress,
                                'amount'  => [
                                    'quantity' => $amount * 1000000,
                                    'unit'     => 'lovelace',
                                ],
                            ],
                        ],
                    ],
                ]
            )->getContent(false)
        );

        $this->wallet->delete($wallet['id']);

        return $resp;
    }
}
