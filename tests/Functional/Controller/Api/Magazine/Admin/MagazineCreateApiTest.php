<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;

class MagazineCreateApiTest extends WebTestCase
{
    public function testApiCannotCreateMagazineAnonymous(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/moderate/magazine/new');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('POST', '/api/moderate/magazine/new', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $name = 'test';
        $title = 'API Test Magazine';
        $description = 'A description';
        $rules = 'Some rules';

        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
            parameters: [
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'rules' => $rules,
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertEquals($name, $jsonData['name']);
        self::assertSame($user->getId(), $jsonData['owner']['userId']);
        self::assertEquals($description, $jsonData['description']);
        self::assertEquals($rules, $jsonData['rules']);
        self::assertFalse($jsonData['isAdult']);
    }

    public function testApiCannotCreateInvalidMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $title = 'No name';
        $description = 'A description';
        $rules = 'Some rules';

        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
            parameters: [
                'name' => null,
                'title' => $title,
                'description' => $description,
                'rules' => $rules,
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);

        $name = 'a';
        $title = 'Too short name';

        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
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

        $name = 'long_name_that_exceeds_the_limit';
        $title = 'Too long name';
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
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

        $name = 'invalidch@racters!';
        $title = 'Invalid Characters in name';
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
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

        $name = 'nulltitle';
        $title = null;
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
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

        $name = 'shorttitle';
        $title = 'as';
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
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

        $name = 'longtitle';
        $title = 'Way too long of a title. This can only be 50 characters!';
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
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

        $name = 'shortrules';
        $title = 'This has too short rules';
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
            parameters: [
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'rules' => 'ru',
                'isAdult' => false,
            ],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(400);

        $name = 'shortdescription';
        $title = 'This has too short of a description';
        $client->jsonRequest(
            'POST', '/api/moderate/magazine/new',
            parameters: [
                'name' => $name,
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
