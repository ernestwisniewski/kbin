<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment;

use App\Repository\ReportRepository;
use App\Tests\WebTestCase;

class PostCommentReportApiTest extends WebTestCase
{
    public function testApiCannotReportCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $report = [
            'reason' => 'This comment breaks the rules!',
        ];

        $client->jsonRequest('POST', "/api/post-comments/{$comment->getId()}/report", $report);

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotReportCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $report = [
            'reason' => 'This comment breaks the rules!',
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/post-comments/{$comment->getId()}/report", $report, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanReportOtherUsersComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user2);

        $reportRepository = $this->getService(ReportRepository::class);

        $report = [
            'reason' => 'This comment breaks the rules!',
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:report');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/post-comments/{$comment->getId()}/report", $report, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(204);
        $report = $reportRepository->findBySubject($comment);
        self::assertNotNull($report);
        self::assertSame('This comment breaks the rules!', $report->reason);
        self::assertSame($user->getId(), $report->reporting->getId());
    }

    public function testApiCanReportOwnComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $reportRepository = $this->getService(ReportRepository::class);

        $report = [
            'reason' => 'This comment breaks the rules!',
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:report');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/post-comments/{$comment->getId()}/report", $report, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(204);
        $report = $reportRepository->findBySubject($comment);
        self::assertNotNull($report);
        self::assertSame('This comment breaks the rules!', $report->reason);
        self::assertSame($user->getId(), $report->reporting->getId());
    }
}
