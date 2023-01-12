<?php

declare(strict_types=1);

namespace App\Cardano;

use GraphQL\Client;

class CardanoExplorer
{
    public function __construct(private readonly string $cardanoExplorerUrl)
    {
    }

    public function findGte(string $address, \DateTimeImmutable $createdAt): \stdClass
    {
        $client = new Client($this->cardanoExplorerUrl);

        $createdAt = \DateTime::createFromImmutable($createdAt);
        $gte = $this->dateTo8601Zulu($createdAt);

        $gql = <<<QUERY
        query {
          transactions(where: {
            outputs: {
              transaction: {
                includedAt:{
                  _gte: "$gte"
                }
              }
              address: {
                _eq:"$address"}
            }
          })
          {
            inputs {
              address
            },
            outputs {
              transaction{
                includedAt
              }
              address
              txHash
              value
            }
          }
        }
        QUERY;

        return $client->runRawQuery($gql)->getResults();
    }

    private function dateTo8601Zulu(\DateTimeInterface $date): string
    {
        return (clone $date)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');
    }
}
