<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Factory\ActivityPub\NodeInfoFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NodeInfoController
{
    public function __construct(private NodeInfoFactory $nodeInfoFactory, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function nodeInfo(): JsonResponse
    {
        return new JsonResponse([
            'links' => [
                'rel' => NodeInfoFactory::NODE_REL,
                'url' => $this->urlGenerator->generate('ap_node_info_v2', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
        ]);
    }

    public function nodeInfoV2(): JsonResponse
    {
        return new JsonResponse($this->nodeInfoFactory->create());
    }
}
