<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\MessageBus;

use App\Repository\EmbedRepository;
use App\Utils\Embed;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class LinkEmbedHandler
{
    public function __construct(
        private EmbedRepository $embedRepository,
        private Embed $embed,
        private CacheItemPoolInterface $markdownCache,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(LinkEmbedMessage $message): void
    {
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $message->body, $match);

        DriverManager::getConnection(
            $this->entityManager->getConnection()->getParams(),
            $this->entityManager->getConfiguration()
        );

        foreach ($match[0] as $url) {
            try {
                $embed = $this->embed->fetch($url)->html;
                if ($embed) {
                    $entity = new \App\Entity\Embed($url, true);
                    $this->embedRepository->add($entity);
                }
            } catch (\Exception $e) {
                $embed = false;
            }

            if (!$embed) {
                $entity = new \App\Entity\Embed($url, false);
                $this->embedRepository->add($entity);
            }
        }

        $this->markdownCache->deleteItem(hash('sha256', json_encode(['content' => $message->body])));
    }
}
