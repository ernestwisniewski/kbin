<?php declare(strict_types=1);

namespace App\Cardano;

use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryCardanoTx;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CardanoWalletTransactions
{
    // https://forum.cardano.org/t/how-to-get-started-with-metadata-on-cardano/45111

    public function __construct(
        private string $cardanoWalletUrl,
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager
    ) {
    }

//    public function create(
//        User $sender,
//        ContentInterface $content,
//        string $passphrase,
//        string $receiverAddress,
//        float $amount
//    ): EntryCardanoTx {
//        $tx = $this->send($passphrase, $sender->cardanoWalletId, $receiverAddress, $amount);
//
//        $entity = new EntryCardanoTx($content, $tx['amount']['quantity'], $tx['id'], (new \DateTimeImmutable()), $sender);
//
//        $this->entityManager->persist($entity);
//        $this->entityManager->flush();
//
//        return $entity;
//    }

    public function create(string $passphrase, string $walletId, string $receiverAddress, float $amount)
    {
        $amount *= 1000000;
        $amount = explode('.', (string) $amount)[0];

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
                                'quantity' => (int) $amount,
                                'unit'     => 'lovelace',
                            ],
                        ],
                    ],
                ],
            ]
        )->toArray();
    }
}
