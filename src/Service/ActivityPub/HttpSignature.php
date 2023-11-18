<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service\ActivityPub;

use App\Exception\InvalidApSignatureException;

/*
 * source:
 * https://github.com/aaronpk/Nautilus/blob/master/app/ActivityPub/HTTPSignature.php
 * https://github.com/pixelfed/pixelfed/blob/dev/app/Util/ActivityPub/HttpSignature.php
 */

class HttpSignature
{
    /**
     * Splits a signature header string into component pieces.
     *
     * @return array{
     *   keyId: string,
     *   algorithm: string,
     *   headers: string,
     *   signature: string,
     * }
     */
    public static function parseSignatureHeader(string $signature): array
    {
        $parts = explode(',', $signature);
        $signatureData = [];

        foreach ($parts as $part) {
            if (preg_match('/(.+)="(.+)"/', $part, $match)) {
                $signatureData[$match[1]] = $match[2];
            }
        }

        if (!isset($signatureData['keyId'])) {
            throw new InvalidApSignatureException('No keyId was found in the signature header. Found: '.implode(', ', array_keys($signatureData)));
        }

        if (!filter_var($signatureData['keyId'], FILTER_VALIDATE_URL)) {
            throw new InvalidApSignatureException('keyId is not a URL: '.$signatureData['keyId']);
        }

        if (!isset($signatureData['headers']) || !isset($signatureData['signature'])) {
            throw new InvalidApSignatureException('Signature is missing headers or signature parts.');
        }

        return $signatureData;
    }
}
