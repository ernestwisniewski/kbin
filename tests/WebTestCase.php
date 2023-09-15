<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use FactoryTrait;
    use OAuth2FlowTrait;
    use ValidationTrait;

    protected const PAGINATED_KEYS = ['items', 'pagination'];
    protected const PAGINATION_KEYS = ['count', 'currentPage', 'maxPage', 'perPage'];
    protected const IMAGE_KEYS = ['filePath', 'sourceUrl', 'storageUrl', 'altText', 'width', 'height'];
    protected const MESSAGE_RESPONSE_KEYS = ['messageId', 'threadId', 'sender', 'body', 'status', 'createdAt'];
    protected const USER_RESPONSE_KEYS = ['userId', 'username', 'about', 'avatar', 'cover', 'createdAt', 'followersCount', 'apId', 'apProfileId', 'isBot', 'isFollowedByUser', 'isFollowerOfUser', 'isBlockedByUser'];
    protected const USER_SMALL_RESPONSE_KEYS = ['userId', 'username', 'isBot', 'isFollowedByUser', 'isFollowerOfUser', 'isBlockedByUser', 'avatar', 'apId', 'apProfileId', 'createdAt'];
    protected const ENTRY_RESPONSE_KEYS = ['entryId', 'magazine', 'user', 'domain', 'title', 'url', 'image', 'body', 'lang', 'tags', 'badges', 'numComments', 'uv', 'dv', 'favourites', 'isFavourited', 'userVote', 'isOc', 'isAdult', 'isPinned', 'views', 'createdAt', 'editedAt', 'lastActive', 'visibility', 'type', 'slug', 'apId'];
    protected const ENTRY_COMMENT_RESPONSE_KEYS = ['commentId', 'magazine', 'user', 'entryId', 'parentId', 'rootId', 'image', 'body', 'lang', 'isAdult', 'uv', 'dv', 'favourites', 'isFavourited', 'userVote', 'visibility', 'apId', 'mentions', 'tags', 'createdAt', 'editedAt', 'lastActive', 'childCount', 'children'];
    protected const POST_RESPONSE_KEYS = ['postId', 'user', 'magazine', 'image', 'body', 'lang', 'isAdult', 'isPinned', 'comments', 'uv', 'dv', 'favourites', 'isFavourited', 'userVote', 'visibility', 'apId', 'tags', 'mentions', 'createdAt', 'editedAt', 'lastActive', 'slug'];
    protected const POST_COMMENT_RESPONSE_KEYS = ['commentId', 'user', 'magazine', 'postId', 'parentId', 'rootId', 'image', 'body', 'lang', 'isAdult', 'uv', 'favourites', 'isFavourited', 'userVote', 'visibility', 'apId', 'mentions', 'createdAt', 'editedAt', 'lastActive', 'childCount', 'children'];
    protected const BAN_RESPONSE_KEYS = ['banId', 'reason', 'expired', 'expiredAt', 'bannedUser', 'bannedBy', 'magazine'];
    protected const LOG_ENTRY_KEYS = ['type', 'createdAt', 'magazine', 'moderator', 'subject'];
    protected const MAGAZINE_RESPONSE_KEYS = ['magazineId', 'owner', 'icon', 'name', 'title', 'description', 'rules', 'subscriptionsCount', 'entryCount', 'entryCommentCount', 'postCount', 'postCommentCount', 'isAdult', 'isUserSubscribed', 'isBlockedByUser', 'tags', 'badges', 'moderators', 'apId', 'apProfileId'];
    protected const MAGAZINE_SMALL_RESPONSE_KEYS = ['magazineId', 'name', 'icon', 'isUserSubscribed', 'isBlockedByUser', 'apId', 'apProfileId'];
    protected const DOMAIN_RESPONSE_KEYS = ['domainId', 'name', 'entryCount', 'subscriptionsCount', 'isUserSubscribed', 'isBlockedByUser'];

    protected const KIBBY_PNG_URL_RESULT = 'a8/1c/a81cc2fea35eeb232cd28fcb109b3eb5a4e52c71bce95af6650d71876c1bcbb7.png';

    protected ArrayCollection $users;
    protected ArrayCollection $magazines;
    protected ArrayCollection $entries;

    protected string $kibbyPath;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->users = new ArrayCollection();
        $this->magazines = new ArrayCollection();
        $this->entries = new ArrayCollection();
        $this->kibbyPath = \dirname(__FILE__).'/assets/kibby_emoji.png';
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    public function getService(string $className)
    {
        return $this->getContainer()->get($className);
    }

    public static function getJsonResponse(KernelBrowser $client): array
    {
        $response = $client->getResponse();
        self::assertJson($response->getContent());

        return json_decode($response->getContent(), associative: true);
    }

    /**
     * Checks that all values in array $keys are present as keys in array $value, and that no additional keys are included.
     */
    public static function assertArrayKeysMatch(array $keys, array $value, string $message = ''): void
    {
        $flipped = array_flip($keys);
        $difference = array_diff_key($value, $flipped);
        $diffString = json_encode(array_keys($difference));
        self::assertEmpty($difference, $message ? $message : "Extra keys were found in the provided array: $diffString");
        $intersect = array_intersect_key($value, $flipped);
        self::assertCount(\count($flipped), $intersect, $message);
    }

    public static function assertNotReached(string $message = 'This branch should never happen'): void
    {
        self::assertFalse(true, $message);
    }
}
