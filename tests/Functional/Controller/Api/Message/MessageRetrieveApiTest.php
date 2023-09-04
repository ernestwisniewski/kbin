<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Message;

use App\DTO\MessageDto;
use App\Entity\Message;
use App\Service\MessageManager;
use App\Tests\WebTestCase;

class MessageRetrieveApiTest extends WebTestCase
{
    public const MESSAGE_THREAD_RESPONSE_KEYS = ['threadId', 'participants', 'messageCount', 'messages'];

    public function testApiCannotGetMessagesAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/messages');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetMessagesWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/messages', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetMessages(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $user);
        /** @var Message $message */
        $message = $thread->messages->get(0);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/messages', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::MESSAGE_THREAD_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($thread->getId(), $jsonData['items'][0]['threadId']);
        self::assertSame(1, $jsonData['items'][0]['messageCount']);

        self::assertIsArray($jsonData['items'][0]['messages']);
        self::assertCount(1, $jsonData['items'][0]['messages']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['items'][0]['messages'][0]);
        self::assertSame($message->getId(), $jsonData['items'][0]['messages'][0]['messageId']);
        self::assertSame($thread->getId(), $jsonData['items'][0]['messages'][0]['threadId']);
        self::assertEquals('test message', $jsonData['items'][0]['messages'][0]['body']);
        self::assertEquals('new', $jsonData['items'][0]['messages'][0]['status']);
        self::assertSame($message->createdAt->getTimestamp(), \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $jsonData['items'][0]['messages'][0]['createdAt'])->getTimestamp());
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['messages'][0]['sender']);
        self::assertSame($messagingUser->getId(), $jsonData['items'][0]['messages'][0]['sender']['userId']);
    }

    public function testApiCannotGetMessageByIdAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/messages/1');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetMessageByIdWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/messages/1', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotGetOtherUsersMessageById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $messagedUser);
        /** @var Message $message */
        $message = $thread->messages->get(0);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/messages/{$message->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetMessageById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $user);
        /** @var Message $message */
        $message = $thread->messages->get(0);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/messages/{$message->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData);
        self::assertSame($message->getId(), $jsonData['messageId']);
        self::assertSame($thread->getId(), $jsonData['threadId']);
        self::assertEquals('test message', $jsonData['body']);
        self::assertEquals('new', $jsonData['status']);
        self::assertSame($message->createdAt->getTimestamp(), \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $jsonData['createdAt'])->getTimestamp());
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['sender']);
        self::assertSame($messagingUser->getId(), $jsonData['sender']['userId']);
    }

    public function testApiCannotGetMessageThreadByIdAnonymous(): void
    {
        $client = self::createClient();
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $messagedUser);

        $client->request('GET', "/api/messages/thread/{$thread->getId()}");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetMessageThreadByIdWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');
        $client->loginUser($user);

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $messagedUser);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/messages/thread/{$thread->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotGetOtherUsersMessageThreadById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $messagedUser);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/messages/thread/{$thread->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetMessageThreadById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $user);
        /** @var Message $message */
        $message = $thread->messages->get(0);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/messages/thread/{$thread->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(array_merge(self::PAGINATED_KEYS, ['participants']), $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['participants']);
        self::assertCount(2, $jsonData['participants']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame($message->getId(), $jsonData['items'][0]['messageId']);
        self::assertSame($thread->getId(), $jsonData['items'][0]['threadId']);
        self::assertEquals('test message', $jsonData['items'][0]['body']);
        self::assertEquals('new', $jsonData['items'][0]['status']);
        self::assertSame($message->createdAt->getTimestamp(), \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $jsonData['items'][0]['createdAt'])->getTimestamp());
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['sender']);
        self::assertSame($messagingUser->getId(), $jsonData['items'][0]['sender']['userId']);
    }
}
