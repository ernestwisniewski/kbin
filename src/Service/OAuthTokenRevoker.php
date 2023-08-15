<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\AccessToken;
use League\Bundle\OAuth2ServerBundle\Model\AuthorizationCode;
use League\Bundle\OAuth2ServerBundle\Model\RefreshToken;

class OAuthTokenRevoker
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function revokeCredentialsForUserWithClient(User $user, Client $client): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(AccessToken::class, 'at')
            ->set('at.revoked', ':revoked')
            ->where('at.userIdentifier = :userIdentifier')
            ->andWhere('at.client = :clientIdentifier')
            ->setParameter('revoked', true)
            ->setParameter('userIdentifier', $user->getUserIdentifier())
            ->setParameter('clientIdentifier', $client->getIdentifier())
            ->getQuery()
            ->execute();

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->update(RefreshToken::class, 'rt')
            ->set('rt.revoked', ':revoked')
            ->where($queryBuilder->expr()->in(
                'rt.accessToken',
                $this->entityManager->createQueryBuilder()
                    ->select('at.identifier')
                    ->from(AccessToken::class, 'at')
                    ->where('at.userIdentifier = :userIdentifier')
                    ->andWhere('at.client = :clientIdentifier')
                    ->getDQL()
            ))
            ->setParameter('revoked', true)
            ->setParameter('userIdentifier', $user->getUserIdentifier())
            ->setParameter('clientIdentifier', $client->getIdentifier())
            ->getQuery()
            ->execute();

        $this->entityManager->createQueryBuilder()
            ->update(AuthorizationCode::class, 'ac')
            ->set('ac.revoked', ':revoked')
            ->where('ac.userIdentifier = :userIdentifier')
            ->andWhere('ac.client = :clientIdentifier')
            ->setParameter('revoked', true)
            ->setParameter('userIdentifier', $user->getUserIdentifier())
            ->setParameter('clientIdentifier', $client->getIdentifier())
            ->getQuery()
            ->execute();
    }
}
