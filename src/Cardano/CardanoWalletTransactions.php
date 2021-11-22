<?php declare(strict_types=1);

namespace App\Cardano;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoWalletTransactions
{
    // https://forum.cardano.org/t/how-to-get-started-with-metadata-on-cardano/45111

    public function __construct(
        private string $cardanoWalletUrl,
        private HttpClientInterface $client,
    ) {
    }

    public function create(string $passphrase, string $walletId, string $receiverAddress, float $amount): array
    {
        return $this->client->request(
            'POST',
            "$this->cardanoWalletUrl/wallets/$walletId/transactions",
            [
                'json' => [
                    'passphrase' => $passphrase,
                    'payments'   => [
                        [
                            'address' => $receiverAddress,
                            'amount'  => [
                                'quantity' => $this->adaToLovelace($amount),
                                'unit'     => 'lovelace',
                            ],
                        ],
                    ],
                ],
            ]
        )->toArray();
    }

    public function calculateFee(string $receiverAddress, string $walletId, float $amount): array
    {
        return $this->client->request(
            'POST',
            "$this->cardanoWalletUrl/wallets/$walletId/payment-fees",
            [
                'json' => [
                    'payments' => [
                        [
                            'address' => $receiverAddress,
                            'amount'  => [
                                'quantity' => $this->adaToLovelace($amount),
                                'unit'     => 'lovelace',
                            ],
                        ],
                    ],
                ],
            ]
        )->toArray();
    }

    public function adaToLovelace(float $amount): int
    {
        $amount *= 1000000;

        $amount = explode('.', (string) $amount)[0];

        return (int) $amount;
    }
}
