<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Notification;

use App\DTO\MessageDto;
use App\Entity\Message;
use App\Service\MessageManager;
use App\Service\NotificationManager;
use App\Tests\WebTestCase;

class NotificationRetrieveApiTest extends WebTestCase
{
    public const NOTIFICATION_RESPONSE_KEYS = ['notificationId', 'status', 'type', 'subject'];

    public function testApiCannotGetNotificationsByStatusAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/notifications/all');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetNotificationsByStatusWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/all', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetNotificationsByStatusMessagesRedactedWithoutScope(): void
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
        $notificationManager = $this->getService(NotificationManager::class);
        $notificationManager->readMessageNotification($message, $user);
        // Create unread notification
        $thread = $messageManager->toThread($dto, $messagingUser, $user);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/all', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('new', $jsonData['items'][0]['status']);
        self::assertEquals('message_notification', $jsonData['items'][0]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('read', $jsonData['items'][1]['status']);
        self::assertEquals('message_notification', $jsonData['items'][1]['type']);

        self::assertIsArray($jsonData['items'][0]['subject']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertNull($jsonData['items'][0]['subject']['messageId']);
        self::assertNull($jsonData['items'][0]['subject']['threadId']);
        self::assertNull($jsonData['items'][0]['subject']['sender']);
        self::assertNull($jsonData['items'][0]['subject']['status']);
        self::assertNull($jsonData['items'][0]['subject']['createdAt']);
        self::assertEquals('This app has not received permission to read your messages.', $jsonData['items'][0]['subject']['body']);
    }

    public function testApiCanGetNotificationsByStatusAll(): void
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
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/all', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('new', $jsonData['items'][0]['status']);
        self::assertEquals('message_notification', $jsonData['items'][0]['type']);

        self::assertIsArray($jsonData['items'][0]['subject']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertSame($message->getId(), $jsonData['items'][0]['subject']['messageId']);
        self::assertSame($message->thread->getId(), $jsonData['items'][0]['subject']['threadId']);
        self::assertIsArray($jsonData['items'][0]['subject']['sender']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['subject']['sender']);
        self::assertSame($messagingUser->getId(), $jsonData['items'][0]['subject']['sender']['userId']);
        self::assertEquals('new', $jsonData['items'][0]['subject']['status']);
        self::assertNotNull($jsonData['items'][0]['subject']['createdAt']);
        self::assertEquals($message->body, $jsonData['items'][0]['subject']['body']);
    }

    public function testApiCanGetNotificationsFromThreads(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $magazine = $this->getMagazineByName('acme');
        $entry = $this->getEntryByTitle('Test notification entry', body: 'Test body', magazine: $magazine, user: $messagingUser);
        $userEntry = $this->getEntryByTitle('Test entry', body: 'Test body', magazine: $magazine, user: $user);
        $comment = $this->createEntryComment('Test notification comment', $userEntry, $messagingUser);
        $commentTwo = $this->createEntryComment('Test notification comment 2', $userEntry, $messagingUser, $comment);
        $parent = $this->createEntryComment('Test parent comment', $entry, $user);
        $reply = $this->createEntryComment('Test reply comment', $entry, $messagingUser, $parent);
        $this->createEntryComment('Test not notified comment', $entry, $messagingUser);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/all', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(4, $jsonData['items']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('new', $jsonData['items'][0]['status']);
        self::assertEquals('entry_comment_reply_notification', $jsonData['items'][0]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('new', $jsonData['items'][1]['status']);
        self::assertEquals('entry_comment_created_notification', $jsonData['items'][1]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertEquals('new', $jsonData['items'][2]['status']);
        self::assertEquals('entry_comment_created_notification', $jsonData['items'][2]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][3]);
        self::assertEquals('new', $jsonData['items'][3]['status']);
        self::assertEquals('entry_created_notification', $jsonData['items'][3]['type']);

        self::assertIsArray($jsonData['items'][0]['subject']);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertSame($reply->getId(), $jsonData['items'][0]['subject']['commentId']);
        self::assertIsArray($jsonData['items'][1]['subject']);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]['subject']);
        self::assertSame($commentTwo->getId(), $jsonData['items'][1]['subject']['commentId']);
        self::assertIsArray($jsonData['items'][2]['subject']);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]['subject']);
        self::assertSame($comment->getId(), $jsonData['items'][2]['subject']['commentId']);
        self::assertIsArray($jsonData['items'][3]['subject']);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][3]['subject']);
        self::assertSame($entry->getId(), $jsonData['items'][3]['subject']['entryId']);
    }

    public function testApiCanGetNotificationsFromPosts(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $magazine = $this->getMagazineByName('acme');
        $post = $this->createPost('Test notification post', magazine: $magazine, user: $messagingUser);
        $userPost = $this->createPost('Test not notified body', magazine: $magazine, user: $user);
        $comment = $this->createPostComment('Test notification comment', $userPost, $messagingUser);
        $commentTwo = $this->createPostCommentReply('Test notification comment 2', $userPost, $messagingUser, $comment);
        $parent = $this->createPostComment('Test parent comment', $post, $user);
        $reply = $this->createPostCommentReply('Test reply comment', $post, $messagingUser, $parent);
        $this->createPostComment('Test not notified comment', $post, $messagingUser);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read user:message:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/all', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(4, $jsonData['items']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('new', $jsonData['items'][0]['status']);
        self::assertEquals('post_comment_reply_notification', $jsonData['items'][0]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('new', $jsonData['items'][1]['status']);
        self::assertEquals('post_comment_created_notification', $jsonData['items'][1]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertEquals('new', $jsonData['items'][2]['status']);
        self::assertEquals('post_comment_created_notification', $jsonData['items'][2]['type']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][3]);
        self::assertEquals('new', $jsonData['items'][3]['status']);
        self::assertEquals('post_created_notification', $jsonData['items'][3]['type']);

        self::assertIsArray($jsonData['items'][0]['subject']);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertSame($reply->getId(), $jsonData['items'][0]['subject']['commentId']);
        self::assertIsArray($jsonData['items'][1]['subject']);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]['subject']);
        self::assertSame($commentTwo->getId(), $jsonData['items'][1]['subject']['commentId']);
        self::assertIsArray($jsonData['items'][2]['subject']);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]['subject']);
        self::assertSame($comment->getId(), $jsonData['items'][2]['subject']['commentId']);
        self::assertIsArray($jsonData['items'][3]['subject']);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][3]['subject']);
        self::assertSame($post->getId(), $jsonData['items'][3]['subject']['postId']);
    }

    public function testApiCanGetNotificationsByStatusRead(): void
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
        $notificationManager = $this->getService(NotificationManager::class);
        $notificationManager->readMessageNotification($message, $user);
        // Create unread notification
        $thread = $messageManager->toThread($dto, $messagingUser, $user);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/read', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('read', $jsonData['items'][0]['status']);
        self::assertEquals('message_notification', $jsonData['items'][0]['type']);

        self::assertIsArray($jsonData['items'][0]['subject']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertNull($jsonData['items'][0]['subject']['messageId']);
        self::assertNull($jsonData['items'][0]['subject']['threadId']);
        self::assertNull($jsonData['items'][0]['subject']['sender']);
        self::assertNull($jsonData['items'][0]['subject']['status']);
        self::assertNull($jsonData['items'][0]['subject']['createdAt']);
        self::assertEquals('This app has not received permission to read your messages.', $jsonData['items'][0]['subject']['body']);
    }

    public function testApiCanGetNotificationsByStatusNew(): void
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
        $notificationManager = $this->getService(NotificationManager::class);
        $notificationManager->readMessageNotification($message, $user);
        // Create unread notification
        $thread = $messageManager->toThread($dto, $messagingUser, $user);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/new', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('new', $jsonData['items'][0]['status']);
        self::assertEquals('message_notification', $jsonData['items'][0]['type']);

        self::assertIsArray($jsonData['items'][0]['subject']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertNull($jsonData['items'][0]['subject']['messageId']);
        self::assertNull($jsonData['items'][0]['subject']['threadId']);
        self::assertNull($jsonData['items'][0]['subject']['sender']);
        self::assertNull($jsonData['items'][0]['subject']['status']);
        self::assertNull($jsonData['items'][0]['subject']['createdAt']);
        self::assertEquals('This app has not received permission to read your messages.', $jsonData['items'][0]['subject']['body']);
    }

    public function testApiCannotGetNotificationsByInvalidStatus(): void
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
        $notificationManager = $this->getService(NotificationManager::class);
        $notificationManager->readMessageNotification($message, $user);
        // Create unread notification
        $thread = $messageManager->toThread($dto, $messagingUser, $user);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/invalid', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);
    }

    public function testApiCannotGetNotificationCountAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/notifications/count');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetNotificationCountWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/count', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetNotificationCount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagingUser = $this->getUserByUsername('JaneDoe');
        $magazine = $this->getMagazineByName('acme');
        $this->getEntryByTitle('Test notification entry', body: 'Test body', magazine: $magazine, user: $messagingUser);
        $this->createPost('Test notification post body', magazine: $magazine, user: $messagingUser);

        $messageManager = $this->getService(MessageManager::class);
        $dto = new MessageDto();
        $dto->body = 'test message';
        $thread = $messageManager->toThread($dto, $messagingUser, $user);
        /** @var Message $message */
        $message = $thread->messages->get(0);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/notifications/count', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(['count'], $jsonData);
        self::assertSame(3, $jsonData['count']);
    }

    public function testApiCannotGetNotificationByIdAnonymous(): void
    {
        $client = self::createClient();

        $notification = $this->createMessageNotification();
        self::assertNotNull($notification);

        $client->request('GET', "/api/notification/{$notification->getId()}");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetNotificationByIdWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $notification = $this->createMessageNotification();
        self::assertNotNull($notification);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/notification/{$notification->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotGetOtherUsersNotificationById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');

        $notification = $this->createMessageNotification($messagedUser);
        self::assertNotNull($notification);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/notification/{$notification->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetNotificationById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');

        $notification = $this->createMessageNotification();
        self::assertNotNull($notification);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/notification/{$notification->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::NOTIFICATION_RESPONSE_KEYS, $jsonData);
        self::assertSame($notification->getId(), $jsonData['notificationId']);
    }
}
