<?php declare(strict_types=1);

namespace App\Service;

use App\Cardano\CardanoWallet;
use App\Cardano\CardanoWalletTransactions;
use App\Entity\CardanoTx;
use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryCardanoTx;
use App\Entity\EntryCardanoTxInit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;

class CardanoManager
{
    public function __construct(
        private CardanoWallet $wallet,
        private CardanoWalletTransactions $walletTransactions,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[ArrayShape(['mnemonic' => "string", 'address' => "string", 'walletId' => "string"])] public function createWallet(
        User $user,
        ?string $mnemonic = null
    ): array {
        if ($user->cardanoWalletId) {
            $this->detachWallet($user);
        }

        $walletInfo = $this->wallet->create($user->getPassword(), $mnemonic); // @todo

        $user->cardanoWalletId      = $walletInfo['walletId'];
        $user->cardanoWalletAddress = $walletInfo['address'];

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $walletInfo;
    }

    public function detachWallet(User $user): void
    {
        if (!$user->cardanoWalletId) {
            return;
        }

        try {
            $this->wallet->delete($user->cardanoWalletId);
        } catch (\Exception $e) {

        }

        $user->cardanoWalletId = null;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function txInit(ContentInterface $subject, string $sessionId, ?User $user = null)
    {
        $req = new EntryCardanoTxInit($subject, $sessionId, $user);

        $this->entityManager->persist($req);
        $this->entityManager->flush();
    }

    public function createTransaction(
        User $sender,
        ContentInterface $subject,
        string $passphrase,
        string $walletId,
        string $receiverAddress,
        float $amount
    ): CardanoTx {
        $tx = $this->walletTransactions->create($passphrase, $walletId, $receiverAddress, $amount);

        $amount = $tx['amount']['quantity'] - $tx['fee']['quantity'];
        $entity = new EntryCardanoTx($subject, $amount, $tx['id'], (new \DateTimeImmutable()), $sender);

        $subject->adaAmount += $amount;

        $this->entityManager->persist($subject);
        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        return $entity;
    }

    #[ArrayShape(['sum' => "int", 'fee' => "int"])] public function calculateFee(string $receiverAddress, string $walletId, float $amount): array
    {
        $fee = $this->walletTransactions->calculateFee($receiverAddress, $walletId, $amount);

        return [
            'sum' => ($this->walletTransactions->adaToLovelace($amount) + $fee['estimated_max']['quantity']) / 1000000,
            'fee' => $fee['estimated_max']['quantity'] / 1000000,
        ];
    }
}
