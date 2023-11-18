<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Factory\ActivityPub\NodeInfoFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class NodeInfoController
{
    public function __construct(
        private NodeInfoFactory $nodeInfoFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function nodeInfo(): JsonResponse
    {
        return new JsonResponse([
            'links' => [
                [
                    'rel' => NodeInfoFactory::NODE_REL_V2_0,
                    'href' => $this->urlGenerator->generate('ap_node_info_v2', ['version' => '2.0'], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                [
                    'rel' => NodeInfoFactory::NODE_REL_V2_1,
                    'href' => $this->urlGenerator->generate('ap_node_info_v2', ['version' => '2.1'], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
            ],
        ]);
    }

    public function nodeInfoV2(string $version): JsonResponse
    {
        return new JsonResponse($this->nodeInfoFactory->create($version));
    }
}
