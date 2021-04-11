<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\DomainInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DomainRepository;
use App\Entity\Domain;

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
        } else {
            $domain->addEntry($subject);
            $domain->updateCounts();
        }

        $this->manager->persist($domain);
        $this->manager->flush();

        return $subject;
    }
}
