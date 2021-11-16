<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryCardanoPaymentInit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CardanoManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function paymentInit(ContentInterface $subject, ?User $user = null): void
    {
        $paymentRequest = new EntryCardanoPaymentInit($subject, $user);

        $this->entityManager->persist($paymentRequest);
        $this->entityManager->flush();
    }
}
