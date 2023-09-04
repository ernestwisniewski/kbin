<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Notification;

use App\Repository\NotificationRepository;
use App\Tests\WebTestCase;

class NotificationDeleteApiTest extends WebTestCase
{
    public function testApiCannotDeleteNotificationByIdAnonymous(): void
    {
        $client = self::createClient();
        $notification = $this->createMessageNotification();

        $client->request('DELETE', "/api/notifications/{$notification->getId()}");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeleteNotificationByIdWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $notification = $this->createMessageNotification();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/notifications/{$notification->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotDeleteOtherUsersNotificationById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $messagedUser = $this->getUserByUsername('JamesDoe');
        $notification = $this->createMessageNotification($messagedUser);

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/notifications/{$notification->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeleteNotificationById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $notification = $this->createMessageNotification();

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/notifications/{$notification->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);

        $notificationRepository = $this->getService(NotificationRepository::class);
        $notification = $notificationRepository->find($notification->getId());
        self::assertNull($notification);
    }

    public function testApiCannotDeleteAllNotificationsAnonymous(): void
    {
        $client = self::createClient();

        $this->createMessageNotification();

        $client->request('DELETE', '/api/notifications');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeleteAllNotificationsWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $this->createMessageNotification();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', '/api/notifications', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeleteAllNotifications(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');

        $notification = $this->createMessageNotification();

        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:notification:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', '/api/notifications', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);

        $notificationRepository = $this->getService(NotificationRepository::class);
        $notification = $notificationRepository->find($notification->getId());
        self::assertNull($notification);
    }
}
