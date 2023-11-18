<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Implementation of RFC 6415 host-meta file.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6415
 */
class HostMetaController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(): Response
    {
        $document = new \XMLWriter();
        $document->openMemory();
        $document->startDocument('1.0', 'UTF-8');

        $document->startElement('XRD');
        $document->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

        $document->startElement('Link');
        $document->writeAttribute('rel', 'lrdd');
        $document->writeAttribute('type', 'application/jrd+json');
        $document->writeAttribute(
            'template',
            $this->urlGenerator->generate(
                'ap_webfinger',
                [],
                $this->urlGenerator::ABSOLUTE_URL,
            ).'?resource={uri}'
        );

        $document->endElement(); // Link
        $document->endElement(); // XRD
        $document->endDocument();

        return new Response(
            $document->outputMemory(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/xrd+xml',
            ],
        );
    }
}
