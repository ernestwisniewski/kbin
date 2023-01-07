<?php

declare(strict_types=1);

namespace App\Service\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Exception\InvalidApSignatureException;
use App\Service\ActivityPubManager;

class SignatureValidator
{
    public function __construct(
        private readonly ActivityPubManager $activityPubManager,
        private readonly ApHttpClient $client,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function validate(string $body, array $headers): void
    {
        $payload = json_decode($body, true);

        $signature = is_array($headers['signature']) ? $headers['signature'][0] : $headers['signature'];
        $date = is_array($headers['date']) ? $headers['date'][0] : $headers['date'];

        if (!$signature || !$date) {
            throw new InvalidApSignatureException();
        }

        // @todo verify headers date

        $signature = HttpSignature::parseSignatureHeader($signature);

        $this->validateUrl($keyId = is_array($signature['keyId']) ? $signature['keyId'][0] : $signature['keyId']);
        $this->validateUrl($id = is_array($payload['id']) ? $payload['id'][0] : $payload['id']);

        $keyDomain = parse_url($keyId, PHP_URL_HOST);
        $idDomain = parse_url($id, PHP_URL_HOST);

        if (isset($payload['object']) && is_array($payload['object']) && isset($payload['object']['attributedTo'])) {
            if (parse_url($payload['object']['attributedTo'], PHP_URL_HOST) !== $keyDomain) {
                throw new InvalidApSignatureException('Invalid host url.');
            }
        }

        if (!$keyDomain || !$idDomain || $keyDomain !== $idDomain) {
            throw new InvalidApSignatureException('Wrong domain.');
        }

        $actorUrl = is_array($payload['actor']) ? $payload['actor'][0] : $payload['actor'];

        $user = $this->activityPubManager->findActorOrCreate($actorUrl);

        $pkey = openssl_pkey_get_public($this->client->getActorObject($user->apProfileId)['publicKey']['publicKeyPem']);

        $this->verifySignature($pkey, $signature, $headers, $this->urlGenerator->generate('ap_shared_inbox'), $body);
    }

    private function validateUrl(string $url): void
    {
        $valid = filter_var($url, FILTER_VALIDATE_URL);
        if (!$valid) {
            throw new InvalidApSignatureException('Invalid url.');
        }

        $parsed = parse_url($url);
        if ('https' !== $parsed['scheme']) {
            throw new InvalidApSignatureException('Invalid scheme url.');
        }
    }

    private function verifySignature(
        \OpenSSLAsymmetricKey $pkey,
        array $signature,
        array $headers,
        string $inboxUrl,
        string $payload
    ): void {
        $digest = 'SHA-256='.base64_encode(hash('sha256', json_encode($payload), true));

        $headersToSign = [];
        foreach (explode(' ', $signature['headers']) as $h) {
            if ('(request-target)' == $h) {
                $headersToSign[$h] = 'post '.$inboxUrl;
            } elseif ('digest' == $h) {
                $headersToSign[$h] = $digest;
            } elseif (isset($headers[$h][0])) {
                $headersToSign[$h] = $headers[$h][0];
            }
        }

        $signingString = self::headersToSigningString($headersToSign);

        $verified = openssl_verify($signingString, base64_decode($signature['signature']), $pkey, OPENSSL_ALGO_SHA256);

        if (!$verified) {
//            throw new InvalidApSignatureException('Verify signature fail.');
        }
    }

    private static function headersToSigningString($headers): string
    {
        return implode(
            "\n",
            array_map(function ($k, $v) {
                return strtolower($k).': '.$v;
            }, array_keys($headers), $headers)
        );
    }
}
