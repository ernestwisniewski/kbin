<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Message;

use App\Entity\Message;
use App\Tests\WebTestCase;

class MessageThreadCreateApiTest extends WebTestCase
{
    public function testApiCannotCreateThreadAnonymous(): void
    {
        $client = self::createClient();
        $messagedUser = $this->getUserByUsername('JohnDoe');

        $client->jsonRequest('POST', "/api/users/{$messagedUser->getId()}/message", parameters: ['body' => 'test message']);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateThreadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $messagedUser = $this->getUserByUsername('JaneDoe');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/users/{$messagedUser->getId()}/message", parameters: ['body' => 'test message'], server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateThread(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagedUser = $this->getUserByUsername('JaneDoe');

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/users/{$messagedUser->getId()}/message", parameters: ['body' => 'test message'], server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MessageRetrieveApiTest::MESSAGE_THREAD_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['participants']);
        self::assertCount(2, $jsonData['participants']);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['participants'][0]);
        self::assertTrue($user->getId() === $jsonData['participants'][0]['userId'] || $messagedUser->getId() === $jsonData['participants'][0]['userId']);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['participants'][1]);
        self::assertTrue($user->getId() === $jsonData['participants'][1]['userId'] || $messagedUser->getId() === $jsonData['participants'][1]['userId']);

        self::assertSame(1, $jsonData['messageCount']);
        self::assertNotNull($jsonData['threadId']);

        self::assertIsArray($jsonData['messages']);
        self::assertCount(1, $jsonData['messages']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['messages'][0]);

        self::assertEquals('test message', $jsonData['messages'][0]['body']);
        self::assertEquals(Message::STATUS_NEW, $jsonData['messages'][0]['status']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['messages'][0]['sender']);
        self::assertSame($user->getId(), $jsonData['messages'][0]['sender']['userId']);
    }
}
