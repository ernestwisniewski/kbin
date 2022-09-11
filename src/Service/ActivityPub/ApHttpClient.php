<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\Entity\User;
use App\Exception\InvalidApPostException;
use App\Factory\ActivityPub\PersonFactory;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/*
 * source:
 * https://github.com/aaronpk/Nautilus/blob/master/app/ActivityPub/HTTPSignature.php
 * https://github.com/pixelfed/pixelfed/blob/dev/app/Util/ActivityPub/HttpSignature.php
 */

class ApHttpClient
{
    public function __construct(
        private HttpClientInterface $client,
        private PersonFactory $personFactory,
        private LoggerInterface $logger,
        private CacheInterface $cache,
    ) {
    }

    public function getActivityObject(string $url, bool $decoded = true): array|string|null
    {
        $resp = $this->cache->get('ap_'.hash('sha256', $url), function (ItemInterface $item) use ($url) {
            $this->logger->info("ApHttpClient:getActivityObject:url: {$url}");

            try {
                $r = $this->client->request('GET', $url, [
                    'headers' => [
                        'Accept'     => 'application/activity+json,application/ld+json,application/json',
                        'User-Agent' => 'kbinBot v0.1 - https://kbin.pub',
                    ],
                ])->getContent();
            } catch (\Exception $e) {
                $item->expiresAfter(30);

                return null;
            }

            $item->expiresAfter(0);

            return $r;
        });

        if (!$resp) {
            return null;
        }

        return $decoded ? json_decode($resp, true) : $resp;
    }

    public function getActorObject(string $apProfileId): ?array
    {
        $resp = $this->cache->get('ap_'.hash('sha256', $apProfileId), function (ItemInterface $item) use ($apProfileId) {
            $this->logger->info("ApHttpClient:getActorObject:url: {$apProfileId}");

            try {
                $r = $this->client->request('GET', $apProfileId, [
                    'headers' => [
                        'Accept'     => 'application/activity+json,application/ld+json,application/json',
                        'User-Agent' => 'kbinBot v0.1 - https://kbin.pub',
                    ],
                ])->getContent();
            } catch (\Exception $e) {
                $item->expiresAfter(30);

                return null;
            }

            $item->expiresAfter(86400);

            return $r;
        });

        return $resp ? json_decode($resp, true) : null;
    }

    public function getInboxUrl(string $apProfileId): string
    {
        $actor = $this->getActorObject($apProfileId);

        return $actor['endpoints']['sharedInbox'] ?? $actor['inbox'];
    }

    public function post(string $url, User $user, ?array $body = null): void
    {
        $cache = new FilesystemAdapter(); // @todo redis

        $cacheKey = 'ap_'.hash('sha256', $url.':'.$body['id']);
        if ($cache->hasItem($cacheKey)) {
            return;
        }

        $digest = self::digest($body);

        $headers       = self::headersToSign($url, $body ? $digest : false);
        $stringToSign  = self::headersToSigningString($headers);
        $signedHeaders = implode(' ', array_map('strtolower', array_keys($headers)));
        $key           = openssl_pkey_get_private($user->privateKey);
        openssl_sign($stringToSign, $signature, $key, OPENSSL_ALGO_SHA256);
        $signature       = base64_encode($signature);
        $keyId           = $this->personFactory->getActivityPubId($user).'#main-key';
        $signatureHeader = 'keyId="'.$keyId.'",headers="'.$signedHeaders.'",algorithm="rsa-sha256",signature="'.$signature.'"';
        unset($headers['(request-target)']);
        $headers['Signature']    = $signatureHeader;
        $headers['User-Agent']   = 'kbinBot v0.1 - https://kbin.pub';
        $headers['Accept']       = 'application/activity+json, application/json';
        $headers['Content-Type'] = 'application/json';

        $this->logger->info("ApHttpClient:post:url: {$url}");
        $this->logger->info("ApHttpClient:post:body ".json_encode($body ?? []));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::headersToCurlArray($headers));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_exec($ch);
        curl_close($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!str_starts_with((string) $code, '2')) {
            throw new InvalidApPostException("Post fail: {$url}, ".json_encode($body));
        }

        // build cache
        $item = $cache->getItem($cacheKey);
        $item->set(true);
        $item->expiresAt(new DateTime('+1 day'));
        $cache->save($item);
    }

    private static function digest(array $body): string
    {
        return base64_encode(hash('sha256', json_encode($body), true));
    }

    #[ArrayShape([
        '(request-target)' => "string",
        'Date'             => "string",
        'Host'             => "mixed",
        'Accept'           => "string",
        'Digest'           => "string",
    ])] protected static function headersToSign(string $url, ?string $digest = null): array
    {
        $date = new DateTime('UTC');

        $headers = [
            '(request-target)' => 'post '.parse_url($url, PHP_URL_PATH),
            'Date'             => $date->format('D, d M Y H:i:s \G\M\T'),
            'Host'             => parse_url($url, PHP_URL_HOST),
        ];

        if ($digest) {
            $headers['Digest'] = 'SHA-256='.$digest;
        }

        return $headers;
    }

    private static function headersToSigningString(array $headers): string
    {
        return implode(
            "\n",
            array_map(function ($k, $v) {
                return strtolower($k).': '.$v;
            }, array_keys($headers), $headers)
        );
    }

    private static function headersToCurlArray($headers): array
    {
        return array_map(function ($k, $v) {
            return "$k: $v";
        }, array_keys($headers), $headers);
    }
}
