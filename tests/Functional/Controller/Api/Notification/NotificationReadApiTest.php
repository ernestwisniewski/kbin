<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Notification;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class NotificationReadApiTest extends WebTestCase
{
    public function testApiCannotMarkNotificationReadAnonymous(): void
    {
        $client = self::createClient();
        $notification = $this->createMessageNotification();

        $client->request('PUT', "/api/notifications/{$notification->getId()}/read");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotMarkNotificationReadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $notification = $this->createMessageNotification();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/notifications/{$notification->getId()}/read", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotMarkOtherUsersNotificationRead(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');
        $notification = $this->createMessageNotification($messagedUser);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/notifications/{$notification->getId()}/read", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanMarkNotificationRead(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $notification = $this->createMessageNotification();

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/notifications/{$notification->getId()}/read", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(NotificationRetrieveApiTest::NOTIFICATION_RESPONSE_KEYS, $jsonData);
        self::assertEquals('read', $jsonData['status']);
        self::assertEquals('message_notification', $jsonData['type']);

        self::assertIsArray($jsonData['subject']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['subject']);
        self::assertNull($jsonData['subject']['messageId']);
        self::assertNull($jsonData['subject']['threadId']);
        self::assertNull($jsonData['subject']['sender']);
        self::assertNull($jsonData['subject']['status']);
        self::assertNull($jsonData['subject']['createdAt']);
        self::assertEquals('This app has not received permission to read your messages.', $jsonData['subject']['body']);
    }

    public function testApiCannotMarkNotificationUnreadAnonymous(): void
    {
        $client = self::createClient();
        $notification = $this->createMessageNotification();

        $client->request('PUT', "/api/notifications/{$notification->getId()}/unread");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotMarkNotificationUnreadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $notification = $this->createMessageNotification();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/notifications/{$notification->getId()}/unread", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotMarkOtherUsersNotificationUnread(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');
        $notification = $this->createMessageNotification($messagedUser);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/notifications/{$notification->getId()}/unread", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanMarkNotificationUnread(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $notification = $this->createMessageNotification();
        $notification->status = Notification::STATUS_READ;
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($notification);
        $entityManager->flush();

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/notifications/{$notification->getId()}/unread", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(NotificationRetrieveApiTest::NOTIFICATION_RESPONSE_KEYS, $jsonData);
        self::assertEquals('new', $jsonData['status']);
        self::assertEquals('message_notification', $jsonData['type']);

        self::assertIsArray($jsonData['subject']);
        self::assertArrayKeysMatch(self::MESSAGE_RESPONSE_KEYS, $jsonData['subject']);
        self::assertNull($jsonData['subject']['messageId']);
        self::assertNull($jsonData['subject']['threadId']);
        self::assertNull($jsonData['subject']['sender']);
        self::assertNull($jsonData['subject']['status']);
        self::assertNull($jsonData['subject']['createdAt']);
        self::assertEquals('This app has not received permission to read your messages.', $jsonData['subject']['body']);
    }

    public function testApiCannotMarkAllNotificationsReadAnonymous(): void
    {
        $client = self::createClient();

        $this->createMessageNotification();

        $client->request('PUT', '/api/notifications/read');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotMarkAllNotificationsReadWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $this->createMessageNotification();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/notifications/read', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanMarkAllNotificationsRead(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');

        $notification = $this->createMessageNotification();

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/notifications/read', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);

        $notificationRepository = $this->getService(NotificationRepository::class);
        $notification = $notificationRepository->find($notification->getId());
        self::assertNotNull($notification);
        self::assertEquals('read', $notification->status);
    }
}
