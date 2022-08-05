<?php declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\Exception\InvalidApSignatureException;
use App\Repository\UserRepository;

class SignatureValidator
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function validate(array $payload, array $headers)
    {
        $signature = is_array($headers['signature']) ? $headers['signature'][0] : $headers['signature'];
        $date      = is_array($headers['date']) ? $headers['date'][0] : $headers['date'];

        if (!$signature || !$date) {
            throw new InvalidApSignatureException();
        }

        // @todo verify headers date

        $signature = HttpSignature::parseSignatureHeader($signature);

        $this->validateUrl($keyId = is_array($signature['keyId']) ? $signature['keyId'][0] : $signature['keyId']);
        $this->validateUrl($id = is_array($signature['id']) ? $signature['id'][0] : $signature['id']);

        $keyDomain = parse_url($signature['keyId'], PHP_URL_HOST);
        $idDomain  = parse_url($signature['keyId'], PHP_URL_HOST);

        if (isset($payload['object']) && is_array($payload['object']) && isset($payload['object']['attributedTo'])) {
            if (parse_url($payload['object']['attributedTo'], PHP_URL_HOST) !== $keyDomain) {
                throw new InvalidApSignatureException('Invalid host url.');
            }
        }

        if (!$keyDomain || !$idDomain || $keyDomain !== $idDomain) {
            throw new InvalidApSignatureException('Wrong domain.');
        }

        $actor = $this->userRepository->findOneBy(['apProfileId' => $keyId]);
        if (!$actor) {
            $actorUrl = is_array($payload['actor']) ? $payload['actor'][0] : $payload['actor'];
        }
    }

    private function validateUrl(string $url): void
    {
        // @todo cache ap_hash_valid_url
        $valid = filter_var($url, FILTER_VALIDATE_URL);
        if (!$valid) {
            throw new InvalidApSignatureException('Invalid url.');
        }

        $parsed = parse_url($url);
        if ($parsed['scheme'] !== 'https') {
            throw new InvalidApSignatureException('Invalid scheme url.');
        }

//        if(in_array($parsed['host'], $blockedInstances)){
//           @todo blocked instances
//        }
    }
}
