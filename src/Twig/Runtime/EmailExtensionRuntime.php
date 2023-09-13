<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\RuntimeExtensionInterface;

class EmailExtensionRuntime implements RuntimeExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EntrypointLookupInterface $entrypointLookupInterface,
        private readonly string $publicDir)
    {
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntrypointLookupInterface::class,
        ];
    }

    /**
     * Loops through all entries with the provided name and outputs their contents into a single string.
     *
     * Used to return a single string containing all css (which may have been split into multiple css files as part of
     * webpack)
     */
    public function getEncoreEntryCssSource(string $entryName): string
    {
        // ensure interface is reset, else subsequent queries will fail
        $this->entrypointLookupInterface->reset();
        $entry = $this->container->get(EntrypointLookupInterface::class);
        $source = '';
        $files = $entry->getCssFiles($entryName);
        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDir.'/'.$file);
        }

        return $source;
    }
}
