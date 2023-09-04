<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Message;

use App\Entity\Message;
use App\Service\MessageManager;
use App\Tests\WebTestCase;

class MessageReadApiTest extends WebTestCase
{
    public function testApiCannotMarkMessagesReadAnonymous(): void
    {
        $client = self::createClient();
        $message = $this->createMessage($this->getUserByUsername('JohnDoe'), $this->getUserByUsername('JaneDoe'), 'test message');

        $client->request('PUT', "/api/messages/{$message->getId()}/read");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotMarkMessagesReadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        $message = $this->createMessage($this->getUserByUsername('JohnDoe'), $this->getUserByUsername('JaneDoe'), 'test message');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/messages/{$message->getId()}/read", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotMarkOtherUsersMessagesRead(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');

        $message = $this->createMessage($messagedUser, $messagingUser, 'test message');

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/messages/{$message->getId()}/read", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanMarkMessagesRead(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');

        $thread = $this->createMessageThread($user, $messagingUser, 'test message');
        /** @var Message $message */
        $message = $thread->messages->get(0);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/messages/{$message->getId()}/read", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData);
        self::assertSame($message->getId(), $jsonData['messageId']);
        self::assertSame($thread->getId(), $jsonData['threadId']);
        self::assertEquals('test message', $jsonData['body']);
        self::assertEquals(Message::STATUS_READ, $jsonData['status']);
        self::assertSame($message->createdAt->getTimestamp(), \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $jsonData['createdAt'])->getTimestamp());
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['sender']);
        self::assertSame($messagingUser->getId(), $jsonData['sender']['userId']);
    }

    public function testApiCannotMarkMessagesUnreadAnonymous(): void
    {
        $client = self::createClient();
        $message = $this->createMessage($this->getUserByUsername('JohnDoe'), $this->getUserByUsername('JaneDoe'), 'test message');

        $client->request('PUT', "/api/messages/{$message->getId()}/unread");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotMarkMessagesUnreadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        $message = $this->createMessage($this->getUserByUsername('JohnDoe'), $this->getUserByUsername('JaneDoe'), 'test message');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/messages/{$message->getId()}/unread", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotMarkOtherUsersMessagesUnread(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');

        $message = $this->createMessage($messagedUser, $messagingUser, 'test message');

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/messages/{$message->getId()}/unread", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanMarkMessagesUnread(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');

        $thread = $this->createMessageThread($user, $messagingUser, 'test message');
        /** @var Message $message */
        $message = $thread->messages->get(0);
        $messageManager = $this->getService(MessageManager::class);
        $messageManager->readMessage($message, $user, flush: true);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/messages/{$message->getId()}/unread", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData);
        self::assertSame($message->getId(), $jsonData['messageId']);
        self::assertSame($thread->getId(), $jsonData['threadId']);
        self::assertEquals('test message', $jsonData['body']);
        self::assertEquals(Message::STATUS_NEW, $jsonData['status']);
        self::assertSame($message->createdAt->getTimestamp(), \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $jsonData['createdAt'])->getTimestamp());
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['sender']);
        self::assertSame($messagingUser->getId(), $jsonData['sender']['userId']);
    }
}
