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

    protected const PAGINATED_KEYS = ['items', 'pagination'];
    protected const PAGINATION_KEYS = ['count', 'currentPage', 'maxPage', 'perPage'];
    protected const IMAGE_KEYS = ['filePath', 'sourceUrl', 'storageUrl', 'altText', 'width', 'height'];
    protected const USER_RESPONSE_KEYS = ['userId', 'username', 'about', 'avatar', 'cover', 'createdAt', 'followersCount', 'apId', 'apProfileId', 'isBot', 'isFollowedByUser', 'isFollowerOfUser', 'isBlockedByUser'];
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
        $this->kibbyPath = dirname(__FILE__).'/assets/kibby_emoji.png';
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
        self::assertCount(count($flipped), $intersect, $message);
    }
}
