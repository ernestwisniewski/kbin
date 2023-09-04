<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class MagazineRetrieveThemeApiTest extends WebTestCase
{
    public const MAGAZINE_THEME_RESPONSE_KEYS = ['magazine', 'customCss', 'icon'];

    public function testApiCanRetrieveMagazineThemeByIdAnonymously(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');
        $magazine->customCss = '.test {}';
        $entityManager = $this->getService(EntityManagerInterface::class);

        $entityManager->persist($magazine);
        $entityManager->flush();

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId().'/theme');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_THEME_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertEquals('.test {}', $jsonData['customCss']);
    }

    public function testApiCanRetrieveMagazineThemeById(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $magazine->customCss = '.test {}';
        $entityManager = $this->getService(EntityManagerInterface::class);

        $entityManager->persist($magazine);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId().'/theme', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_THEME_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertEquals('.test {}', $jsonData['customCss']);
    }
}
