<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\CardanoPaymentInit;
use App\Message\Cardano\SubjectTransactionsRefreshMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CardanoWalletObserver extends Command
{
    protected static $defaultName = 'kbin:cardano:refresh';

    public function __construct(private MessageBusInterface $bus, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows refresh users transactions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $requests = $this->entityManager->getRepository(CardanoPaymentInit::class)->findForRefresh();

        foreach ($requests as $request) {
            /**
             * @var $request CardanoPaymentInit
             */
            $this->bus->dispatch(
                new SubjectTransactionsRefreshMessage(
                    $request->getSubject()->getId(),
                    $this->entityManager->getClassMetadata(get_class($request->getSubject()))->name,
                    $request->createdAt
                )
            );
        }

        return Command::SUCCESS;
    }
}
