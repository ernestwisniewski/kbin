<?php

namespace App\DependencyInjection\Compiler;

use League\CommonMark\ConfigurableEnvironmentInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddMarkdownExtensionsPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        $definition = $container->getDefinition(ConfigurableEnvironmentInterface::class);

        foreach ($container->findTaggedServiceIds('commonmark.event_listener') as $serviceId => $tags) {
            $event = $tags[array_key_last($tags)]['event'] ?? null;

            if (!$event) {
                throw new \RuntimeException('event missing');
            }

            $method = $tags[array_key_last($tags)]['method'] ?? '__invoke';
            $priority = $tags[array_key_last($tags)]['priority'] ?? 0;

            $definition->addMethodCall('addEventListener', [
                $event,
                [new Reference($serviceId), $method],
                $priority,
            ]);
        }

        foreach ($container->findTaggedServiceIds('commonmark.inline_parser') as $serviceId => $tags) {
            $definition->addMethodCall('addInlineParser', [
                new Reference($serviceId),
                $tags[array_key_last($tags)]['priority'] ?? 0,
            ]);
        }

        foreach ($container->findTaggedServiceIds('commonmark.block_renderer') as $serviceId => $tags) {
            $element = $tags[array_key_last($tags)]['element'] ?? null;

            if (!$element) {
                throw new \RuntimeException('element missing on BlockRenderer');
            }

            $priority = $tags[array_key_last($tags)]['priority'] ?? 0;

            $definition->addMethodCall('addBlockRenderer', [
                $element,
                new Reference($serviceId),
                $priority,
            ]);
        }

        foreach ($container->findTaggedServiceIds('commonmark.inline_renderer') as $serviceId => $tags) {
            $element = $tags[array_key_last($tags)]['element'] ?? null;

            if (!$element) {
                throw new \RuntimeException('element missing on InlineRenderer');
            }

            $priority = $tags[array_key_last($tags)]['priority'] ?? 0;

            $definition->addMethodCall('addInlineRenderer', [
                    $element,
                    new Reference($serviceId),
                    $priority,
            ]);
        }
    }
}
