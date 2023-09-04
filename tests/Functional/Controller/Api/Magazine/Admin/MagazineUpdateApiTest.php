<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;

class MagazineUpdateApiTest extends WebTestCase
{
    public function testApiCannotUpdateMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('PUT', "/api/moderate/magazine/{$magazine->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/moderate/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:update');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $name = 'test';
        $title = 'API Test Magazine';
        $description = 'A description';
        $rules = 'Some rules';

        $client->jsonRequest(
            'PUT', "/api/moderate/magazine/{$magazine->getId()}",
            parameters: [
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'rules' => $rules,
                'isAdult' => true,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertEquals($name, $jsonData['name']);
        self::assertSame($user->getId(), $jsonData['owner']['userId']);
        self::assertEquals($description, $jsonData['description']);
        self::assertEquals($rules, $jsonData['rules']);
        self::assertTrue($jsonData['isAdult']);
    }

    public function testApiCannotUpdateMagazineWithInvalidParams(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:update');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $name = 'someothername';
        $title = 'Different name';
        $description = 'A description';
        $rules = 'Some rules';

        $client->jsonRequest(
            'PUT', "/api/moderate/magazine/{$magazine->getId()}",
            parameters: [
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'rules' => $rules,
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);

        $description = 'short title';
        $title = 'as';
        $client->jsonRequest(
            'PUT', "/api/moderate/magazine/{$magazine->getId()}",
            parameters: [
                'title' => $title,
                'description' => $description,
                'rules' => $rules,
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);

        $description = 'long title';
        $title = 'Way too long of a title. This can only be 50 characters!';
        $client->jsonRequest(
            'PUT', "/api/moderate/magazine/{$magazine->getId()}",
            parameters: [
                'title' => $title,
                'description' => $description,
                'rules' => $rules,
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);

        $description = 'short rules';
        $title = 'This has too short rules';
        $client->jsonRequest(
            'PUT', "/api/moderate/magazine/{$magazine->getId()}",
            parameters: [
                'title' => $title,
                'description' => $description,
                'rules' => 'ru',
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);

        $rules = 'short description';
        $title = 'This has too short of a description';
        $client->jsonRequest(
            'PUT', "/api/moderate/magazine/{$magazine->getId()}",
            parameters: [
                'title' => $title,
                'description' => 'de',
                'rules' => $rules,
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);
    }
}
