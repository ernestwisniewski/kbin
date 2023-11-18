<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MagazineUpdateThemeApiTest extends WebTestCase
{
    public const MAGAZINE_THEME_RESPONSE_KEYS = ['magazine', 'customCss', 'icon'];

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->kibbyPath = \dirname(__FILE__, 6).'/assets/kibby_emoji.png';
    }

    public function testApiCannotUpdateMagazineThemeAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/theme");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiModCannotUpdateMagazineTheme(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new MagazineModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:theme');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST',
            "/api/moderate/magazine/{$magazine->getId()}/theme",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateMagazineThemeWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST',
            "/api/moderate/magazine/{$magazine->getId()}/theme",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateMagazineThemeWithCustomCss(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:theme');
        $token = $codes['token_type'].' '.$codes['access_token'];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $customCss = 'a {background: red;}';

        $client->request(
            'POST', "/api/moderate/magazine/{$magazine->getId()}/theme",
            parameters: [
                'customCss' => $customCss,
            ],
            files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_THEME_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertStringContainsString($customCss, $jsonData['customCss']);
        self::assertIsArray($jsonData['icon']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['icon']);
        self::assertSame(96, $jsonData['icon']['width']);
        self::assertSame(96, $jsonData['icon']['height']);
        self::assertEquals(
            'a8/1c/a81cc2fea35eeb232cd28fcb109b3eb5a4e52c71bce95af6650d71876c1bcbb7.png',
            $jsonData['icon']['filePath']
        );
    }

    public function testApiCanUpdateMagazineThemeWithBackgroundImage(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:theme');
        $token = $codes['token_type'].' '.$codes['access_token'];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $backgroundImage = 'shape1';

        $client->request(
            'POST', "/api/moderate/magazine/{$magazine->getId()}/theme",
            parameters: [
                'backgroundImage' => $backgroundImage,
            ],
            files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_THEME_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertStringContainsString('/build/images/shape.png', $jsonData['customCss']);
        self::assertIsArray($jsonData['icon']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['icon']);
        self::assertSame(96, $jsonData['icon']['width']);
        self::assertSame(96, $jsonData['icon']['height']);
        self::assertEquals(self::KIBBY_PNG_URL_RESULT, $jsonData['icon']['filePath']);
    }
}
