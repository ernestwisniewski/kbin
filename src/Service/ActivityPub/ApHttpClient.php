<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\Entity\User;
use App\Factory\ActivityPub\PersonFactory;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/*
 * source:
 * https://github.com/aaronpk/Nautilus/blob/master/app/ActivityPub/HTTPSignature.php
 * https://github.com/pixelfed/pixelfed/blob/dev/app/Util/ActivityPub/HttpSignature.php
 */

class ApHttpClient
{
    public function __construct(private HttpClientInterface $client, private PersonFactory $personFactory, private LoggerInterface $logger)
    {
    }

    public function getActivityObject(string $url, bool $decoded = true): array|string
    {
        $req = $this->client->request('GET', $url, [
            'headers' => [
                'Accept'     => 'application/activity+json,application/ld+json,application/json',
                'User-Agent' => 'kbinBot v0.1 - https://kbin.pub',
            ],
        ]);

        $this->logger->info("ApHttpClient:getActivityObject:url: {$url}");

        return $decoded ? json_decode($req->getContent(), true) : $req->getContent();
    }

    public function getActorObject(string $apProfileId): array
    {
        $req = $this->client->request('GET', $apProfileId, [
            'headers' => [
                'Accept'     => 'application/activity+json,application/ld+json,application/json',
                'User-Agent' => 'kbinBot v0.1 - https://kbin.pub',
            ],
        ]);

        $this->logger->info("ApHttpClient:getActorObject:url: {$apProfileId}");

        return json_decode($req->getContent(), true);
    }

    public function getInboxUrl(string $apProfileId): string
    {
        $actor = $this->getActorObject($apProfileId);

        return $actor['endpoints']['sharedInbox'] ?? $actor['inbox'];
    }

    public function post(string $url, ?array $body = null, ?User $user = null): void
    {
        if ($body) {
            $digest = self::digest($body);
        }

        $headers       = self::headersToSign($url, $body ? $digest : false);
        $stringToSign  = self::headersToSigningString($headers);
        $signedHeaders = implode(' ', array_map('strtolower', array_keys($headers)));
        $key           = openssl_pkey_get_private($user->privateKey);
        openssl_sign($stringToSign, $signature, $key, OPENSSL_ALGO_SHA256);
        $signature       = base64_encode($signature);
        $keyId           = $this->personFactory->getActivityPubId($user).'#main-key';
        $signatureHeader = 'keyId="'.$keyId.'",headers="'.$signedHeaders.'",algorithm="rsa-sha256",signature="'.$signature.'"';
        unset($headers['(request-target)']);
        $headers['Signature']  = $signatureHeader;
        $headers['User-Agent'] = 'kbinBot v0.1 - https://kbin.pub';
        $params['headers']     = $headers;

        if ($body) {
            $params['json'] = $body;
        }

        $this->logger->info("ApHttpClient:post:url: {$url}");
        $this->logger->info("ApHttpClient:post:body ".json_encode($body ?? []));

        $this->client->request('POST', $url, $params);
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
            'Accept'           => 'application/activity+json, application/json',
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
}
