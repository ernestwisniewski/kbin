<?php declare(strict_types=1);

namespace App\MessageHandler\Cardano;

use App\Cardano\CardanoTransactions;
use App\Entity\Contracts\ContentInterface;
use App\Message\Cardano\SubjectTransactionsRefreshMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SubjectTransactionsRefreshHandler implements MessageHandlerInterface
{
    public function __construct(
        private CardanoTransactions $transactions,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(SubjectTransactionsRefreshMessage $message): void
    {
        $subject      = $this->entityManager->getRepository($message->className)->find($message->id);
        $user         = $subject->user;

        // fetch transaction list
        $transactions = $this->transactions->fetch($user->cardanoWalletId, $message->createdAt);

        // set subject metadata
        // delete payment request
    }
}
