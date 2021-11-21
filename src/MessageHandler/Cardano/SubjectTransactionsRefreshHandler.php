<?php declare(strict_types=1);

namespace App\MessageHandler\Cardano;

use App\Cardano\CardanoExplorer;
use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryCardanoTx;
use App\Entity\User;
use App\Message\Cardano\SubjectTransactionsRefreshMessage;
use App\Repository\CardanoTxInitRepository;
use App\Repository\CardanoTxRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SubjectTransactionsRefreshHandler implements MessageHandlerInterface
{
    public function __construct(
        private CardanoExplorer $explorer,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private CardanoTxInitRepository $initRepository,
        private CardanoTxRepository $txRepository
    ) {
    }

    public function __invoke(SubjectTransactionsRefreshMessage $message): void
    {
        $txInit = $this->initRepository->find($message->txInitId);
        if (!$txInit) {
            return;
        }

        $className = $this->entityManager->getClassMetadata(get_class($txInit->getSubject()))->name;
        $subject   = $this->entityManager->getRepository($className)->find($txInit->getSubject()->getId());

        /**
         * @var User $receiver
         */
        $receiver = $subject->user;

        // fetch transaction list
        $transactions = $this->explorer->findGte($receiver->cardanoWalletAddress, $txInit->createdAt);

        try {
            $this->entityManager->beginTransaction();

            // @todo
            foreach ($this->createTx($subject, $transactions) as $t) {
                $this->entityManager->persist($t);
                $this->entityManager->remove($txInit);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
        // set subject metadata
        // delete payment request
    }

    private function createTx(ContentInterface $subject, StdClass $transactions): \Generator
    {
        foreach ($transactions->data->transactions as $t) {
            $senderAddress = end($t->inputs)->address;
            $txHash        = end($t->outputs)->txHash;
            $amount        = end($t->outputs)->value;
            $createdAt     = new \DateTimeImmutable(end($t->outputs)->transaction->includedAt);

            if ($this->txRepository->findOneBy(['txHash' => $txHash])) {
                continue;
            }

            $sender = $this->userRepository->findOneBy(['cardanoWalletAddress' => $senderAddress]);

            yield new EntryCardanoTx($subject, $subject->user, (int) $amount, $txHash, $createdAt, $sender);
        }
    }
}
