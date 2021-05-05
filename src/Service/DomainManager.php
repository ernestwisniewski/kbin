<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\DomainInterface;
use App\Entity\Domain;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;

class
DomainManager
{
    public function __construct(
        private DomainRepository $repository,
        private EntityManagerInterface $manager
    ) {
    }

    public function extract(DomainInterface $subject): DomainInterface
    {
        // @todo
        $domainName = $subject->getUrl() ?? 'https://karab.in';

        $domainName = preg_replace('/^www\./i', '', parse_url($domainName)['host']);

        $domain = $this->repository->findOneByName($domainName);

        if (!$domain) {
            $domain          = new Domain($subject, $domainName);
            $subject->domain = $domain;
            $this->manager->persist($domain);
        }

        $domain->addEntry($subject);
        $domain->updateCounts();

        $this->manager->flush();

        return $subject;
    }
}
