<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class MagazineModlogApiTest extends WebTestCase
{
    public function testApiCanRetrieveModlogByMagazineIdAnonymously(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId().'/log');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertEmpty($jsonData['items']);
    }

    public function testApiCanRetrieveMagazineById(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId().'/log', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertEmpty($jsonData['items']);
    }

    public function testApiModlogReflectsModerationActionsTaken(): void
    {
        $client = self::createClient();

        $this->createModlogMessages();
        $magazine = $this->getMagazineByName('acme');
        $moderator = $magazine->getOwner();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->refresh($magazine);

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId().'/log');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertCount(5, $jsonData['items']);

        $this->validateModlog($jsonData, $magazine, $moderator);
    }
}
