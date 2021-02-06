<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\DomainInterface;
use App\Entity\Domain;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DomainManager
{
    private DomainRepository $domainRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(DomainRepository $domainRepository, EntityManagerInterface $entityManager)
    {
        $this->domainRepository = $domainRepository;
        $this->entityManager    = $entityManager;
    }

    public function extract(DomainInterface $subject): DomainInterface
    {
        // @todo
        $domainName = $subject->getUrl() ?? 'https://karab.in';

        $domainName = preg_replace('/^www\./i', '', parse_url($domainName)['host']);

        $domain = $this->domainRepository->findOneByName($domainName);

        if (!$domain) {
            $domain  = new Domain($subject, $domainName);
            $subject = $subject->setDomain($domain);
        } else {
            $domain->addEntry($subject);
            $domain->updateCounts();
        }

        $this->entityManager->persist($domain);
        $this->entityManager->flush();

        return $subject;
    }
}
