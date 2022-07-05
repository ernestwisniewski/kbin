<?php declare(strict_types=1);

namespace App\Tests\Functional\ApiDataProvider;

use App\Tests\ApiTestCase;
use App\Tests\FactoryTrait;
use DateTimeInterface;

class PostCollectionDataProviderTest extends ApiTestCase
{
    use FactoryTrait;

    public function testPostsCollection(): void
    {
        $client = $this->createClient();

        $post = $this->createPost('testowy post', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'));
        $this->createPost('test2', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'));
        $this->createPost('test3', $this->getMagazineByName('acme'), $this->getUserByUsername('JohnDoe'));

        $comment = $this->createPostComment('test post comment', $post);

        $this->createVote(1, $post, $this->getUserByUsername('testUser1'));
        $this->createVote(1, $post, $this->getUserByUsername('testUser2'));
        $this->createVote(1, $comment, $this->getUserByUsername('testUser1'));
        $this->createVote(1, $comment, $this->getUserByUsername('testUser2'));

        $response = $client->request('GET', '/api/posts');

        $this->assertCount(16, $response->toArray()['hydra:member'][0]);
        $this->assertCount(4, $response->toArray()['hydra:member'][0]['user']);

//        $this->assertMatchesResourceCollectionJsonSchema(PostDto::class); // todo image

        $this->assertJsonContains([
            '@context'         => '/api/contexts/post',
            '@id'              => '/api/posts',
            '@type'            => 'hydra:Collection',
            'hydra:member'     => [
                [
                    '@id'          => '/api/posts/'.$post->getId(),
                    '@type'        => 'post',
                    'magazine'     => [
                        '@id'   => '/api/magazines/acme',
                        '@type' => 'magazine',
                        'name'  => 'acme',
                    ],
                    'user'         => [
                        '@id'      => '/api/users/JohnDoe',
                        '@type'    => 'user',
                        'username' => 'JohnDoe',
                    ],
                    'image'        => null, // @todo
                    'comments'     => 1,
                    'uv'           => 2,
                    'isAdult'      => false,
                    'score'        => 2,
                    'visibility'   => 'visible',
                    'createdAt'    => $post->createdAt->format(DateTimeInterface::RFC3339),
                    'lastActive'   => $post->lastActive->format(DateTimeInterface::RFC3339),
                    'id'           => $post->getId(),
                    'bestComments' => [
                        [
                            '@id'        => '/api/post_comments/'.$comment->getId(),
                            '@type'      => 'post_comment',
                            'user'       => [
                                '@id'      => '/api/users/JohnDoe',
                                '@type'    => 'user',
                                'username' => 'JohnDoe',
                                'avatar'   => null,
                            ],
                            'uv'         => 2,
                            'createdAt'  => $comment->createdAt->format(DateTimeInterface::RFC3339),
                            'lastActive' => $comment->lastActive->format(DateTimeInterface::RFC3339),
                        ],
                    ],
                ],
            ],
            'hydra:totalItems' => 3,
            'hydra:view'       => [
                '@id'         => '/api/posts?page=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/posts?page=1',
                'hydra:last'  => '/api/posts?page=2',
                'hydra:next'  => '/api/posts?page=2',
            ],
        ]);
    }
}
