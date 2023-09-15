<?php

declare(strict_types=1);

namespace App\MessageHandler\Cardano;

use App\Cardano\CardanoExplorer;
use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryCardanoTx;
use App\Message\Cardano\SubjectTransactionsRefreshMessage;
use App\Repository\CardanoTxInitRepository;
use App\Repository\CardanoTxRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SubjectTransactionsRefreshHandler
{
    public function __construct(
        private readonly CardanoExplorer $explorer,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CardanoTxInitRepository $initRepository,
        private readonly CardanoTxRepository $txRepository
    ) {
    }

    public function __invoke(SubjectTransactionsRefreshMessage $message): void
    {
        if (!$txInit = $this->initRepository->find($message->txInitId)) {
            return;
        }

        $className = $this->entityManager->getClassMetadata(\get_class($txInit->getSubject()))->name;

        /**
         * @var $subject Entry
         */
        $subject = $this->entityManager->getRepository($className)->find($txInit->getSubject()->getId());
        $receiver = $subject->user;

        // fetch transaction list
        $transactions = $this->explorer->findGte($receiver->cardanoWalletAddress, $txInit->createdAt);

        $this->entityManager->beginTransaction();

        try {
            if (!$tx = $this->createTx($subject, $transactions)) {
                return;
            }

            $subject->adaAmount += $tx->amount;

            $this->entityManager->persist($subject);
            $this->entityManager->persist($tx);
            $this->entityManager->remove($txInit);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function createTx(ContentInterface $subject, \stdClass $transactions): ?EntryCardanoTx
    {
        foreach ($transactions->data->transactions as $tx) {
            $senderAddress = end($tx->inputs)->address;
            $txHash = end($tx->outputs)->txHash;
            $amount = end($tx->outputs)->value;
            $createdAt = new \DateTimeImmutable(end($tx->outputs)->transaction->includedAt);

            if ($this->txRepository->findOneBy(['txHash' => $txHash])) {
                continue;
            }

            $sender = $this->userRepository->findOneBy(['cardanoWalletAddress' => $senderAddress]);

            return new EntryCardanoTx($subject, (int) $amount, $txHash, $createdAt, $sender);
        }

        return null;
    }
}
