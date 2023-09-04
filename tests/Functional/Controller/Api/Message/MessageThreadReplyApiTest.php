<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Message;

use App\Entity\Message;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class MessageThreadReplyApiTest extends WebTestCase
{
    public function testApiCannotReplyToThreadAnonymous(): void
    {
        $client = self::createClient();
        $to = $this->getUserByUsername('JohnDoe');
        $from = $this->getUserByUsername('JaneDoe');
        $thread = $this->createMessageThread($to, $from, 'starting a thread');

        $client->jsonRequest('POST', "/api/messages/thread/{$thread->getId()}/reply", parameters: ['body' => 'test message']);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotReplyToThreadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $from = $this->getUserByUsername('JaneDoe');
        $thread = $this->createMessageThread($user, $from, 'starting a thread');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/messages/thread/{$thread->getId()}/reply", parameters: ['body' => 'test message'], server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanReplyToThread(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');

        $from = $this->getUserByUsername('JaneDoe');
        $thread = $this->createMessageThread($user, $from, 'starting a thread');
        // Fake when the message was created at so that the newest to oldest order can be reliably determined
        $thread->messages->get(0)->createdAt = new \DateTimeImmutable('-5 seconds');
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($thread);
        $entityManager->flush();

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/messages/thread/{$thread->getId()}/reply", parameters: ['body' => 'test message'], server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MessageRetrieveApiTest::MESSAGE_THREAD_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['participants']);
        self::assertCount(2, $jsonData['participants']);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['participants'][0]);
        self::assertTrue($user->getId() === $jsonData['participants'][0]['userId'] || $from->getId() === $jsonData['participants'][0]['userId']);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['participants'][1]);
        self::assertTrue($user->getId() === $jsonData['participants'][1]['userId'] || $from->getId() === $jsonData['participants'][1]['userId']);

        self::assertSame(2, $jsonData['messageCount']);
        self::assertNotNull($jsonData['threadId']);

        self::assertIsArray($jsonData['messages']);
        self::assertCount(2, $jsonData['messages']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['messages'][0]);

        // Newest first
        self::assertEquals('test message', $jsonData['messages'][0]['body']);
        self::assertEquals(Message::STATUS_NEW, $jsonData['messages'][0]['status']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['messages'][0]['sender']);
        self::assertSame($user->getId(), $jsonData['messages'][0]['sender']['userId']);

        self::assertEquals('starting a thread', $jsonData['messages'][1]['body']);
        self::assertEquals(Message::STATUS_NEW, $jsonData['messages'][1]['status']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['messages'][1]['sender']);
        self::assertSame($from->getId(), $jsonData['messages'][1]['sender']['userId']);
    }
}
