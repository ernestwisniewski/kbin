<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\ActivityPub;

use App\Entity\Magazine;
use App\Exception\InvalidApSignatureException;
use App\Service\ActivityPub\ApHttpClient;
use App\Service\ActivityPub\SignatureValidator;
use App\Service\ActivityPubManager;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignatureValidatorTest extends TestCase
{
    private const KEY_PATH = __DIR__.'/../../../fixtures/';

    private array $body;
    private array $headers;
    private string $publicKeyPem;

    /**
     * Sets up the test with a valid, hs2019 signed, http request body and headers.
     *
     * Includes the headers and signature that would be included in a request from
     * a Lemmy (0.18.3) instance
     */
    public function setUp(): void
    {
        $this->publicKeyPem = file_get_contents(self::KEY_PATH.'public_signing_key.pem');

        $this->body = [
            'actor' => 'https://kbin.localhost/m/group',
            'id' => 'https://kbin.localhost/f/object/1',
        ];
        $headers = [
            '(request-target)' => 'post /f/inbox',
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

        $key = openssl_pkey_get_private(file_get_contents(self::KEY_PATH.'signing_key.pem'));
        openssl_sign($signingString, $signature, $key, OPENSSL_ALGO_SHA256);
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
                        'publicKeyPem' => $this->publicKeyPem,
                    ],
                ],
            );

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('/f/inbox');

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $urlGenerator, $logger);

        $sut->validate(json_encode($this->body), $this->headers);
    }

    public function testItDoesNotValidateARequestWithDifferentBody(): void
    {
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
                        'publicKeyPem' => $this->publicKeyPem,
                    ],
                ],
            );

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('/f/inbox');

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $urlGenerator, $logger);

        $badBody = [
            'actor' => 'https://kbin.localhost/m/badgroup',
            'id' => 'https://kbin.localhost/f/object/1',
        ];

        $this->expectException(InvalidApSignatureException::class);
        $this->expectExceptionMessage('Signature of request could not be verified.');
        $sut->validate(json_encode($badBody), $this->headers);
    }

    public function testItDoesNotValidateARequestWhenDomainsDoNotMatch(): void
    {
        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'https://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'https://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apHttpClient = $this->createStub(ApHttpClient::class);
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $urlGenerator, $logger);

        $badBody = [
            'actor' => 'https://kbin.localhost/m/group',
            'id' => 'https://lemmy.localhost/activities/announce/1',
        ];

        $this->expectException(InvalidApSignatureException::class);
        $this->expectExceptionMessage('Supplied key domain does not match domain of incoming activity.');
        $sut->validate(json_encode($badBody), $this->headers);
    }

    public function testItDoesNotValidateARequestWhenUrlsAreNotHTTPS(): void
    {
        $stubMagazine = $this->createStub(Magazine::class);
        $stubMagazine->apProfileId = 'http://kbin.localhost/m/group';

        $this->headers['signature'][0] = sprintf($this->headers['signature'][0], 'http://kbin.localhost/m/group');

        $apManager = $this->createStub(ActivityPubManager::class);
        $apHttpClient = $this->createStub(ApHttpClient::class);
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);

        $logger = $this->createStub(LoggerInterface::class);

        $sut = new SignatureValidator($apManager, $apHttpClient, $urlGenerator, $logger);

        $badBody = [
            'actor' => 'http://kbin.localhost/m/group',
            'id' => 'http://kbin.localhost/f/object/1',
        ];

        $this->expectException(InvalidApSignatureException::class);
        $this->expectExceptionMessage('Necessary supplied URL does not use HTTPS.');
        $sut->validate(json_encode($badBody), $this->headers);
    }
}
