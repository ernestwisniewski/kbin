<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\CardanoTxInit;
use App\Message\Cardano\SubjectTransactionsRefreshMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CardanoWalletObserverCommand extends Command
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
        $requests = $this->entityManager->getRepository(CardanoTxInit::class)->findForRefresh();

        foreach ($requests as $request) {
            /**
             * @var $request CardanoTxInit
             */
            $this->bus->dispatch(
                new SubjectTransactionsRefreshMessage(
                    $request->getId()
                )
            );
        }

        return Command::SUCCESS;
    }
}
