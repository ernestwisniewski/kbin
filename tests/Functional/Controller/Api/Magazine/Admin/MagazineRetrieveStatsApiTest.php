<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\DTO\ModeratorDto;
use App\Event\Entry\EntryHasBeenSeenEvent;
use App\Service\FavouriteManager;
use App\Service\MagazineManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MagazineRetrieveStatsApiTest extends WebTestCase
{
    public const VIEW_STATS_KEYS = ['data'];
    public const STATS_BY_CONTENT_TYPE_KEYS = ['entry', 'post', 'entry_comment', 'post_comment'];

    public const COUNT_ITEM_KEYS = ['datetime', 'count'];
    public const VOTE_ITEM_KEYS = ['datetime', 'boost', 'down', 'up'];

    public function testApiCannotRetrieveMagazineStatsAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/views");

        self::assertResponseStatusCodeSame(401);
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/votes");

        self::assertResponseStatusCodeSame(401);
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/content");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveMagazineStatsWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/views", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/votes", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/content", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveMagazineStatsIfNotOwner(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $this->getUserByUsername('JaneDoe'));
        $magazineManager = $this->getService(MagazineManager::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $this->getUserByUsername('JohnDoe');
        $magazineManager->addModerator($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:stats');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/views", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/votes", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/content", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveMagazineStats(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JohnDoe2');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');

        $entry = $this->getEntryByTitle('Stats test', body: 'This is gonna be a statistic', magazine: $magazine, user: $user);

        $requestStack = $this->getService(RequestStack::class);
        $requestStack->push(Request::create('/'));
        $dispatcher = $this->getService(EventDispatcherInterface::class);
        $dispatcher->dispatch(new EntryHasBeenSeenEvent($entry));

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favourite = $favouriteManager->toggle($user, $entry);

        $voteManager = $this->getService(VoteManager::class);
        $vote = $voteManager->upvote($entry, $user);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($favourite);
        $entityManager->persist($vote);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:stats');
        $token = $codes['token_type'].' '.$codes['access_token'];

        // Start a day ago to avoid timezone issues when testing on machines with non-UTC timezones
        $startString = rawurlencode($entry->getCreatedAt()->add(\DateInterval::createFromDateString('-1 day'))->format(\DateTimeImmutable::ATOM));
        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/views?resolution=hour&start=$startString", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::VIEW_STATS_KEYS, $jsonData);
        self::assertIsArray($jsonData['data']);
        self::assertCount(1, $jsonData['data']);
        self::assertArrayKeysMatch(self::COUNT_ITEM_KEYS, $jsonData['data'][0]);
        $now = new \DateTime();
        $now->setTime((int) $now->format('H'), 0);
        $nowTimestamp = $now->getTimestamp();
        $viewTimestamp = (new \DateTimeImmutable($jsonData['data'][0]['datetime']))->getTimestamp();
        // Should only be different if tests are run on the edge of an hour changing
        if ($nowTimestamp !== $viewTimestamp) {
            self::assertEquals(abs($nowTimestamp - $viewTimestamp), 3600);
        }
        self::assertSame(1, $jsonData['data'][0]['count']);

        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/votes?resolution=hour&start=$startString", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::STATS_BY_CONTENT_TYPE_KEYS, $jsonData);
        self::assertIsArray($jsonData['entry']);
        self::assertCount(1, $jsonData['entry']);
        self::assertIsArray($jsonData['entry_comment']);
        self::assertEmpty($jsonData['entry_comment']);
        self::assertIsArray($jsonData['post']);
        self::assertEmpty($jsonData['post']);
        self::assertIsArray($jsonData['post_comment']);
        self::assertEmpty($jsonData['post_comment']);
        self::assertArrayKeysMatch(self::VOTE_ITEM_KEYS, $jsonData['entry'][0]);
        $voteTimestamp = (new \DateTimeImmutable($jsonData['entry'][0]['datetime']))->getTimestamp();
        if ($nowTimestamp !== $voteTimestamp) {
            self::assertEquals(abs($nowTimestamp - $voteTimestamp), 3600);
        }
        self::assertSame(1, $jsonData['entry'][0]['up']);
        self::assertSame(0, $jsonData['entry'][0]['down']);
        self::assertSame(1, $jsonData['entry'][0]['boost']);

        $client->request('GET', "/api/stats/magazine/{$magazine->getId()}/content?resolution=hour&start=$startString", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::STATS_BY_CONTENT_TYPE_KEYS, $jsonData);
        self::assertIsArray($jsonData['entry']);
        self::assertCount(1, $jsonData['entry']);
        self::assertIsArray($jsonData['entry_comment']);
        self::assertEmpty($jsonData['entry_comment']);
        self::assertIsArray($jsonData['post']);
        self::assertEmpty($jsonData['post']);
        self::assertIsArray($jsonData['post_comment']);
        self::assertEmpty($jsonData['post_comment']);
        self::assertArrayKeysMatch(self::COUNT_ITEM_KEYS, $jsonData['entry'][0]);
        $contentTimestamp = (new \DateTimeImmutable($jsonData['entry'][0]['datetime']))->getTimestamp();
        if ($nowTimestamp !== $contentTimestamp) {
            self::assertEquals(abs($nowTimestamp - $contentTimestamp), 3600);
        }
        self::assertSame(1, $jsonData['entry'][0]['count']);
    }
}