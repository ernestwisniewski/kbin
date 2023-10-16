<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\ActivityPub;

use App\Entity\Magazine;
use App\Exception\InvalidApSignatureException;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\SignatureValidator;
use App\Service\ActivityPubManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SignatureValidatorTest extends TestCase
{
    private static \OpenSSLAsymmetricKey $privateKey;
    private static string $publicKeyPem;

    private array $body;
    private array $headers;

    /**
     * Sets up an RSA keypair for use in the tests.
     */
    public static function setUpBeforeClass(): void
    {
        $res = openssl_pkey_new(
            [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        if (false === $res) {
            self::fail('Unable to generate suitable RSA key, ensure your testing environment has a correctly configured OpenSSL library');
        }

        $details = openssl_pkey_get_details($res);

        self::$publicKeyPem = $details['key'];

        openssl_pkey_export($res, $privateKey);
        self::$privateKey = openssl_pkey_get_private($privateKey);
    }

    /**
     * Sets up the test with a valid, hs2019 signed, http request body and headers.
     *
     * Includes the headers and signature that would be included in a request from
     * a Lemmy (0.18.3) instance
     */
    private function createSignedRequest(string $inbox): void
    {
        $this->body = [
            'actor' => 'https://kbin.localhost/m/group',
            'id' => 'https://kbin.localhost/f/object/1',
        ];
        $headers = [
            '(request-target)' => 'post '.$inbox,
            'content-type' => 'application/activity+json',
            'date' => (new \DateTimeImmutable('now'))->format('D, d M Y H:i:s \G\M\T'),
            'digest' => 'SHA-256='.base64_encode(hash('sha256', json_encode($this->body), true)),
            'host' => 'kbin.localhost',
        ];

        $signingString = implode(
            "\n",
            array_map(function ($k, $v) {
                return strtolower($k).': '.$v;
            }, array_keys($headers), $headers)
        );
        $signedHeaders = implode(' ', array_map('strtolower', array_keys($headers)));

        openssl_sign($signingString, $signature, self::$privateKey, OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);

        unset($headers['(request-target)']);
        $headers['signature'] = 'keyId="%s#main-key",headers="'.$signedHeaders.'",algorithm="hs2019",signature="'.$signature.'"';
        array_walk($headers, function (string &$value) {
            $value = [$value];
        });
        $this->headers = $headers;
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testItValidatesACorrectlySignedRequest(): void
    {
        $this->createSignedRequest('/f/inbox');

        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'https://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'https://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apManager->method('findActorOrCreate')
            ->willReturn($stubMagazine);

        $apHttpClient = $this->createStub(ApHttpClient::class);
        $apHttpClient->method('getActorObject')
            ->willReturn(
                [
                    'publicKey' => [
                        'publicKeyPem' => self::$publicKeyPem,
                    ],
                ],
            );

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $logger);

        $sut->validate(['uri' => '/f/inbox'], $this->headers, json_encode($this->body));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testItValidatesACorrectlySignedRequestToAPersonalInbox(): void
    {
        $this->createSignedRequest('/u/user/inbox');

        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'https://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'https://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apManager->method('findActorOrCreate')
            ->willReturn($stubMagazine);

        $apHttpClient = $this->createStub(ApHttpClient::class);
        $apHttpClient->method('getActorObject')
            ->willReturn(
                [
                    'publicKey' => [
                        'publicKeyPem' => self::$publicKeyPem,
                    ],
                ],
            );

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $logger);

        $sut->validate(['uri' => '/u/user/inbox'], $this->headers, json_encode($this->body));
    }

    public function testItDoesNotValidateARequestWithDifferentBody(): void
    {
        $this->createSignedRequest('/f/inbox');

        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'https://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'https://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apManager->method('findActorOrCreate')
            ->willReturn($stubMagazine);

        $apHttpClient = $this->createStub(ApHttpClient::class);
        $apHttpClient->method('getActorObject')
            ->willReturn(
                [
                    'publicKey' => [
                        'publicKeyPem' => self::$publicKeyPem,
                    ],
                ],
            );

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $logger);

        $badBody = [
            'actor' => 'https://kbin.localhost/m/badgroup',
            'id' => 'https://kbin.localhost/f/object/1',
        ];

        $this->expectException(InvalidApSignatureException::class);
        $this->expectExceptionMessage('Signature of request could not be verified.');
        $sut->validate(['uri' => '/f/inbox'], $this->headers, json_encode($badBody));
    }

    public function testItDoesNotValidateARequestWhenDomainsDoNotMatch(): void
    {
        $this->createSignedRequest('/f/inbox');

        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'https://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'https://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apHttpClient = $this->createStub(ApHttpClient::class);

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $logger);

        $badBody = [
            'actor' => 'https://kbin.localhost/m/group',
            'id' => 'https://lemmy.localhost/activities/announce/1',
        ];

        $this->expectException(InvalidApSignatureException::class);
        $this->expectExceptionMessage('Supplied key domain does not match domain of incoming activity.');
        $sut->validate(['uri' => '/f/inbox'], $this->headers, json_encode($badBody));
    }

    public function testItDoesNotValidateARequestWhenUrlsAreNotHTTPS(): void
    {
        $this->createSignedRequest('/f/inbox');

        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'http://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'http://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apHttpClient = $this->createStub(ApHttpClient::class);

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $logger);

        $badBody = [
            'actor' => 'http://kbin.localhost/m/group',
            'id' => 'http://kbin.localhost/f/object/1',
        ];

        $this->expectException(InvalidApSignatureException::class);
        $this->expectExceptionMessage('Necessary supplied URL does not use HTTPS.');
        $sut->validate(['uri' => '/f/inbox'], $this->headers, json_encode($badBody));
    }
}
