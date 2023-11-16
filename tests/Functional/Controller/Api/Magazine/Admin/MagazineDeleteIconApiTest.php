<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\DTO\ModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Repository\ImageRepository;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MagazineDeleteIconApiTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->kibbyPath = \dirname(__FILE__, 6).'/assets/kibby_emoji.png';
    }

    public function testApiCannotDeleteMagazineIconAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/icon");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeleteMagazineIconWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/moderate/magazine/{$magazine->getId()}/icon",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotDeleteMagazineIcon(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/moderate/magazine/{$magazine->getId()}/icon",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeleteMagazineIcon(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $upload = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $imageRepository = $this->getService(ImageRepository::class);
        $image = $imageRepository->findOrCreateFromUpload($upload);
        self::assertNotNull($image);
        $magazine->icon = $image;

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($magazine);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:theme');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['icon']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['icon']);
        self::assertSame(96, $jsonData['icon']['width']);
        self::assertSame(96, $jsonData['icon']['height']);
        self::assertEquals(
            'a8/1c/a81cc2fea35eeb232cd28fcb109b3eb5a4e52c71bce95af6650d71876c1bcbb7.png',
            $jsonData['icon']['filePath']
        );

        $client->request(
            'DELETE',
            "/api/moderate/magazine/{$magazine->getId()}/icon",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineUpdateThemeApiTest::MAGAZINE_THEME_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertNull($jsonData['icon']);
    }
}
